@extends('layouts.admin')

@section('title', 'Historial de Pedidos WhatsApp')
@section('page-title', 'Historial de Pedidos')

@section('content')
<div class="space-y-6">
    <!-- Barra Superior de Filtros: Fecha / Turnos / Estados -->
    <div class="bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/80 space-y-4">
        <!-- Fila 1: Título y Selector de Fecha -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-slate-800">Pedidos Armados para WhatsApp</h3>
                <p class="text-xs text-slate-500 mt-0.5">Historial y control operativo de ventas y entregas</p>
            </div>

            <!-- Selector de Calendario y Botones de Jornada -->
            <form method="GET" action="{{ route('admin.orders.index') }}" id="order-date-filter-form" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input type="hidden" name="shift" id="shift-input" value="{{ $shift }}">

                <!-- Input Flatpickr para selección de fecha -->
                <div class="relative flex-grow sm:w-44">
                    <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-rose-500 text-xs pointer-events-none"></i>
                    <input type="text" name="date" id="order-date-picker" value="{{ ($date && $date !== 'all') ? $date : '' }}"
                           placeholder="Elegir fecha..."
                           class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">
                    @if($date && $date !== 'all')
                        <button type="button" onclick="clearDateFilter()" title="Quitar fecha"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 text-xs">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    @endif
                </div>

                <!-- Botones rápidos de jornada (3 columnas iguales en móvil) -->
                <div class="grid grid-cols-3 sm:flex rounded-xl bg-slate-100 p-1 gap-1 text-center flex-shrink-0">
                    <a href="{{ route('admin.orders.index', array_filter(['date' => $currentBusinessDate, 'shift' => $shift, 'status' => $status])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $date === $currentBusinessDate ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Hoy
                    </a>

                    @php
                        $yesterdayBusinessDate = \Carbon\Carbon::parse($currentBusinessDate)->subDay()->format('Y-m-d');
                    @endphp
                    <a href="{{ route('admin.orders.index', array_filter(['date' => $yesterdayBusinessDate, 'shift' => $shift, 'status' => $status])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $date === $yesterdayBusinessDate ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Ayer
                    </a>

                    <a href="{{ route('admin.orders.index', array_filter(['date' => 'all', 'status' => $status])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($date === 'all') ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Todas
                    </a>
                </div>
            </form>
        </div>

        <!-- Fila 2: Selector de Turno y Resumen Económico -->
        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 flex items-center space-x-1 flex-shrink-0">
                    <i class="fas fa-business-time text-purple-600"></i>
                    <span>Turno:</span>
                </span>

                <div class="grid grid-cols-3 sm:flex gap-1.5 w-full sm:w-auto">
                    <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => 'completo', 'status' => $status])) }}"
                       class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition text-center flex items-center justify-center space-x-1 {{ $shift === 'completo' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fas fa-clock text-xs"></i>
                        <span>Todo el día</span>
                        <span class="opacity-80 font-normal hidden lg:inline ml-1">(08 a 03 hs)</span>
                    </a>

                    <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => 'manana', 'status' => $status])) }}"
                       class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition text-center flex items-center justify-center space-x-1 {{ $shift === 'manana' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fas fa-sun text-amber-300 text-xs"></i>
                        <span>Mañana</span>
                        <span class="opacity-80 font-normal hidden lg:inline ml-1">(08 a 16 hs)</span>
                    </a>

                    <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => 'tarde', 'status' => $status])) }}"
                       class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition text-center flex items-center justify-center space-x-1 {{ ($shift === 'tarde' || $shift === 'noche') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fas fa-moon text-indigo-300 text-xs"></i>
                        <span>Tarde</span>
                        <span class="opacity-80 font-normal hidden lg:inline ml-1">(16:01 a 03 hs)</span>
                    </a>
                </div>
            </div>

            <!-- Resumen de Pedidos y Monto en la selección actual -->
            <div class="text-xs font-bold text-slate-700 bg-slate-50 px-3.5 py-1.5 rounded-xl border border-slate-200/80 flex items-center justify-between sm:justify-start space-x-2 flex-shrink-0">
                <span>{{ $totalOrdersFiltered }} pedidos</span>
                <span class="text-slate-300">|</span>
                <span class="text-emerald-700 font-black">${{ number_format($totalAmountFiltered, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Fila 3: Filtro por Estado con scroll táctil suave -->
        <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 flex items-center space-x-1 flex-shrink-0">
                <i class="fas fa-filter text-slate-400"></i>
                <span>Estado:</span>
            </span>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 min-w-0 flex-grow touch-pan-x -mx-1 px-1">
                <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => $shift])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex-shrink-0 {{ empty($status) ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Todos los estados
                </a>

                <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'enviado_whatsapp'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 whitespace-nowrap flex-shrink-0 {{ $status === 'enviado_whatsapp' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fab fa-whatsapp text-emerald-400"></i>
                    <span>WhatsApp</span>
                </a>

                <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'en_preparacion'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 whitespace-nowrap flex-shrink-0 {{ $status === 'en_preparacion' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-kitchen-set text-amber-300"></i>
                    <span>En Preparación</span>
                </a>

                <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'entregado'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 whitespace-nowrap flex-shrink-0 {{ $status === 'entregado' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-check-double text-blue-300"></i>
                    <span>Entregados</span>
                </a>

                <a href="{{ route('admin.orders.index', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'cancelado'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 whitespace-nowrap flex-shrink-0 {{ $status === 'cancelado' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-rose-700 hover:bg-rose-50' }}">
                    <i class="fas fa-times-circle text-rose-300"></i>
                    <span>Cancelados</span>
                </a>
            </div>
        </div>

        <!-- Fila 4: Banner de Turno Activo -->
        @if($shiftInfo)
            <div class="p-3 bg-purple-50/70 border border-purple-100 rounded-2xl text-xs text-purple-800 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-calendar-check text-purple-600"></i>
                    <span><strong>Jornada:</strong> {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} — {{ $shiftInfo['label'] }}</span>
                </span>
                <span class="text-[11px] text-purple-600 font-mono">
                    {{ $shiftInfo['start']->format('d/m H:i') }} hs hasta {{ $shiftInfo['end']->format('d/m H:i') }} hs
                </span>
            </div>
        @endif
    </div>

    <!-- Contenedor de Pedidos (Desktop: Tabla / Mobile: Tarjetas) -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <!-- Vista Desktop: Tabla -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-3.5">ID</th>
                        <th class="py-3 px-3.5">Cliente</th>
                        <th class="py-3 px-3.5">Tipo Entrega</th>
                        <th class="py-3 px-3.5">Platos</th>
                        <th class="py-3 px-3.5">Total</th>
                        <th class="py-3 px-3.5">Estado</th>
                        <th class="py-3 px-3.5">Fecha / Hora</th>
                        <th class="py-3 px-4 text-right sticky-action-col bg-slate-50 whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="py-3.5 px-3.5 font-bold text-slate-900">#{{ $order->id }}</td>
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                <div class="text-xs text-slate-400 flex items-center space-x-1 mt-0.5">
                                    <i class="fab fa-whatsapp text-emerald-500"></i>
                                    <span>{{ $order->customer_phone }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $order->delivery_type === 'delivery' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Retiro' }}
                                </span>
                                @if($order->delivery_address)
                                    <div class="text-xs text-slate-400 truncate max-w-xs mt-1">{{ $order->delivery_address }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5">
                                <span class="text-xs text-slate-700 font-bold bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $order->items->sum('quantity') }} items
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 font-black text-slate-900 text-base">
                                ${{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                @if($order->status === 'enviado_whatsapp')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fab fa-whatsapp text-emerald-600 text-xs"></i>
                                        <span>WhatsApp</span>
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
                                @elseif($order->status === 'cancelado')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fas fa-times-circle text-rose-500 text-xs"></i>
                                        <span>Cancelado</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5 text-xs text-slate-400 whitespace-nowrap">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 sticky-action-col bg-white group-hover:bg-slate-50 transition-colors whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', ['order' => $order, 'return_url' => request()->fullUrl()]) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition inline-block">
                                    Ver Detalle
                                </a>
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar pedido #{{ $order->id }}? Se descontará de las ventas y métricas.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Eliminar pedido">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-sm">
                                No se encontraron pedidos con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Mobile: Tarjetas Adaptativas (Perfecto para iPhone 15 Pro Max sin scroll horizontal) -->
        <div class="block lg:hidden p-3 space-y-3 bg-slate-100/60">
            @forelse($orders as $order)
                <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
                    <!-- Fila Superior: ID + Tipo Entrega + Estado -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-black text-slate-900 text-sm bg-slate-100 px-2 py-1 rounded-lg">
                                #{{ $order->id }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $order->delivery_type === 'delivery' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Retiro' }}
                            </span>
                        </div>

                        <!-- Estado -->
                        <div>
                            @if($order->status === 'enviado_whatsapp')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fab fa-whatsapp text-emerald-600 text-[10px]"></i>
                                    <span>WhatsApp</span>
                                </span>
                            @elseif($order->status === 'en_preparacion')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fas fa-kitchen-set text-amber-600 text-[10px]"></i>
                                    <span>En Prep.</span>
                                </span>
                            @elseif($order->status === 'entregado')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fas fa-check-double text-blue-600 text-[10px]"></i>
                                    <span>Entregado</span>
                                </span>
                            @elseif($order->status === 'cancelado')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="fas fa-times-circle text-rose-500 text-[10px]"></i>
                                    <span>Cancelado</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Fila Central: Datos de Cliente + WhatsApp + Dirección -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-base leading-snug">{{ $order->customer_name }}</h4>
                            <span class="text-xs text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/\D/', '', $order->customer_phone) }}" target="_blank"
                               class="inline-flex items-center space-x-1 text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded-lg">
                                <i class="fab fa-whatsapp"></i>
                                <span>{{ $order->customer_phone }}</span>
                            </a>
                            <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg">
                                {{ $order->items->sum('quantity') }} items
                            </span>
                        </div>
                        @if($order->delivery_address)
                            <p class="text-xs text-slate-500 flex items-start space-x-1 pt-1">
                                <i class="fas fa-map-marker-alt text-slate-400 mt-0.5 text-[11px]"></i>
                                <span class="leading-tight">{{ $order->delivery_address }}</span>
                            </p>
                        @endif
                    </div>

                    <!-- Fila Inferior: Total y Botón Ver Detalle (Ocupa ancho natural sin scroll lateral) -->
                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total</span>
                            <span class="text-lg font-black text-slate-900">
                                ${{ number_format($order->total_amount, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('¿Eliminar pedido #{{ $order->id }}? Se descontará de las ventas y métricas.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Eliminar pedido">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.orders.show', ['order' => $order, 'return_url' => request()->fullUrl()]) }}"
                               class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center space-x-1.5 shadow-xs">
                                <span>Ver Detalle</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-slate-400 text-xs bg-white rounded-2xl p-6">
                    No se encontraron pedidos con los filtros seleccionados.
                </div>
            @endforelse
        </div>

        @if($orders->hasPages())
            <div class="p-6 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr('#order-date-picker', {
            locale: 'es',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: false,
            defaultDate: "{{ ($date && $date !== 'all') ? $date : '' }}",
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    const form = document.getElementById('order-date-filter-form');
                    form.submit();
                }
            }
        });
    });

    function clearDateFilter() {
        const input = document.getElementById('order-date-picker');
        input.value = '';
        const form = document.getElementById('order-date-filter-form');
        const hiddenDate = form.querySelector('input[name="date"]');
        if (hiddenDate) hiddenDate.value = 'all';
        form.submit();
    }
</script>
@endpush
@endsection
