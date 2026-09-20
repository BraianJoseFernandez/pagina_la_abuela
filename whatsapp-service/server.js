const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const { Client, LocalAuth } = require('whatsapp-web.js');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3001;
const AUTH_DIR = path.join(__dirname, 'auth_info');

if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

app.use(cors());
app.use(express.json());

let currentQR = null;
let currentQRImage = null;
let connectionState = 'disconnected'; // 'disconnected', 'qr_ready', 'connected'
let connectedUser = null;

// Initialize whatsapp-web.js client
let client;

function initClient() {
    client = new Client({
        authStrategy: new LocalAuth({ dataPath: AUTH_DIR }),
        webVersionCache: {
            type: 'remote',
            remotePath: 'https://raw.githubusercontent.com/wppconnect-team/wa-version/main/html/2.2412.54.html'
        },
        puppeteer: {
            args: [
                '--no-sandbox', 
                '--disable-setuid-sandbox', 
                '--disable-dev-shm-usage'
            ],
            headless: true
        },
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    });

    client.on('qr', async (qr) => {
        console.log('Generando nuevo QR...');
        currentQR = qr;
        connectionState = 'qr_ready';
        try {
            currentQRImage = await qrcode.toDataURL(qr);
        } catch (err) {
            console.error('Error procesando QR:', err);
        }
    });

    client.on('ready', () => {
        console.log('✅ Conexión establecida con WhatsApp (whatsapp-web.js)!');
        connectionState = 'connected';
        currentQR = null;
        currentQRImage = null;
        
        // El bot ya tiene el nombre por defecto en whatsapp-web.js, pero podemos obtener la info del dispositivo
        connectedUser = {
            id: client.info.wid.user,
            name: client.info.pushname || 'Rotisería La Abuela'
        };
    });

    client.on('authenticated', () => {
        console.log('Autenticado exitosamente.');
    });

    client.on('auth_failure', msg => {
        console.error('Fallo de autenticación:', msg);
        connectionState = 'disconnected';
        currentQR = null;
        currentQRImage = null;
        
        // Borrar credenciales porque la sesión caducó
        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (e) {}

        console.log('Reiniciando el cliente para pedir QR nuevamente...');
        setTimeout(() => {
            initClient();
        }, 3000);
    });

    client.on('disconnected', (reason) => {
        console.log('Cliente desconectado:', reason);
        connectionState = 'disconnected';
        currentQR = null;
        currentQRImage = null;
        connectedUser = null;

        // Limpiamos credenciales si el usuario cerro sesión
        if (reason === 'LOGOUT') {
            try {
                fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                fs.mkdirSync(AUTH_DIR, { recursive: true });
            } catch (e) {}
        }
        
        console.log('Reiniciando cliente en 5s...');
        setTimeout(() => {
            initClient();
        }, 5000);
    });

    client.initialize().catch(err => {
        console.error('Error fatal inicializando Puppeteer:', err);
        console.error('¡Asegúrate de instalar las dependencias de Chromium (ej: libgbm-dev, libnss3, etc) en tu servidor!');
    });
}

// Iniciar el cliente al arrancar el servidor
initClient();

// Formatear número argentino a JID internacional de WhatsApp
function formatToJid(rawPhone) {
    let clean = (rawPhone || '').toString().replace(/\D/g, '');
    if (!clean) return null;

    if (clean.startsWith('0')) {
        clean = clean.substring(1);
    }

    if (clean.length === 10) {
        clean = '549' + clean;
    } else if (clean.startsWith('54') && !clean.startsWith('549') && clean.length === 12) {
        clean = '549' + clean.substring(2);
    }

    return `${clean}@c.us`; // whatsapp-web.js usa @c.us en vez de @s.whatsapp.net
}

// --- RUTAS DE LA API ---

app.get('/status', (req, res) => {
    res.json({
        success: true,
        status: connectionState,
        connected: connectionState === 'connected',
        user: connectedUser
    });
});

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

    return res.json({
        success: true,
        status: connectionState,
        message: 'Generando código QR, por favor aguarda unos segundos...'
    });
});

app.post('/send', async (req, res) => {
    try {
        if (connectionState !== 'connected' || !client) {
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

        // Enviar el mensaje usando whatsapp-web.js
        const response = await client.sendMessage(jid, message);

        return res.json({
            success: true,
            messageId: response?.id?._serialized || response?.id?.id || 'unknown',
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

app.post('/disconnect', async (req, res) => {
    try {
        if (client && connectionState === 'connected') {
            await client.logout().catch(() => {});
        } else if (client) {
            await client.destroy().catch(() => {});
        }
        
        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (e) {}

        connectionState = 'disconnected';
        connectedUser = null;
        currentQR = null;
        currentQRImage = null;

        setTimeout(() => initClient(), 1500);

        res.json({ success: true, message: 'Sesión de WhatsApp cerrada con éxito. Generando nuevo QR...' });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.post('/reset', async (req, res) => {
    try {
        if (client) {
            await client.destroy().catch(() => {});
        }
        
        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (e) {}

        connectionState = 'disconnected';
        connectedUser = null;
        currentQR = null;
        currentQRImage = null;

        setTimeout(() => initClient(), 1000);

        res.json({ success: true, message: 'Servicio reiniciado y credenciales limpiadas con éxito.' });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`WhatsApp Microservice escuchando en puerto ${PORT}`);
});
