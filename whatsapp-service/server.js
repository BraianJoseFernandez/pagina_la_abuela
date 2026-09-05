const express = require('express');
const cors = require('cors');
const QRCode = require('qrcode');
const pino = require('pino');
const path = require('path');
const fs = require('fs');

const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion
} = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.PORT || 3001;
const AUTH_DIR = path.join(__dirname, 'auth_info');

if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

app.use(cors());
app.use(express.json());

let sock = null;
let currentQR = null;
let currentQRImage = null;
let connectionState = 'disconnected';
let connectedUser = null;
let isInitializing = false;

const logger = pino({ level: 'silent' });

async function initWhatsApp() {
    if (isInitializing) return;
    isInitializing = true;

    try {
        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901307] }));

        sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: false,
            auth: state,
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            browser: ['Rotisería La Abuela', 'Desktop', '1.0.0']
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                currentQR = qr;
                connectionState = 'qr_ready';
                try {
                    currentQRImage = await QRCode.toDataURL(qr);
                } catch (e) {
                    console.error('Error generando imagen QR:', e);
                }
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

                connectionState = 'disconnected';
                currentQR = null;
                currentQRImage = null;
                connectedUser = null;

                if (shouldReconnect) {
                    console.log('Conexión cerrada temporalmente. Reconectando en 5s...');
                    setTimeout(() => {
                        isInitializing = false;
                        initWhatsApp();
                    }, 5000);
                } else {
                    console.log('Sesión cerrada por el usuario. Limpiando credenciales...');
                    try {
                        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                        fs.mkdirSync(AUTH_DIR, { recursive: true });
                    } catch (err) {}
                    isInitializing = false;
                }
            } else if (connection === 'open') {
                console.log('✅ Conexión establecida con WhatsApp!');
                connectionState = 'connected';
                currentQR = null;
                currentQRImage = null;
                connectedUser = sock.user;
                isInitializing = false;
            } else if (connection === 'connecting') {
                connectionState = 'connecting';
            }
        });
    } catch (err) {
        console.error('Error inicializando Baileys:', err);
        connectionState = 'disconnected';
        isInitializing = false;
    }
}

// Formatear número argentino a JID internacional de WhatsApp
function formatToJid(rawPhone) {
    let clean = (rawPhone || '').toString().replace(/\D/g, '');
    if (!clean) return null;

    // Si comienza con 0 (ej: 03794123456), quitar el 0
    if (clean.startsWith('0')) {
        clean = clean.substring(1);
    }

    // Si no tiene código de país (ej: 3794123456 con 10 dígitos)
    if (clean.length === 10) {
        clean = '549' + clean;
    } else if (clean.startsWith('54') && !clean.startsWith('549') && clean.length === 12) {
        // ej: 543794123456 -> 5493794123456
        clean = '549' + clean.substring(2);
    }

    return `${clean}@s.whatsapp.net`;
}

// 1. Estado de la conexión
app.get('/status', (req, res) => {
    res.json({
        success: true,
        status: connectionState,
        connected: connectionState === 'connected',
        user: connectedUser ? {
            id: connectedUser.id,
            name: connectedUser.name || 'Rotisería La Abuela'
        } : null
    });
});

// 2. Obtener QR en caso de necesitar escanear
app.get('/qr', (req, res) => {
    if (connectionState === 'connected') {
        return res.json({
            success: true,
            status: 'connected',
            connected: true,
            message: 'Ya estás conectado a WhatsApp'
        });
    }

    if (currentQRImage) {
        return res.json({
            success: true,
            status: 'qr_ready',
            qr_image: currentQRImage,
            qr_text: currentQR
        });
    }

    // Si no hay QR pero no está conectado, reintentar inicializar
    if (!isInitializing && connectionState === 'disconnected') {
        initWhatsApp();
    }

    return res.json({
        success: true,
        status: connectionState,
        message: 'Generando código QR, por favor aguarda unos segundos...'
    });
});

// 3. Enviar mensaje por WhatsApp en segundo plano
app.post('/send', async (req, res) => {
    try {
        if (connectionState !== 'connected' || !sock) {
            return res.status(503).json({
                success: false,
                error: 'El servicio de WhatsApp no está conectado. Escanea el código QR en la configuración.'
            });
        }

        const { phone, message } = req.body;

        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                error: 'Se requiere número de teléfono y mensaje.'
            });
        }

        let jid = formatToJid(phone);
        if (!jid) {
            return res.status(400).json({
                success: false,
                error: 'Número de teléfono inválido.'
            });
        }

        // Verificar si el número existe en WhatsApp
        const [onWa] = await sock.onWhatsApp(jid).catch(() => []);
        if (onWa && onWa.jid) {
            jid = onWa.jid;
        }

        // Enviar mensaje
        const result = await sock.sendMessage(jid, { text: message });

        return res.json({
            success: true,
            messageId: result?.key?.id,
            targetJid: jid,
            message: 'Mensaje enviado con éxito.'
        });
    } catch (err) {
        console.error('Error enviando mensaje WhatsApp:', err);
        return res.status(500).json({
            success: false,
            error: err.message || 'Error al enviar mensaje por WhatsApp.'
        });
    }
});

// 4. Desconectar sesión
app.post('/disconnect', async (req, res) => {
    try {
        if (sock) {
            await sock.logout().catch(() => {});
        }
        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (e) {}

        connectionState = 'disconnected';
        connectedUser = null;
        currentQR = null;
        currentQRImage = null;
        isInitializing = false;

        // Reiniciar para generar un nuevo QR
        setTimeout(() => initWhatsApp(), 1500);

        res.json({ success: true, message: 'Sesión de WhatsApp cerrada con éxito.' });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`WhatsApp Microservice escuchando en puerto ${PORT}`);
    initWhatsApp();
});
