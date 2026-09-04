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
        $totalAmountFiltered = (clone $query)->sum('total_amount');

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
        return view('admin.orders.show', compact('order'));
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
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Pedido eliminado del historial.');
    }
}
