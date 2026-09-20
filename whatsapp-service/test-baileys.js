const { default: makeWASocket, useMultiFileAuthState, Browsers, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');

async function connectToWhatsApp() {
    console.log('Iniciando prueba local de Baileys...');
    const { state, saveCreds } = await useMultiFileAuthState('auth_test');
    const { version, isLatest } = await fetchLatestBaileysVersion();
    console.log(`Usando versión de WA: ${version.join('.')}, isLatest: ${isLatest}`);

    const sock = makeWASocket({
        version,
        // Usar nivel 'info' o 'debug' para ver TODO lo que pasa por debajo
        logger: pino({ level: 'info' }),
        printQRInTerminal: true, // Importante: Mostrar QR en terminal para pruebas
        auth: state,
        browser: Browsers.macOS('Desktop'), // Probamos con macOS
        syncFullHistory: false
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
            console.log('CÓDIGO QR LISTO. Escanea con tu teléfono.');
        }

        if (connection === 'close') {
            const shouldReconnect = (lastDisconnect.error)?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log('Conexión cerrada debido a:', lastDisconnect.error);
            console.log('¿Debería reconectar?:', shouldReconnect);
            
            if (shouldReconnect) {
                connectToWhatsApp();
            } else {
                console.log('Sesión cerrada (401). Borra la carpeta auth_test y vuelve a intentarlo.');
            }
        } else if (connection === 'open') {
            console.log('✅ ¡CONECTADO EXITOSAMENTE A WHATSAPP!');
        }
    });
}

connectToWhatsApp();
