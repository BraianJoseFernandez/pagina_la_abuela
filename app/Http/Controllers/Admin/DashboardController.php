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
        $totalAllTimeOrders = Order::count();

        // Consulta filtrada para la jornada/turno
        $ordersQuery = Order::query();
        $shiftInfo = null;

        if ($date && $date !== 'all') {
            $shiftInfo = BusinessShiftService::getShiftRange($date, $shift);
            $ordersQuery->whereBetween('created_at', [$shiftInfo['start'], $shiftInfo['end']]);
        }

        if ($status && in_array($status, ['enviado_whatsapp', 'en_preparacion', 'entregado', 'cancelado'])) {
            $ordersQuery->where('status', $status);
        }

        // Métricas de la jornada / turno seleccionado
        $shiftOrdersCount = (clone $ordersQuery)->count();
        $shiftTotalSales = (clone $ordersQuery)->where('status', '!=', 'cancelado')->sum('total_amount');
        $shiftDeliveredCount = (clone $ordersQuery)->where('status', 'entregado')->count();
        $shiftPendingCount = (clone $ordersQuery)->whereIn('status', ['enviado_whatsapp', 'en_preparacion'])->count();
        $shiftCancelledCount = (clone $ordersQuery)->where('status', 'cancelado')->count();

        // Pedidos a mostrar en la tabla/tarjetas de la jornada (hasta 15 pedidos)
        $recentOrders = (clone $ordersQuery)->with('items')->latest()->take(15)->get();
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
}
