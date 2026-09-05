@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Resumen General')

@section('content')
<div class="space-y-8">
    <!-- Barra de Filtros de Jornada y Turno para el Dashboard -->
    <div class="bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/80 space-y-4">
        <!-- Fila 1: Título y Selector de Fecha -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-slate-800">Panel Operativo de Jornada</h3>
                <p class="text-xs text-slate-500 mt-0.5">Métricas y pedidos correspondientes al turno seleccionado</p>
            </div>

            <!-- Selector de Calendario y Botones de Jornada -->
            <form method="GET" action="{{ route('admin.dashboard') }}" id="dashboard-date-filter-form" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <input type="hidden" name="shift" id="dash-shift-input" value="{{ $shift }}">

                <!-- Input Flatpickr para selección de fecha -->
                <div class="relative flex-grow sm:w-44">
                    <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-rose-500 text-xs pointer-events-none"></i>
                    <input type="text" name="date" id="dashboard-date-picker" value="{{ $date }}"
                           placeholder="Elegir fecha..."
                           class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500 cursor-pointer">
                    @if($date && $date !== 'all')
                        <button type="button" onclick="clearDashboardDateFilter()" title="Quitar fecha"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-600 text-xs">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    @endif
                </div>

                <!-- Botones rápidos de jornada (3 columnas iguales en móvil, compactos en desktop) -->
                <div class="grid grid-cols-3 sm:flex rounded-xl bg-slate-100 p-1 gap-1 text-center flex-shrink-0">
                    <a href="{{ route('admin.dashboard', array_filter(['date' => $currentBusinessDate, 'shift' => $shift, 'status' => $status])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $date === $currentBusinessDate ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Hoy
                    </a>

                    @php
                        $yesterdayBusinessDate = \Carbon\Carbon::parse($currentBusinessDate)->subDay()->format('Y-m-d');
                    @endphp
                    <a href="{{ route('admin.dashboard', array_filter(['date' => $yesterdayBusinessDate, 'shift' => $shift, 'status' => $status])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $date === $yesterdayBusinessDate ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Ayer
                    </a>

                    <a href="{{ route('admin.dashboard', array_filter(['date' => 'all', 'status' => $status])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $date === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Todos
                    </a>
                </div>
            </form>
        </div>

        <!-- Fila 2: Selector de Turno (3 columnas en móvil para encajar en 1 sola línea) -->
        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 flex items-center space-x-1 flex-shrink-0">
                <i class="fas fa-business-time text-purple-600"></i>
                <span>Turno:</span>
            </span>

            <div class="grid grid-cols-3 sm:flex gap-1.5 w-full sm:w-auto">
                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => 'completo', 'status' => $status])) }}"
                   class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition text-center flex items-center justify-center space-x-1 {{ $shift === 'completo' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-clock text-xs"></i>
                    <span>Todo el día</span>
                    <span class="opacity-80 font-normal hidden lg:inline ml-1">(08 a 03 hs)</span>
                </a>

                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => 'manana', 'status' => $status])) }}"
                   class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition text-center flex items-center justify-center space-x-1 {{ $shift === 'manana' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-sun text-amber-300 text-xs"></i>
                    <span>Mañana</span>
                    <span class="opacity-80 font-normal hidden lg:inline ml-1">(08 a 16 hs)</span>
                </a>

                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => 'tarde', 'status' => $status])) }}"
                   class="px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition text-center flex items-center justify-center space-x-1 {{ ($shift === 'tarde' || $shift === 'noche') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-moon text-indigo-300 text-xs"></i>
                    <span>Tarde</span>
                    <span class="opacity-80 font-normal hidden lg:inline ml-1">(16:01 a 03 hs)</span>
                </a>
            </div>
        </div>

        <!-- Fila 3: Selector de Estados con scroll táctil suave sin barra fea -->
        <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1 flex items-center space-x-1 flex-shrink-0">
                <i class="fas fa-filter text-slate-400"></i>
                <span>Estado:</span>
            </span>

            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 min-w-0 flex-grow touch-pan-x -mx-1 px-1">
                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => $shift])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex-shrink-0 {{ empty($status) ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Todos los estados
                </a>
                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'enviado_whatsapp'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1 whitespace-nowrap flex-shrink-0 {{ $status === 'enviado_whatsapp' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fab fa-whatsapp text-emerald-400 text-xs"></i>
                    <span>WhatsApp</span>
                </a>
                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'en_preparacion'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1 whitespace-nowrap flex-shrink-0 {{ $status === 'en_preparacion' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-kitchen-set text-amber-300 text-xs"></i>
                    <span>En Prep.</span>
                </a>
                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'entregado'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1 whitespace-nowrap flex-shrink-0 {{ $status === 'entregado' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <i class="fas fa-check-double text-blue-300 text-xs"></i>
                    <span>Entregados</span>
                </a>
                <a href="{{ route('admin.dashboard', array_filter(['date' => $date, 'shift' => $shift, 'status' => 'cancelado'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1 whitespace-nowrap flex-shrink-0 {{ $status === 'cancelado' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-rose-700 hover:bg-rose-50' }}">
                    <i class="fas fa-times-circle text-rose-400 text-xs"></i>
                    <span>Cancelados</span>
                </a>
            </div>
        </div>

        <!-- Fila 4: Banner Informativo -->
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

    <!-- Tarjetas de Métricas Principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Ventas del Turno (Al hacer hover se oculta el icono de $ para dar 100% de espacio a cifras de 7+ dígitos) -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center gap-4 group transition-all duration-300 hover:shadow-md hover:border-emerald-200 cursor-pointer relative overflow-hidden"
             onclick="this.classList.toggle('is-revealed')"
             title="{{ $shiftTotalSales > 0 ? 'Pasa el cursor para ver el total' : 'Ventas del turno: $0' }}">
            
            @if($shiftTotalSales > 0)
                <!-- Icono de $ (se oculta en hover para dar todo el ancho a cifras grandes) -->
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold flex-shrink-0 group-hover:hidden group-[.is-revealed]:hidden transition-all">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            @else
                <!-- Sin ventas: icono fijo y visible -->
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            @endif

            <div class="min-w-0 flex-grow w-full">
                <div class="flex items-center justify-between gap-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 whitespace-nowrap">Ventas Turno</span>
                    @if($shiftTotalSales > 0)
                        <span class="text-[10px] font-bold text-slate-400 group-hover:text-emerald-600 group-[.is-revealed]:text-emerald-600 transition-colors flex items-center space-x-1 flex-shrink-0">
                            <i class="fas fa-eye-slash group-hover:hidden group-[.is-revealed]:hidden text-slate-300"></i>
                            <i class="fas fa-eye hidden group-hover:inline group-[.is-revealed]:inline text-emerald-500"></i>
                            <span class="hidden group-hover:inline group-[.is-revealed]:inline font-semibold">Total</span>
                        </span>
                    @endif
                </div>

                <div class="py-0.5">
                    @if($shiftTotalSales > 0)
                        <!-- Sin hover: Preview truncado con puntos ($378....) junto al icono -->
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight group-hover:hidden group-[.is-revealed]:hidden transition-all">
                            ${{ rtrim(substr(number_format($shiftTotalSales, 0, ',', '.'), 0, 4), '.') }}....
                        </h3>
                        <!-- Con hover: El icono de $ se elimina y este total ocupa todo el ancho libre (soporta 7+ cifras ej: $1.250.000) -->
                        <h3 class="hidden group-hover:block group-[.is-revealed]:block text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 tracking-tight whitespace-nowrap transition-all">
                            ${{ number_format($shiftTotalSales, 0, ',', '.') }}
                        </h3>
                    @else
                        <!-- Sin ventas en el turno: solo $0 (nunca $$0) -->
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">
                            $0
                        </h3>
                    @endif
                </div>

                <span class="text-xs font-semibold text-emerald-600">{{ $shiftDeliveredCount }} entregados</span>
            </div>
        </div>

        <!-- Pedidos en la Jornada / Turno -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4 transition-all duration-300 hover:shadow-md hover:border-purple-200">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div class="min-w-0 flex-grow">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block truncate">Pedidos Turno</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight my-0.5">{{ $shiftOrdersCount }}</h3>
                <span class="text-xs font-semibold {{ $shiftCancelledCount > 0 ? 'text-rose-500' : 'text-slate-400' }}">
                    {{ $shiftPendingCount }} activos • {{ $shiftCancelledCount }} cancelados
                </span>
            </div>
        </div>

        <!-- Platos en la Carta -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4 transition-all duration-300 hover:shadow-md hover:border-red-200">
            <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="fas fa-utensils"></i>
            </div>
            <div class="min-w-0 flex-grow">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block truncate">Total Platos</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight my-0.5">{{ $productsCount }}</h3>
                <span class="text-xs font-semibold text-emerald-600">{{ $availableProductsCount }} disponibles</span>
            </div>
        </div>

        <!-- Categorías / Secciones -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4 transition-all duration-300 hover:shadow-md hover:border-amber-200">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="min-w-0 flex-grow">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block truncate">Categorías</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight my-0.5">{{ $categoriesCount }}</h3>
                <span class="text-xs font-semibold text-slate-500">Secciones activas</span>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="bg-gradient-to-r from-red-600 via-rose-600 to-purple-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
            <h2 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight">¿Deseas agregar nuevos platos o modificar precios?</h2>
            <p class="text-red-100 text-xs sm:text-sm max-w-xl">
                Desde aquí puedes gestionar la carta completa, editar precios al instante o configurar los banners promocionales.
            </p>
        </div>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('admin.products.create') }}"
               class="px-5 py-3 rounded-2xl bg-white text-red-600 font-bold text-sm shadow-lg hover:bg-red-50 hover:scale-105 transition-all flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Nuevo Plato</span>
            </a>
            <a href="{{ route('admin.categories.create') }}"
               class="px-5 py-3 rounded-2xl bg-black/20 hover:bg-black/30 text-white font-bold text-sm backdrop-blur-md transition-all flex items-center space-x-2 border border-white/20">
                <i class="fas fa-folder-plus"></i>
                <span>Nueva Sección</span>
            </a>
        </div>
    </div>

    <!-- Pedidos del Turno (Desktop: Tabla / Mobile: Tarjetas) -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-800">
                    {{ $date && $date !== 'all' ? 'Pedidos del Turno' : 'Últimos Pedidos Armados' }}
                </h3>
                <p class="text-xs text-slate-500">
                    {{ $shiftInfo ? $shiftInfo['label'] : 'Historial de clientes que armaron su pedido' }}
                </p>
            </div>
            <a href="{{ route('admin.orders.index', array_filter(['date' => $date !== 'all' ? $date : null, 'shift' => $shift, 'status' => $status])) }}"
               class="text-xs font-bold text-red-600 hover:text-red-700 flex items-center space-x-1">
                <span>Ver todos</span>
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        </div>

        <!-- Vista Desktop: Tabla -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-3.5">ID</th>
                        <th class="py-3 px-3.5">Cliente</th>
                        <th class="py-3 px-3.5">Entrega</th>
                        <th class="py-3 px-3.5">Total</th>
                        <th class="py-3 px-3.5">Estado</th>
                        <th class="py-3 px-3.5">Fecha</th>
                        <th class="py-3 px-4 text-right sticky-action-col bg-slate-50">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="py-3.5 px-3.5 font-bold text-slate-800">#{{ $order->id }}</td>
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                <div class="text-xs text-slate-400">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="py-3.5 px-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $order->delivery_type === 'delivery' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Retiro en Local' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 font-black text-slate-900">
                                ${{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                @if($order->status === 'enviado_whatsapp')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                        <i class="fab fa-whatsapp text-emerald-600 text-xs"></i>
                                        <span>WhatsApp</span>
                                    </span>
                                @elseif($order->status === 'en_preparacion')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">
                                        <i class="fas fa-kitchen-set text-amber-600 text-xs"></i>
                                        <span>En Prep.</span>
                                    </span>
                                @elseif($order->status === 'entregado')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                        <i class="fas fa-check-double text-blue-600 text-xs"></i>
                                        <span>Entregado</span>
                                    </span>
                                @elseif($order->status === 'cancelado')
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
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
                            <td class="py-3.5 px-4 text-right sticky-action-col bg-white group-hover:bg-slate-50 transition-colors whitespace-nowrap space-x-1.5">
                                <a href="{{ route('admin.orders.show', ['order' => $order, 'return_url' => request()->fullUrl()]) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition inline-block whitespace-nowrap">
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
                            <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                Aún no se han registrado pedidos en este turno.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Mobile: Tarjetas Adaptativas (Sin scroll horizontal) -->
        <div class="block lg:hidden p-3 space-y-3 bg-slate-100/60">
            @forelse($recentOrders as $order)
                <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs space-y-2.5">
                    <!-- Cabecera Tarjeta: ID + Entrega + Estado -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-black text-slate-900 text-sm bg-slate-100 px-2 py-0.5 rounded-lg">
                                #{{ $order->id }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $order->delivery_type === 'delivery' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Retiro' }}
                            </span>
                        </div>

                        <div>
                            @if($order->status === 'enviado_whatsapp')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                    <i class="fab fa-whatsapp text-emerald-600 text-[10px]"></i>
                                    <span>WhatsApp</span>
                                </span>
                            @elseif($order->status === 'en_preparacion')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700">
                                    <i class="fas fa-kitchen-set text-amber-600 text-[10px]"></i>
                                    <span>En Prep.</span>
                                </span>
                            @elseif($order->status === 'entregado')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700">
                                    <i class="fas fa-check-double text-blue-600 text-[10px]"></i>
                                    <span>Entregado</span>
                                </span>
                            @elseif($order->status === 'cancelado')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700">
                                    <i class="fas fa-times-circle text-rose-500 text-[10px]"></i>
                                    <span>Cancelado</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Datos Cliente -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm leading-snug">{{ $order->customer_name }}</h4>
                            <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/\D/', '', $order->customer_phone) }}" target="_blank"
                               class="text-xs text-emerald-600 font-semibold hover:underline flex items-center space-x-1 mt-0.5">
                                <i class="fab fa-whatsapp text-[11px]"></i>
                                <span>{{ $order->customer_phone }}</span>
                            </a>
                        </div>
                        <span class="text-xs text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <!-- Pie Tarjeta: Total y Botón de Acción -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total</span>
                            <span class="text-base font-black text-slate-900">
                                ${{ number_format($order->total_amount, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('¿Eliminar pedido #{{ $order->id }}? Se descontará de las ventas y métricas.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Eliminar pedido">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.orders.show', ['order' => $order, 'return_url' => request()->fullUrl()]) }}"
                               class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center space-x-1">
                                <span>Ver Detalle</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs bg-white rounded-2xl p-6">
                    Aún no se han registrado pedidos en este turno.
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr('#dashboard-date-picker', {
            locale: 'es',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: false,
            defaultDate: "{{ ($date && $date !== 'all') ? $date : '' }}",
            onChange: function(selectedDates, dateStr) {
                if (dateStr) {
                    const form = document.getElementById('dashboard-date-filter-form');
                    form.submit();
                }
            }
        });
    });

    function clearDashboardDateFilter() {
        const input = document.getElementById('dashboard-date-picker');
        input.value = '';
        const form = document.getElementById('dashboard-date-filter-form');
        const hiddenDate = form.querySelector('input[name="date"]');
        if (hiddenDate) hiddenDate.value = 'all';
        form.submit();
    }
</script>
@endpush
@endsection
