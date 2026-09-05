<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmationMail;
use App\Models\Category;
use App\Models\EventSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PublicMenuController extends Controller
{
    /**
     * Muestra la carta pública del restaurante.
     */
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('order')
            ->get();

        $event = EventSetting::where('is_active', true)->first();

        $settings = Setting::all()->pluck('value', 'key');

        return view('public.index', compact('categories', 'event', 'settings'));
    }

    /**
     * Obtiene el HTML / datos de una categoría específica para la transición dinámica.
     */
    public function getCategory(string $slug, Request $request)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'images' => fn($q) => $q->where('is_visible', true)->orderBy('order'),
                'activeProducts' => fn($q) => $q->with([
                    'category',
                    'variants' => fn($v) => $v->orderBy('order'),
                    'garnishes' => fn($g) => $g->where('is_available', true)->orderBy('order')
                ])
            ])
            ->firstOrFail();

        if ($request->wantsJson()) {
            return response()->json([
                'category' => $category,
                'html' => view('public.partials.category_content', compact('category'))->render()
            ]);
        }

        return view('public.partials.category_content', compact('category'));
    }

    /**
     * Registra un pedido realizado desde el carrito antes de redirigir a WhatsApp.
     */
    public function saveOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'delivery_type' => 'required|in:delivery,takeaway',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_map_url' => 'nullable|string|max:1000',
            'delivery_latitude' => 'nullable|numeric',
            'delivery_longitude' => 'nullable|numeric',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.category_name' => 'nullable|string|max:255',
            'items.*.product_name' => 'required|string',
            'items.*.variant_name' => 'nullable|string',
            'items.*.cooking_method' => 'nullable|string',
            'items.*.garnish_name' => 'nullable|string',
            'items.*.garnish_price' => 'nullable|numeric',
            'items.*.unit_price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.subtotal' => 'required|numeric',
            'items.*.notes' => 'nullable|string',
        ], [
            'customer_name.required' => 'Por favor, ingresa tu nombre completo.',
            'customer_phone.required' => 'Por favor, ingresa tu número de teléfono o WhatsApp.',
            'customer_email.email' => 'Por favor, ingresa un correo electrónico válido.',
            'delivery_type.required' => 'Debes seleccionar si el pedido es para envío o retiro en el local.',
            'delivery_type.in' => 'La modalidad de entrega seleccionada no es válida.',
            'total_amount.required' => 'El monto total del pedido es obligatorio.',
            'items.required' => 'El carrito no contiene productos.',
            'items.min' => 'Debes agregar al menos un producto al pedido.',
        ]);

        if ($validated['delivery_type'] === 'delivery') {
            $hasAddress = !empty(trim($validated['delivery_address'] ?? ''));
            $hasMap = !empty(trim($validated['delivery_map_url'] ?? '')) || (!empty($validated['delivery_latitude']) && !empty($validated['delivery_longitude']));
            if (!$hasAddress && !$hasMap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Para envíos a domicilio debes ingresar una dirección o marcar tu ubicación en el mapa.'
                ], 422);
            }
        }

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'delivery_type' => $validated['delivery_type'],
            'delivery_address' => $validated['delivery_address'] ?? null,
            'delivery_map_url' => $validated['delivery_map_url'] ?? null,
            'delivery_latitude' => $validated['delivery_latitude'] ?? null,
            'delivery_longitude' => $validated['delivery_longitude'] ?? null,
            'payment_method' => $validated['payment_method'] ?? 'Efectivo',
            'notes' => $validated['notes'] ?? null,
            'total_amount' => $validated['total_amount'],
            'status' => 'enviado_whatsapp',
        ]);

        foreach ($validated['items'] as $item) {
            $categoryName = $item['category_name'] ?? null;
            if (!$categoryName && !empty($item['product_id'])) {
                $matchedProd = Product::with('category')->find($item['product_id']);
                $categoryName = $matchedProd?->category?->name;
            }
            if (!$categoryName && !empty($item['product_name'])) {
                $matchedProd = Product::with('category')->where('name', $item['product_name'])->first();
                $categoryName = $matchedProd?->category?->name;
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'category_name' => $categoryName,
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'] ?? null,
                'cooking_method' => $item['cooking_method'] ?? null,
                'garnish_name' => $item['garnish_name'] ?? null,
                'garnish_price' => $item['garnish_price'] ?? 0.00,
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        // Si el cliente indicó un correo electrónico, enviamos confirmación del pedido
        if (!empty($order->customer_email)) {
            try {
                Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
            } catch (\Throwable $e) {
                Log::error('Error enviando email de confirmación de pedido #' . $order->id . ': ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido registrado con éxito.',
            'order_id' => $order->id
        ]);
    }

    /**
     * Resuelve URLs de Google Maps (incluyendo acortadas como maps.app.goo.gl)
     * para extraer coordenadas exactas de entrega.
     */
    public function resolveMapsUrl(Request $request)
    {
        $url = trim($request->input('url', ''));
        if (empty($url)) {
            return response()->json(['success' => false, 'message' => 'Enlace no proporcionado.'], 422);
        }

        // 1. Extraer coordenadas si ya están explícitas en el texto o URL
        if (preg_match('/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/', $url, $matches) ||
            preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches) ||
            preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches) ||
            preg_match('/(?:place|search)\/(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches) ||
            preg_match('/geo:(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return response()->json([
                'success' => true,
                'lat' => (float)$matches[1],
                'lng' => (float)$matches[2],
                'url' => $url
            ]);
        }

        // 2. Si es una URL acortada tipo maps.app.goo.gl o goo.gl/maps, seguir redirecciones
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            curl_exec($ch);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            if ($effectiveUrl) {
                if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $effectiveUrl, $matches) ||
                    preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $effectiveUrl, $matches) ||
                    preg_match('/(?:place|search)\/(-?\d+\.\d+),(-?\d+\.\d+)/', $effectiveUrl, $matches)) {
                    return response()->json([
                        'success' => true,
                        'lat' => (float)$matches[1],
                        'lng' => (float)$matches[2],
                        'url' => $effectiveUrl
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error resolviendo URL de Google Maps: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'No pudimos extraer las coordenadas exactas de este enlace. Por favor selecciona el punto en el mapa o busca tu calle y número.'
        ], 400);
    }
}

