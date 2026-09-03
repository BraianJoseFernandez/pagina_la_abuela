<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Rotisería La Abuela</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 24px 12px;
        }
        .main-card {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #e11d48 100%);
            padding: 30px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            color: #fecdd3;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .text {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn-reset {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #e11d48 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 15px;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
        }
        .notice-box {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 13px;
            color: #991b1b;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .fallback {
            border-top: 1px solid #f1f5f9;
            padding-top: 18px;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            word-break: break-all;
        }
        .fallback a {
            color: #dc2626;
            text-decoration: underline;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 18px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <!-- Header -->
            <div class="header">
                <h1>{{ $settings['restaurant_name'] ?? 'Rotisería La Abuela' }}</h1>
                <p>Seguridad y Acceso al Sistema</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">¡Hola, {{ $user->name }}! 👋</div>
                <p class="text">
                    Hemos recibido una solicitud para restablecer la contraseña de tu cuenta de acceso en el panel de <strong>Rotisería La Abuela</strong>.
                </p>
                <p class="text">
                    Para crear una nueva contraseña, haz clic en el siguiente botón:
                </p>

                <div class="btn-container">
                    <a href="{{ $resetUrl }}" target="_blank" class="btn-reset">
                        🔐 Restablecer mi Contraseña
                    </a>
                </div>

                <div class="notice-box">
                    ⏱️ <strong>Nota de Seguridad:</strong> Este enlace expirará en 60 minutos.<br>
                    Si no solicitaste este cambio, puedes ignorar este mensaje con total tranquilidad; tu cuenta sigue estando protegida.
                </div>

                <div class="fallback">
                    Si tienes problemas haciendo clic en el botón, copia y pega la siguiente URL en tu navegador web:<br>
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                Rotisería La Abuela • {{ $settings['address'] ?? 'Av. libertad 5445' }}
            </div>
        </div>
    </div>
</body>
</html>
