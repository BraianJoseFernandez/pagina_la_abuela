<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function status(): JsonResponse
    {
        return response()->json($this->whatsAppService->getStatus());
    }

    public function qr(): JsonResponse
    {
        return response()->json($this->whatsAppService->getQr());
    }

    public function disconnect(): JsonResponse
    {
        return response()->json($this->whatsAppService->disconnect());
    }

    /**
     * Despacha una comanda de pedido directamente al WhatsApp del cadete en segundo plano.
     */
    public function dispatchOrder(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'cadete_name' => 'required|string|max:100',
            'cadete_phone' => 'required|string|max:50',
            'comment' => 'nullable|string|max:500'
        ]);

        $cadetePhone = preg_replace('/\D/', '', $validated['cadete_phone']);
        if (empty($cadetePhone)) {
            return response()->json([
                'success' => false,
                'error' => 'El número de teléfono del cadete no es válido.'
            ], 422);
        }

        // Construir el texto estructurado del pedido
        $msg = "🛵 *PEDIDO PARA ENTREGA #{$order->id}*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "👤 *Cliente:* {$order->customer_name}\n";
        $msg .= "📞 *Teléfono:* {$order->customer_phone}\n";

        if ($order->delivery_address) {
            $msg .= "📍 *Dirección:* {$order->delivery_address}\n";
        }
        if ($order->delivery_map_url) {
            $msg .= "🗺️ *Ubicación GPS (Google Maps):* {$order->delivery_map_url}\n";
        }
        if (!$order->delivery_address && $order->delivery_map_url) {
            $msg .= "📍 *Dirección:* Ubicación exacta en el mapa (toca el enlace de Google Maps)\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📋 *DETALLE DE LA COMIDA:*\n";

        foreach ($order->items as $item) {
            $details = [];
            if ($item->variant_name) $details[] = $item->variant_name;
            if ($item->cooking_method) $details[] = $item->cooking_method;
            $detailsText = count($details) > 0 ? ' (' . implode(' - ', $details) . ')' : '';
            $msg .= "• *{$item->quantity}x* {$item->product_name}{$detailsText}\n";
            if ($item->notes) {
                $msg .= "   └ _Nota: {$item->notes}_\n";
            }
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";

        $totalFormatted = '$' . number_format($order->total_amount, 0, ',', '.');
        if (stripos($order->payment_method ?? 'Efectivo', 'efectivo') !== false) {
            $msg .= "💵 *COBRAR AL CLIENTE EN EFECTIVO:* *{$totalFormatted}*\n";
        } else {
            $msg .= "💳 *PAGO:* *PAGADO ({$order->payment_method}) - NO COBRAR AL CLIENTE*\n";
        }

        if ($order->customer_notes) {
            $msg .= "📝 *Nota del cliente:* {$order->customer_notes}\n";
        }

        if (!empty($validated['comment'])) {
            $msg .= "⚠️ *INSTRUCCIÓN DE CAJA:* {$validated['comment']}\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "_¡Buen viaje y con cuidado!_ 🛵💨";

        // URL manual de respaldo por si el servicio no está conectado
        $manualUrl = "https://web.whatsapp.com/send?phone={$cadetePhone}&text=" . urlencode($msg);

        // Despachar a través del servicio Baileys
        $sendResult = $this->whatsAppService->sendMessage($cadetePhone, $msg);

        if ($sendResult['success']) {
            return response()->json([
                'success' => true,
                'message' => "Comanda enviada directamente al WhatsApp de {$validated['cadete_name']}."
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $sendResult['error'] ?? 'No se pudo enviar el mensaje por WhatsApp.',
            'fallback_url' => $manualUrl
        ], 503);
    }
}
