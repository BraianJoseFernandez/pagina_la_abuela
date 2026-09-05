<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\BusinessShiftService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $date = $request->query('date');
        $shift = $request->query('shift', 'completo');

        $currentBusinessDate = BusinessShiftService::getCurrentBusinessDate();
        $activeShift = BusinessShiftService::getCurrentActiveShift();

        $query = Order::with('items')->latest();

        // Filtro por fecha operativa y turno
        $shiftInfo = null;
        if ($date) {
            $shiftInfo = BusinessShiftService::getShiftRange($date, $shift);
            $query->whereBetween('created_at', [$shiftInfo['start'], $shiftInfo['end']]);
        }

        // Filtro por estado
        if ($status && in_array($status, ['enviado_whatsapp', 'en_preparacion', 'entregado', 'cancelado'])) {
            $query->where('status', $status);
        }

        // Métricas resumidas de la consulta actual (para mostrar en cabecera)
        $totalOrdersFiltered = (clone $query)->count();

        // La cuenta de ventas no debe contabilizar los pedidos cancelados ni borrados (SoftDeletes)
        $totalAmountQuery = clone $query;
        if ($status !== 'cancelado') {
            $totalAmountQuery->whereNotIn('status', ['cancelado']);
        }
        $totalAmountFiltered = $totalAmountQuery->sum('total_amount');

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact(
            'orders',
            'status',
            'date',
            'shift',
            'shiftInfo',
            'currentBusinessDate',
            'activeShift',
            'totalOrdersFiltered',
            'totalAmountFiltered'
        ));
    }

    public function show(Order $order): View
    {
        $order->load('items');
        $cadetesJson = \App\Models\Setting::get('delivery_cadetes');
        $allCadetes = $cadetesJson ? json_decode($cadetesJson, true) : [];
        // Filtrar estrictamente cadetes que estén activos y tengan teléfono
        $cadetes = array_values(array_filter($allCadetes, function ($c) {
            $isActive = !isset($c['is_active']) || $c['is_active'] === true || $c['is_active'] === 1 || $c['is_active'] === '1' || $c['is_active'] === 'true';
            return $isActive && !empty(trim($c['phone'] ?? ''));
        }));

        return view('admin.orders.show', compact('order', 'cadetes'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:enviado_whatsapp,en_preparacion,entregado,cancelado',
        ], [
            'status.required' => 'Debes indicar el nuevo estado del pedido.',
            'status.in' => 'El estado seleccionado no es válido.',
        ]);

        $order->update(['status' => $request->input('status')]);

        return redirect()->back()->with('success', 'Estado del pedido #' . $order->id . ' actualizado.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $orderId = $order->id;
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Pedido #' . $orderId . ' eliminado correctamente del sistema.');
    }
}
