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
        $currentBusinessDate = BusinessShiftService::getCurrentBusinessDate();
        $activeShift = BusinessShiftService::getCurrentActiveShift();

        // Si la petición trae parámetros explícitos, los guardamos en la sesión
        if ($request->has('date') || $request->has('shift') || $request->has('status')) {
            $status = $request->query('status');
            $date = $request->query('date');
            $shift = $request->query('shift', 'completo');

            session(['admin_orders_filter' => [
                'status' => $status,
                'date' => $date,
                'shift' => $shift,
            ]]);
        } elseif (session()->has('admin_orders_filter')) {
            // Si no vienen parámetros pero tenemos una selección previa en sesión
            $saved = session('admin_orders_filter');
            $status = $saved['status'] ?? null;
            $date = $saved['date'] ?? $currentBusinessDate;
            $shift = $saved['shift'] ?? 'completo';
        } else {
            // Por defecto: jornada actual
            $status = null;
            $date = $currentBusinessDate;
            $shift = 'completo';
        }

        // Recordar la URL completa del listado para retornos
        session(['admin_orders_return_url' => $request->fullUrl()]);

        $query = Order::with('items')->latest();

        // Filtro por fecha operativa y turno (date === 'all' significa todas las fechas)
        $shiftInfo = null;
        if ($date && $date !== 'all') {
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

    public function show(Request $request, Order $order): View
    {
        $order->load('items');

        // Si viene return_url explícito o en referer previo, guardarlo en sesión
        if ($request->has('return_url')) {
            session(['admin_orders_return_url' => $request->query('return_url')]);
        } elseif ($request->headers->has('referer') && !str_contains($request->headers->get('referer'), '/orders/' . $order->id)) {
            session(['admin_orders_return_url' => $request->headers->get('referer')]);
        }

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
        $returnUrl = session('admin_orders_return_url') ?? route('admin.orders.index');
        return redirect()->to($returnUrl)->with('success', 'Pedido #' . $orderId . ' eliminado correctamente del sistema.');
    }
}
