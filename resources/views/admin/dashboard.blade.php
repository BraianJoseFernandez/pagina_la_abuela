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
                        <h3 id="metric-shift-total-sales-preview" class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight group-hover:hidden group-[.is-revealed]:hidden transition-all">
                            ${{ rtrim(substr(number_format($shiftTotalSales, 0, ',', '.'), 0, 4), '.') }}....
                        </h3>
                        <!-- Con hover: El icono de $ se elimina y este total ocupa todo el ancho libre (soporta 7+ cifras ej: $1.250.000) -->
                        <h3 id="metric-shift-total-sales-full" class="hidden group-hover:block group-[.is-revealed]:block text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 tracking-tight whitespace-nowrap transition-all">
                            ${{ number_format($shiftTotalSales, 0, ',', '.') }}
                        </h3>
                    @else
                        <!-- Sin ventas en el turno: solo $0 (nunca $$0) -->
                        <h3 id="metric-shift-total-sales-preview" class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">
                            $0
                        </h3>
                        <h3 id="metric-shift-total-sales-full" class="hidden text-xl sm:text-2xl lg:text-3xl font-black text-emerald-600 tracking-tight whitespace-nowrap">
                            $0
                        </h3>
                    @endif
                </div>

                <span id="metric-shift-delivered-count" class="text-xs font-semibold text-emerald-600">{{ $shiftDeliveredCount }} entregados</span>
            </div>
        </div>

        <!-- Pedidos en la Jornada / Turno -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4 transition-all duration-300 hover:shadow-md hover:border-purple-200">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div class="min-w-0 flex-grow">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block truncate">Pedidos Turno</span>
                <h3 id="metric-shift-orders-count" class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight my-0.5">{{ $shiftOrdersCount }}</h3>
                <span id="metric-shift-pending-count" class="text-xs font-semibold {{ $shiftCancelledCount > 0 ? 'text-rose-500' : 'text-slate-400' }}">
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
                <div class="flex items-center space-x-2.5">
                    <h3 class="text-lg font-black text-slate-800">
                        {{ $date && $date !== 'all' ? 'Pedidos del Turno' : 'Últimos Pedidos Armados' }}
                    </h3>
                    <span id="dashboard-live-indicator" class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>En vivo</span>
                    </span>
                    <button type="button" id="btn-toggle-sound" onclick="toggleOrderSound()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer" title="Activar/Silenciar sonido de nuevo pedido">
                        <i id="sound-icon" class="fas fa-volume-up text-xs text-emerald-600"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
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
                <tbody id="dashboard-orders-table-body" class="divide-y divide-slate-100 font-medium">
                    @include('admin.partials.dashboard_orders_desktop')
                </tbody>
            </table>
        </div>

        <!-- Vista Mobile: Tarjetas Adaptativas (Sin scroll horizontal) -->
        <div id="dashboard-orders-mobile-container" class="block lg:hidden p-3 space-y-3 bg-slate-100/60">
            @include('admin.partials.dashboard_orders_mobile')
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

        // Inicializar estado del botón de sonido
        updateSoundButtonUI();
    });

    function clearDashboardDateFilter() {
        const input = document.getElementById('dashboard-date-picker');
        input.value = '';
        const form = document.getElementById('dashboard-date-filter-form');
        const hiddenDate = form.querySelector('input[name="date"]');
        if (hiddenDate) hiddenDate.value = 'all';
        form.submit();
    }

    // --- GESTIÓN DE SONIDO PARA NUEVOS PEDIDOS (Web Audio API) ---
    let audioCtx = null;
    let soundEnabled = localStorage.getItem('admin_order_sound') !== 'false';

    function initAudio() {
        try {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) {
                    audioCtx = new AudioContext();
                }
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        } catch (e) {
            console.warn('AudioContext init error:', e);
        }
    }

    // Desbloquear audio con el primer clic del usuario en la pantalla
    document.addEventListener('click', function unlockAudioOnce() {
        initAudio();
        document.removeEventListener('click', unlockAudioOnce);
    }, { once: true });

    function updateSoundButtonUI() {
        const btn = document.getElementById('btn-toggle-sound');
        const icon = document.getElementById('sound-icon');
        if (!btn || !icon) return;

        if (soundEnabled) {
            icon.className = 'fas fa-volume-up text-xs text-emerald-600';
            btn.title = 'Sonido de nuevo pedido activado (clic para silenciar)';
        } else {
            icon.className = 'fas fa-volume-mute text-xs text-slate-400';
            btn.title = 'Sonido de nuevo pedido silenciado (clic para activar)';
        }
    }

    function toggleOrderSound() {
        initAudio();
        soundEnabled = !soundEnabled;
        localStorage.setItem('admin_order_sound', soundEnabled ? 'true' : 'false');
        updateSoundButtonUI();

        if (soundEnabled) {
            playOrderNotificationChime();
        }
    }

    function playOrderNotificationChime() {
        if (!soundEnabled) return;
        try {
            initAudio();
            if (!audioCtx) return;

            const now = audioCtx.currentTime;

            // Primer tono (campana suave y agradable: Re5 -> La5)
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now); // D5
            osc1.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5

            gain1.gain.setValueAtTime(0.35, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.55);

            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);

            osc1.start(now);
            osc1.stop(now + 0.55);

            // Segundo tono armónico brillante (Re6)
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(880, now + 0.14);
            osc2.frequency.exponentialRampToValueAtTime(1174.66, now + 0.32); // D6

            gain2.gain.setValueAtTime(0.28, now + 0.14);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.75);

            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);

            osc2.start(now + 0.14);
            osc2.stop(now + 0.75);
        } catch (err) {
            console.warn('Audio notification failed:', err);
        }
    }

    // --- ACTUALIZACIÓN EN VIVO (POLLING LIGERO) ---
    let lastOrderId = {{ $recentOrders->first()?->id ?? 0 }};
    let isPolling = false;

    async function checkLiveOrders() {
        if (isPolling) return;
        isPolling = true;

        try {
            const params = new URLSearchParams(window.location.search);
            params.set('last_order_id', lastOrderId);

            const url = `{{ route('admin.dashboard.live-orders') }}?${params.toString()}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            if (!data || !data.success) return;

            // Actualizar vista desktop
            if (data.html_desktop) {
                const desktopTbody = document.getElementById('dashboard-orders-table-body');
                if (desktopTbody) {
                    desktopTbody.innerHTML = data.html_desktop;
                }
            }

            // Actualizar vista móvil
            if (data.html_mobile) {
                const mobileContainer = document.getElementById('dashboard-orders-mobile-container');
                if (mobileContainer) {
                    mobileContainer.innerHTML = data.html_mobile;
                }
            }

            // Actualizar métricas del turno
            if (data.metrics) {
                const m = data.metrics;
                const previewSales = document.getElementById('metric-shift-total-sales-preview');
                const fullSales = document.getElementById('metric-shift-total-sales-full');
                const deliveredCount = document.getElementById('metric-shift-delivered-count');
                const ordersCount = document.getElementById('metric-shift-orders-count');
                const pendingCount = document.getElementById('metric-shift-pending-count');

                if (previewSales) {
                    previewSales.textContent = m.shiftTotalSales > 0 ? m.shiftTotalSalesPreview : '$0';
                }
                if (fullSales) {
                    fullSales.textContent = m.shiftTotalSalesFormatted;
                }
                if (deliveredCount) {
                    deliveredCount.textContent = `${m.shiftDeliveredCount} entregados`;
                }
                if (ordersCount) {
                    ordersCount.textContent = m.shiftOrdersCount;
                }
                if (pendingCount) {
                    pendingCount.textContent = `${m.shiftPendingCount} activos • ${m.shiftCancelledCount} cancelados`;
                    pendingCount.className = m.shiftCancelledCount > 0 ? 'text-xs font-semibold text-rose-500' : 'text-xs font-semibold text-slate-400';
                }
            }

            // Si hay pedidos nuevos
            if (data.has_new && data.new_orders && data.new_orders.length > 0) {
                playOrderNotificationChime();

                data.new_orders.forEach(order => {
                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 7000,
                            timerProgressBar: true,
                        });
                        Toast.fire({
                            icon: 'success',
                            title: `🔔 ¡Nuevo Pedido #${order.id}!`,
                            html: `<div class="text-xs text-slate-700 font-bold mt-1">${order.customer_name} &bull; <span class="font-extrabold text-emerald-600">${order.total_amount}</span></div><div class="text-[11px] text-slate-500">${order.delivery_type}</div>`
                        });
                    }
                });

                // Efecto visual en indicador "En vivo"
                const indicator = document.getElementById('dashboard-live-indicator');
                if (indicator) {
                    indicator.classList.add('ring-4', 'ring-emerald-400', 'bg-emerald-200');
                    setTimeout(() => {
                        indicator.classList.remove('ring-4', 'ring-emerald-400', 'bg-emerald-200');
                    }, 2500);
                }
            }

            if (data.latest_order_id) {
                lastOrderId = Math.max(lastOrderId, data.latest_order_id);
            }
        } catch (err) {
            console.warn('Live polling error:', err);
        } finally {
            isPolling = false;
        }
    }

    // Iniciar sondeo en segundo plano cada 7 segundos
    setInterval(checkLiveOrders, 7000);
</script>
@endpush
@endsection
