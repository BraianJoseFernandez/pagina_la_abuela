const express = require('express');
const cors = require('cors');
const QRCode = require('qrcode');
const pino = require('pino');
const path = require('path');
const fs = require('fs');
const dns = require('dns');

// Solución para servidores VPS: Forzar IPv4 para evitar el error 408 (Timeout) de WhatsApp
dns.setDefaultResultOrder('ipv4first');

const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    Browsers
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
let reconnectTimer = null;
let authState = null;
let authSaveCreds = null;

const logger = pino({ level: 'silent' });

// Filtrar mensajes internos de libsignal (SessionEntry) que ensucian la consola
const originalConsoleInfo = console.info;
console.info = function (...args) {
    if (typeof args[0] === 'string' && (args[0].includes('Closing session') || args[0].includes('Opening session'))) {
        return;
    }
    originalConsoleInfo.apply(console, args);
};

process.on('uncaughtException', (err) => {
    console.error('Error no capturado (evitando crash):', err?.message || err);
});

process.on('unhandledRejection', (reason) => {
    console.error('Promesa rechazada no capturada (evitando crash):', reason?.message || reason);
});

function scheduleReconnect(delayMs = 4000) {
    if (reconnectTimer) clearTimeout(reconnectTimer);
    reconnectTimer = setTimeout(() => {
        reconnectTimer = null;
        initWhatsApp();
    }, delayMs);
}

function cleanSocket() {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer);
        reconnectTimer = null;
    }
    if (sock) {
        try {
            sock.ev.removeAllListeners();
            if (sock.ws) {
                try { sock.ws.close(); } catch (e) {}
            }
            sock.end(undefined);
        } catch (e) {}
        sock = null;
    }
}

async function initWhatsApp() {
    if (isInitializing) return;
    isInitializing = true;

    try {
        cleanSocket();

        if (!authState || !fs.existsSync(path.join(AUTH_DIR, 'creds.json'))) {
            const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
            authState = state;
            authSaveCreds = saveCreds;
        }

        const { version } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901307] }));

        if (state.creds && state.creds.me && !state.creds.me.name) {
            state.creds.me.name = 'Rotisería La Abuela';
        }

        sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: false,
            auth: authState,
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            browser: ['Rotisería La Abuela', 'Chrome', '20.0.04'],
            markOnlineOnConnect: false,
            syncFullHistory: false,
            shouldSyncHistoryMessage: () => false
        });

        sock.ev.on('creds.update', authSaveCreds);

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
                const isLoggedOut = statusCode === DisconnectReason.loggedOut;

                connectionState = 'disconnected';
                currentQR = null;
                currentQRImage = null;
                connectedUser = null;
                isInitializing = false;

                cleanSocket();

                if (isLoggedOut) {
                    console.log('Sesión cerrada por el usuario (401). Limpiando credenciales y generando QR nuevo...');
                    try {
                        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                        fs.mkdirSync(AUTH_DIR, { recursive: true });
                        authState = null;
                        authSaveCreds = null;
                    } catch (err) {}
                    scheduleReconnect(2000);
                } else if (statusCode === DisconnectReason.restartRequired) {
                    // 515 = restartRequired: WhatsApp lo envía justo después de escanear el QR
                    // para recargar las nuevas claves criptográficas. ¡NUNCA borrar auth_info aquí!
                    console.log('WhatsApp completó el escaneo y solicitó reinicio (515 restartRequired). Reconectando en 1s...');
                    scheduleReconnect(1000);
                } else if (statusCode === 440) {
                    console.log('Conexión reemplazada (status 440). Esperando 5s para reconexión limpia...');
                    scheduleReconnect(5000);
                } else {
                    console.log(`Conexión cerrada temporalmente (status: ${statusCode}). Reconectando en 3s...`);
                    scheduleReconnect(3000);
                }
            } else if (connection === 'open') {
                console.log('✅ Conexión establecida con WhatsApp!');
                
                // Forzar el estado a "desconectado" (invisible) para que el teléfono suene
                try {
                    await sock.sendPresenceUpdate('unavailable');
                } catch (e) {
                    console.error('Error al forzar presencia oculta:', e);
                }

                connectionState = 'connected';
                currentQR = null;
                currentQRImage = null;
                connectedUser = sock.user;
                isInitializing = false;
                if (reconnectTimer) {
                    clearTimeout(reconnectTimer);
                    reconnectTimer = null;
                }
            } else if (connection === 'connecting') {
                connectionState = 'connecting';
            }
        });
    } catch (err) {
        console.error('Error inicializando Baileys:', err);
        cleanSocket();
        connectionState = 'disconnected';
        isInitializing = false;
        scheduleReconnect(5000);
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

    // Solo programar inicialización si no hay nada corriendo ni en cola
    if (!isInitializing && !sock && !reconnectTimer && connectionState === 'disconnected') {
        scheduleReconnect(1000);
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
        cleanSocket();
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

        res.json({ success: true, message: 'Sesión de WhatsApp cerrada con éxito. Generando nuevo QR...' });
    } catch (err) {
        cleanSocket();
        isInitializing = false;
        res.status(500).json({ success: false, error: err.message });
    }
});

// 5. Forzar reinicio / regeneración de QR
app.post('/reset', async (req, res) => {
    try {
        cleanSocket();
        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (e) {}

        connectionState = 'disconnected';
        connectedUser = null;
        currentQR = null;
        currentQRImage = null;
        isInitializing = false;

        setTimeout(() => initWhatsApp(), 1000);

        res.json({ success: true, message: 'Servicio reiniciado y credenciales limpiadas con éxito.' });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`WhatsApp Microservice escuchando en puerto ${PORT}`);
    initWhatsApp();
});
