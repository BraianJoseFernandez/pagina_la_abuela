<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - Rotisería La Abuela</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #e11d48 100%);
            padding: 32px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 14px;
            color: #fecdd3;
            font-style: italic;
        }
        .order-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 14px;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 28px 24px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .intro-text {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .info-box {
            background-color: #f1f5f9;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .info-box-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 4px 8px 4px 0;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
            width: 35%;
        }
        .info-value {
            display: table-cell;
            padding: 4px 0;
            font-size: 13px;
            color: #1e293b;
            font-weight: 700;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 10px 8px;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            vertical-align: top;
        }
        .item-category {
            display: inline-block;
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-size: 10px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .item-title {
            font-weight: 700;
            color: #0f172a;
        }
        .item-variant {
            display: inline-block;
            background-color: #f3e8ff;
            color: #7e22ce;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
            margin-top: 3px;
        }
        .item-cooking {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
            margin-top: 3px;
            margin-left: 4px;
        }
        .item-garnish {
            display: inline-block;
            background-color: #ecfdf5;
            color: #065f46;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
            margin-top: 3px;
            margin-left: 4px;
            border: 1px solid #a7f3d0;
        }
        .item-note {
            font-size: 12px;
            color: #64748b;
            font-style: italic;
            margin-top: 4px;
        }
        .total-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
            border: 1px solid #fecdd3;
            border-radius: 14px;
            padding: 16px;
            text-align: right;
            margin-top: 10px;
            margin-bottom: 28px;
        }
        .total-label {
            font-size: 13px;
            font-weight: 700;
            color: #9f1239;
            text-transform: uppercase;
            margin-right: 8px;
        }
        .total-amount {
            font-size: 24px;
            font-weight: 900;
            color: #e11d48;
        }
        .btn-whatsapp {
            display: block;
            width: fit-content;
            margin: 0 auto;
            background-color: #10b981;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .footer a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <!-- Encabezado -->
            <div class="header">
                <h1>{{ $settings['restaurant_name'] ?? 'Rotisería La Abuela' }}</h1>
                <p>"{{ $settings['restaurant_slogan'] ?? 'Cocinar con amor te alimenta el alma' }}"</p>
                <div class="order-badge">PEDIDO #{{ $order->id }}</div>
            </div>

            <!-- Contenido -->
            <div class="content">
                <div class="greeting">¡Hola, {{ $order->customer_name }}! 👋</div>
                <p class="intro-text">
                    Recibimos tu pedido correctamente. A continuación te dejamos el detalle de tu compra para que tengas el comprobante:
                </p>

                <!-- Datos de Entrega -->
                <div class="info-box">
                    <div class="info-box-title">📋 Datos de la Orden</div>
                    <div class="info-grid">
                        <div class="info-row">
                            <span class="info-label">Modalidad:</span>
                            <span class="info-value">
                                {{ $order->delivery_type === 'delivery' ? '🛵 Envío a Domicilio' : '🛍️ Retiro en el Local' }}
                            </span>
                        </div>
                        @if($order->delivery_type === 'delivery' && $order->delivery_address)
                            <div class="info-row">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value">📍 {{ $order->delivery_address }}</span>
                            </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value">📞 {{ $order->customer_phone }}</span>
                        </div>
                        @if($order->payment_method)
                            <div class="info-row">
                                <span class="info-label">Pago:</span>
                                <span class="info-value">💳 {{ $order->payment_method }}</span>
                            </div>
                        @endif
                        @if($order->notes)
                            <div class="info-row">
                                <span class="info-label">Aclaraciones:</span>
                                <span class="info-value">📝 {{ $order->notes }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tabla de Platos -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Plato / Detalle</th>
                            <th style="text-align: center; width: 60px;">Cant.</th>
                            <th style="text-align: right; width: 90px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    @if($item->category_name)
                                        <div><span class="item-category">{{ $item->category_name }}</span></div>
                                    @endif
                                    <div class="item-title">{{ $item->product_name }}</div>
                                    @if($item->variant_name)
                                        <span class="item-variant">{{ $item->variant_name }}</span>
                                    @endif
                                    @if($item->cooking_method)
                                        <span class="item-cooking">
                                            {{ in_array($item->cooking_method, ['Horno', 'Al Horno', '🔥 Horno']) ? '🔥 Horno' : ($item->cooking_method === 'Frita' ? '🍳 Frita' : $item->cooking_method) }}
                                        </span>
                                    @endif
                                    @if($item->garnish_name)
                                        <span class="item-garnish">
                                            🥗 Guarnición: {{ $item->garnish_name }}{{ $item->garnish_price > 0 ? ' (+$' . number_format($item->garnish_price, 0, ',', '.') . ')' : '' }}
                                        </span>
                                    @endif
                                    @if($item->notes)
                                        <div class="item-note">Nota: {{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #475569;">
                                    {{ $item->quantity }}x
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #0f172a;">
                                    ${{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Monto Total -->
                <div class="total-box">
                    <span class="total-label">Total a pagar:</span>
                    <span class="total-amount">${{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>

                <!-- Botón de Contacto WhatsApp -->
                @php
                    $wpPhone = preg_replace('/\D/', '', $settings['whatsapp_phone'] ?? '5493794565528');
                @endphp
                <a href="https://wa.me/{{ $wpPhone }}?text={{ urlencode('¡Hola! Tengo una consulta sobre mi pedido #' . $order->id) }}"
                   target="_blank" class="btn-whatsapp">
                    💬 Consultar por WhatsApp
                </a>
            </div>

            <!-- Pie de Email -->
            <div class="footer">
                <p style="margin: 0 0 6px 0;">
                    📍 {{ $settings['address'] ?? 'Av. libertad 5445' }} — Corrientes, Argentina
                </p>
                <p style="margin: 0;">
                    Rotisería La Abuela • <a href="{{ config('app.url') }}">Ver nuestra carta</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
