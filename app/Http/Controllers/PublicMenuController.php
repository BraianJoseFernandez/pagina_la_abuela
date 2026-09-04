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
                'activeProducts' => fn($q) => $q->with(['category', 'variants' => fn($v) => $v->orderBy('order')])
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
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.category_name' => 'nullable|string|max:255',
            'items.*.product_name' => 'required|string',
            'items.*.variant_name' => 'nullable|string',
            'items.*.cooking_method' => 'nullable|string',
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

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'delivery_type' => $validated['delivery_type'],
            'delivery_address' => $validated['delivery_address'] ?? null,
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
}
