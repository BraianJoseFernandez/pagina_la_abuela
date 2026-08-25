<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\EventSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
                'images' => fn($q) => $q->orderBy('order'),
                'activeProducts' => fn($q) => $q->with(['variants' => fn($v) => $v->orderBy('order')])
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
            'delivery_type' => 'required|in:delivery,takeaway',
            'delivery_address' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string',
            'items.*.variant_name' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.subtotal' => 'required|numeric',
            'items.*.notes' => 'nullable|string',
        ]);

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'delivery_type' => $validated['delivery_type'],
            'delivery_address' => $validated['delivery_address'] ?? null,
            'payment_method' => $validated['payment_method'] ?? 'Efectivo',
            'notes' => $validated['notes'] ?? null,
            'total_amount' => $validated['total_amount'],
            'status' => 'enviado_whatsapp',
        ]);

        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'] ?? null,
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido registrado con éxito.',
            'order_id' => $order->id
        ]);
    }
}
