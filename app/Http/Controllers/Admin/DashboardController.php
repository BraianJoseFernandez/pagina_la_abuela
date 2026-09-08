<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EventSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BusinessShiftService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $currentBusinessDate = BusinessShiftService::getCurrentBusinessDate();
        $activeShift = BusinessShiftService::getCurrentActiveShift();

        // Si se pasa 'date', se usa ese valor; si no se pasa nada, por defecto se toma la jornada actual.
        // Si se pasa date=all, se consideran todas las fechas.
        $date = $request->has('date') ? $request->query('date') : $currentBusinessDate;
        $shift = $request->query('shift', 'completo');
        $status = $request->query('status');

        $categoriesCount = Category::count();
        $productsCount = Product::count();
        $availableProductsCount = Product::where('is_available', true)->count();
        $usersCount = User::count();
        $totalAllTimeOrders = Order::whereNotIn('status', ['cancelado'])->count();

        // Consulta base para la jornada/turno
        $shiftBaseQuery = Order::query();
        $shiftInfo = null;

        if ($date && $date !== 'all') {
            $shiftInfo = BusinessShiftService::getShiftRange($date, $shift);
            $shiftBaseQuery->whereBetween('created_at', [$shiftInfo['start'], $shiftInfo['end']]);
        }

        // Métricas de la jornada / turno seleccionado:
        // Las ventas contabilizan pedidos activos y entregados, excluyendo estrictamente cancelados y borrados (SoftDeletes)
        $shiftOrdersCount = (clone $shiftBaseQuery)->count();
        $shiftTotalSales = (clone $shiftBaseQuery)->whereNotIn('status', ['cancelado'])->sum('total_amount');
        $shiftDeliveredCount = (clone $shiftBaseQuery)->where('status', 'entregado')->count();
        $shiftPendingCount = (clone $shiftBaseQuery)->whereIn('status', ['enviado_whatsapp', 'en_preparacion'])->count();
        $shiftCancelledCount = (clone $shiftBaseQuery)->where('status', 'cancelado')->count();

        // Consulta filtrada para la tabla/tarjetas de pedidos de la jornada según estado
        $ordersQuery = clone $shiftBaseQuery;
        if ($status && in_array($status, ['enviado_whatsapp', 'en_preparacion', 'entregado', 'cancelado'])) {
            $ordersQuery->where('status', $status);
        }

        // Pedidos a mostrar en la tabla/tarjetas de la jornada (hasta 15 pedidos)
        $recentOrders = $ordersQuery->with('items')->latest()->take(15)->get();
        $activeEvent = EventSetting::where('is_active', true)->first();

        return view('admin.dashboard', compact(
            'categoriesCount',
            'productsCount',
            'availableProductsCount',
            'usersCount',
            'totalAllTimeOrders',
            'recentOrders',
            'activeEvent',
            'date',
            'shift',
            'status',
            'shiftInfo',
            'currentBusinessDate',
            'activeShift',
            'shiftOrdersCount',
            'shiftTotalSales',
            'shiftDeliveredCount',
            'shiftPendingCount',
            'shiftCancelledCount'
        ));
    }

    /**
     * Endpoint ligero para sondeo en tiempo real de nuevos pedidos y métricas de la jornada
     */
    public function liveOrders(Request $request)
    {
        $currentBusinessDate = BusinessShiftService::getCurrentBusinessDate();
        $date = $request->has('date') ? $request->query('date') : $currentBusinessDate;
        $shift = $request->query('shift', 'completo');
        $status = $request->query('status');
        $lastOrderId = $request->has('last_order_id') ? (int) $request->query('last_order_id') : null;

        // Consulta base para la jornada/turno
        $shiftBaseQuery = Order::query();
        if ($date && $date !== 'all') {
            $shiftInfo = BusinessShiftService::getShiftRange($date, $shift);
            $shiftBaseQuery->whereBetween('created_at', [$shiftInfo['start'], $shiftInfo['end']]);
        }

        // Métricas de la jornada / turno
        $shiftOrdersCount = (clone $shiftBaseQuery)->count();
        $shiftTotalSales = (clone $shiftBaseQuery)->whereNotIn('status', ['cancelado'])->sum('total_amount');
        $shiftDeliveredCount = (clone $shiftBaseQuery)->where('status', 'entregado')->count();
        $shiftPendingCount = (clone $shiftBaseQuery)->whereIn('status', ['enviado_whatsapp', 'en_preparacion'])->count();
        $shiftCancelledCount = (clone $shiftBaseQuery)->where('status', 'cancelado')->count();

        // Consulta para pedidos recientes
        $ordersQuery = clone $shiftBaseQuery;
        if ($status && in_array($status, ['enviado_whatsapp', 'en_preparacion', 'entregado', 'cancelado'])) {
            $ordersQuery->where('status', $status);
        }

        $recentOrders = $ordersQuery->with('items')->latest()->take(15)->get();
        $latestOrderId = $recentOrders->first()?->id ?? 0;

        // Detectar si hay nuevos pedidos respecto al last_order_id
        $hasNew = ($lastOrderId !== null && $latestOrderId > $lastOrderId);
        $newOrders = [];

        if ($hasNew) {
            $newOrders = $recentOrders->where('id', '>', $lastOrderId)->map(function ($o) {
                return [
                    'id' => $o->id,
                    'customer_name' => $o->customer_name,
                    'total_amount' => '$' . number_format($o->total_amount, 0, ',', '.'),
                    'delivery_type' => $o->delivery_type === 'delivery' ? 'Delivery' : 'Retiro en Local',
                ];
            })->values()->all();
        }

        $formattedTotalSales = number_format($shiftTotalSales, 0, ',', '.');
        $previewTotalSales = '$' . rtrim(substr($formattedTotalSales, 0, 4), '.') . '....';

        return response()->json([
            'success' => true,
            'latest_order_id' => $latestOrderId,
            'has_new' => $hasNew,
            'new_orders' => $newOrders,
            'metrics' => [
                'shiftOrdersCount' => $shiftOrdersCount,
                'shiftTotalSales' => $shiftTotalSales,
                'shiftTotalSalesFormatted' => '$' . $formattedTotalSales,
                'shiftTotalSalesPreview' => $previewTotalSales,
                'shiftDeliveredCount' => $shiftDeliveredCount,
                'shiftPendingCount' => $shiftPendingCount,
                'shiftCancelledCount' => $shiftCancelledCount,
            ],
            'html_desktop' => view('admin.partials.dashboard_orders_desktop', compact('recentOrders'))->render(),
            'html_mobile' => view('admin.partials.dashboard_orders_mobile', compact('recentOrders'))->render(),
        ]);
    }
}

