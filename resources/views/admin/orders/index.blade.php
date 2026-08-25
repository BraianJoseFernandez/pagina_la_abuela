@extends('layouts.admin')

@section('title', 'Historial de Pedidos WhatsApp')
@section('page-title', 'Historial de Pedidos')

@section('content')
<div class="space-y-6">
    <!-- Encabezado con Filtros por Estado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200/80">
        <div>
            <h3 class="text-xl font-black text-slate-800">Pedidos Armados para WhatsApp</h3>
            <p class="text-xs text-slate-500 mt-0.5">Listado de todos los pedidos creados por los clientes en la carta online</p>
        </div>

        <!-- Filtro por Estado -->
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.orders.index') }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ empty($status) ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Todos
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'enviado_whatsapp']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $status === 'enviado_whatsapp' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fab fa-whatsapp text-emerald-400"></i>
                <span><span class="hidden sm:inline">Enviados por </span>WhatsApp</span>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'en_preparacion']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $status === 'en_preparacion' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fas fa-kitchen-set text-amber-300"></i>
                <span>En Preparación</span>
            </a>
            <a href="{{ route('admin.orders.index', ['status' => 'entregado']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 {{ $status === 'entregado' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <i class="fas fa-check-double text-blue-300"></i>
                <span>Entregados</span>
            </a>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-4 px-6">ID</th>
                        <th class="py-4 px-6">Cliente</th>
                        <th class="py-4 px-6">Tipo Entrega</th>
                        <th class="py-4 px-6">Platos</th>
                        <th class="py-4 px-6">Total</th>
                        <th class="py-4 px-6">Estado</th>
                        <th class="py-4 px-6">Fecha / Hora</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-slate-900">#{{ $order->id }}</td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                <div class="text-xs text-slate-400 flex items-center space-x-1 mt-0.5">
                                    <i class="fab fa-whatsapp text-emerald-500"></i>
                                    <span>{{ $order->customer_phone }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $order->delivery_type === 'delivery' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Retiro' }}
                                </span>
                                @if($order->delivery_address)
                                    <div class="text-xs text-slate-400 truncate max-w-xs mt-1">{{ $order->delivery_address }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="text-xs text-slate-700 font-bold bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $order->items->sum('quantity') }} items
                                </span>
                            </td>
                            <td class="py-4 px-6 font-black text-slate-900 text-base">
                                ${{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if($order->status === 'enviado_whatsapp')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fab fa-whatsapp text-emerald-600 text-xs"></i>
                                        <span><span class="hidden md:inline">Enviado por </span>WhatsApp</span>
                                    </span>
                                @elseif($order->status === 'en_preparacion')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fas fa-kitchen-set text-amber-600 text-xs"></i>
                                        <span>En Preparación</span>
                                    </span>
                                @elseif($order->status === 'entregado')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fas fa-check-double text-blue-600 text-xs"></i>
                                        <span>Entregado</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-400">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition inline-block">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-sm">
                                No se encontraron pedidos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-6 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
