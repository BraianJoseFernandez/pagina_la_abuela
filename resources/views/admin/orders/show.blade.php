@extends('layouts.admin')

@section('title', 'Detalle de Pedido #' . $order->id)
@section('page-title', 'Pedido #' . $order->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center space-x-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Volver al Listado</span>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Detalle de Productos -->
        <div class="md:col-span-2 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
            <div class="border-b border-slate-100 pb-4 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Platos del Pedido</h3>
                    <span class="text-xs text-slate-400">Registrado el {{ $order->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <span class="text-2xl font-black text-purple-700">${{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <div class="py-3.5 flex justify-between items-start">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                <span class="font-black text-sm bg-purple-100 text-purple-800 w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0">{{ $item->quantity }}x</span>
                                <h4 class="font-bold text-slate-800 text-base">{{ $item->product_name }}</h4>
                                @if($item->category_name)
                                    <span class="inline-flex items-center space-x-1 bg-red-50 text-red-700 border border-red-200/80 text-[11px] font-bold px-2 py-0.5 rounded-lg shadow-2xs">
                                        <i class="fas fa-tag text-[9px] text-red-500"></i>
                                        <span>{{ $item->category_name }}</span>
                                    </span>
                                @endif
                            </div>
                            @if($item->variant_name)
                                <span class="inline-block bg-slate-100 text-slate-600 text-xs font-bold px-2.5 py-0.5 rounded-md ml-8">
                                    {{ $item->variant_name }}
                                </span>
                            @endif
                            @if($item->cooking_method)
                                <span class="inline-flex items-center space-x-1 bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-0.5 rounded-md {{ $item->variant_name ? 'ml-2' : 'ml-8' }} border border-amber-200">
                                    <i class="fas fa-fire-burner text-[10px]"></i>
                                    <span>{{ in_array($item->cooking_method, ['Horno', 'Al Horno', '🔥 Horno']) ? '🔥 Horno' : ($item->cooking_method === 'Frita' ? '🍳 Frita' : $item->cooking_method) }}</span>
                                </span>
                            @endif
                            @if($item->garnish_name)
                                <span class="inline-flex items-center space-x-1 bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-md {{ ($item->variant_name || $item->cooking_method) ? 'ml-2' : 'ml-8' }} border border-emerald-200">
                                    <i class="fas fa-bowl-food text-[10px] text-emerald-600"></i>
                                    <span>Guarnición: {{ $item->garnish_name }}{{ $item->garnish_price > 0 ? ' (+$' . number_format($item->garnish_price, 0, ',', '.') . ')' : '' }}</span>
                                </span>
                            @endif
                            @if($item->notes)
                                <p class="text-xs text-slate-500 italic ml-8"><i class="fas fa-pencil-alt text-[10px] mr-1 text-slate-400"></i>{{ $item->notes }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400 font-medium">${{ number_format($item->unit_price, 0, ',', '.') }} c/u</div>
                            <div class="text-sm font-black text-slate-800">${{ number_format($item->subtotal, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($order->notes)
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs space-y-1">
                    <span class="font-bold uppercase tracking-wider block text-[11px] text-amber-800">Aclaraciones generales del cliente:</span>
                    <p class="leading-relaxed">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Información del Cliente & Actualización de Estado -->
        <div class="space-y-6">
            <!-- Datos del Cliente -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Datos del Cliente</h4>

                <div>
                    <span class="text-xs text-slate-400 block">Nombre</span>
                    <h4 class="text-base font-bold text-slate-800">{{ $order->customer_name }}</h4>
                </div>

                <div>
                    <span class="text-xs text-slate-400 block">Teléfono</span>
                    <a href="https://api.whatsapp.com/send?phone={{ preg_replace('/\D/', '', $order->customer_phone) }}" target="_blank"
                       class="text-sm font-bold text-emerald-600 hover:text-emerald-700 flex items-center space-x-1.5 mt-0.5">
                        <i class="fab fa-whatsapp"></i>
                        <span>{{ $order->customer_phone }}</span>
                    </a>
                </div>

                @if($order->customer_email)
                    <div>
                        <span class="text-xs text-slate-400 block">Correo Electrónico</span>
                        <a href="mailto:{{ $order->customer_email }}" class="text-xs font-bold text-red-600 hover:text-red-700 flex items-center space-x-1.5 mt-0.5">
                            <i class="fas fa-envelope text-slate-400"></i>
                            <span>{{ $order->customer_email }}</span>
                        </a>
                    </div>
                @endif

                <div>
                    <span class="text-xs text-slate-400 block">Tipo de Entrega</span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold inline-block mt-1 {{ $order->delivery_type === 'delivery' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $order->delivery_type === 'delivery' ? 'Envío a Domicilio' : 'Retiro en Local' }}
                    </span>
                </div>

                @if($order->delivery_type === 'delivery')
                    <div>
                        <span class="text-xs text-slate-400 block">Dirección de Entrega</span>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">
                            {{ $order->delivery_address ?: 'No se especificó texto (ver mapa)' }}
                        </p>
                    </div>

                    @if($order->delivery_map_url)
                        <div class="p-3 rounded-2xl bg-blue-50/90 border border-blue-200/80 space-y-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <span class="text-xs font-bold text-blue-900">Ubicación con Pin de Maps</span>
                            </div>
                            @if($order->delivery_latitude && $order->delivery_longitude)
                                <div class="text-[11px] font-mono text-blue-700">
                                    Coordenadas: {{ number_format($order->delivery_latitude, 5) }}, {{ number_format($order->delivery_longitude, 5) }}
                                </div>
                            @endif
                            <a href="{{ $order->delivery_map_url }}" target="_blank"
                               class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition">
                                <i class="fas fa-external-link-alt text-[10px]"></i>
                                <span>Abrir en Google Maps</span>
                            </a>
                        </div>
                    @endif
                @endif

                <div>
                    <span class="text-xs text-slate-400 block">Forma de Pago</span>
                    <span class="text-xs font-bold text-slate-700">{{ $order->payment_method ?? 'Efectivo' }}</span>
                </div>
            </div>

            <!-- Despachar a Motomandado (Exclusivo para Envío a Domicilio) -->
            @if($order->delivery_type === 'delivery')
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-1.5">
                                <i class="fas fa-motorcycle text-red-600"></i>
                                <span>Despachar a Motomandado</span>
                            </h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Elige a qué cadete activo enviar la comanda</p>
                        </div>
                        @if(count($cadetes) > 0)
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full">
                                {{ count($cadetes) }} {{ count($cadetes) === 1 ? 'activo' : 'activos' }}
                            </span>
                        @endif
                    </div>

                    @if(count($cadetes) > 0)
                        <div class="space-y-3">
                            @foreach($cadetes as $cad)
                                @php
                                    $cadName = !empty(trim($cad['name'] ?? '')) ? $cad['name'] : 'Cadete ' . ($loop->iteration);
                                    $cadPhone = preg_replace('/\D/', '', $cad['phone']);
                                    $cadColor = !empty($cad['color']) ? $cad['color'] : '#059669';
                                @endphp
                                <div class="rounded-2xl p-3.5 text-white shadow-sm transition-all duration-200 hover:shadow-md border border-white/20"
                                     style="background-color: {{ $cadColor }};">
                                    <!-- Fila Superior: Datos del Cadete -->
                                    <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-white/20">
                                        <div class="flex items-center space-x-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-xl bg-white/25 flex items-center justify-center flex-shrink-0 text-white shadow-xs">
                                                <i class="fas fa-motorcycle text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="block font-black text-sm text-white truncate leading-tight">{{ $cadName }}</span>
                                                <span class="text-[11px] text-white/90 font-mono block mt-0.5">{{ $cad['phone'] }}</span>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center space-x-1 bg-white/20 px-2 py-0.5 rounded-md text-[10px] font-bold text-white uppercase tracking-wider flex-shrink-0 shadow-2xs">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                                            <span>Activo</span>
                                        </span>
                                    </div>

                                    <!-- Fila Inferior: Botón de Despacho de ancho completo -->
                                    <div class="pt-2.5">
                                        <button type="button"
                                                onclick="openDispatchCadeteModal('{{ addslashes($cadName) }}', '{{ $cadPhone }}', '{{ $cadColor }}')"
                                                class="w-full py-2.5 px-3 rounded-xl bg-black/25 hover:bg-black/35 active:bg-black/45 text-white font-black text-xs flex items-center justify-center space-x-2 transition shadow-xs cursor-pointer group">
                                            <i class="fab fa-whatsapp text-sm text-emerald-300"></i>
                                            <span>Despachar por WhatsApp</span>
                                            <i class="fas fa-arrow-right text-[10px] opacity-80 group-hover:translate-x-1 transition-transform"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-center space-y-2">
                            <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                <i class="fas fa-motorcycle text-lg"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-700">No hay motomandados activos.</p>
                            <p class="text-[11px] text-slate-500">Solo se puede despachar a los cadetes marcados como activos en el sistema.</p>
                            <a href="{{ route('admin.settings.index') }}"
                               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs">
                                <i class="fas fa-users-cog text-xs"></i>
                                <span>Gestionar Cadetes en Configuración</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Estado del Pedido -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Actualizar Estado</h4>

                <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="enviado_whatsapp" {{ $order->status === 'enviado_whatsapp' ? 'selected' : '' }}>Enviado por WhatsApp</option>
                        <option value="en_preparacion" {{ $order->status === 'en_preparacion' ? 'selected' : '' }}>En Preparación</option>
                        <option value="entregado" {{ $order->status === 'entregado' ? 'selected' : '' }}>Entregado / Completado</option>
                        <option value="cancelado" {{ $order->status === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                        Guardar Estado
                    </button>
                </form>
            </div>

            <!-- Zona de Peligro: Eliminar Pedido -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-rose-100 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-500 flex items-center space-x-1.5">
                    <i class="fas fa-trash-alt text-xs"></i>
                    <span>Eliminar Pedido</span>
                </h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Eliminar este pedido lo quitará de las ventas del turno, del historial y de todas las métricas del sistema.
                </p>
                <form id="delete-order-form" action="{{ route('admin.orders.destroy', $order) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteOrder()" class="w-full py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition flex items-center justify-center space-x-2">
                        <i class="fas fa-trash-alt text-xs"></i>
                        <span>Eliminar Pedido #{{ $order->id }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Despacho a Cadete con Comentario Opcional -->
<div id="dispatch-cadete-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all scale-95 opacity-0 duration-200" id="dispatch-modal-card">
        <!-- Header con color dinámico del cadete -->
        <div id="dispatch-modal-header" class="p-5 text-white flex items-center justify-between" style="background-color: #059669;">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-xs">
                    <i class="fas fa-motorcycle text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black tracking-wide leading-tight">Despachar Pedido #{{ $order->id }}</h3>
                    <p class="text-xs text-white/90 font-medium" id="dispatch-modal-cadete-sub">Cadete: ...</p>
                </div>
            </div>
            <button type="button" onclick="closeDispatchCadeteModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
            <!-- Resumen del Pedido para el Cadete -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80 space-y-2 text-xs">
                <div class="flex justify-between items-start">
                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Cliente:</span>
                    <span class="font-bold text-slate-800 text-right">{{ $order->customer_name }} ({{ $order->customer_phone }})</span>
                </div>
                <div class="flex justify-between items-start">
                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Dirección:</span>
                    <span class="font-bold text-slate-800 text-right max-w-[240px]">{{ $order->delivery_address ?: 'Ubicación señalada en Google Maps' }}</span>
                </div>
                @if($order->delivery_map_url)
                    <div class="flex justify-between items-center text-blue-700 font-bold pt-1 border-t border-slate-200">
                        <span><i class="fas fa-map-pin mr-1"></i> Pin de Maps:</span>
                        <span class="text-[11px] bg-blue-100/70 text-blue-800 px-2 py-0.5 rounded-md">Incluido en el mensaje</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                    <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Cobro al Entregar:</span>
                    @if($order->payment_method === 'Efectivo')
                        <span class="font-black text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-xl text-xs">
                            💵 Cobrar: ${{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    @else
                        <span class="font-black text-blue-700 bg-blue-100 px-2.5 py-1 rounded-xl text-xs">
                            💳 Pagado ({{ $order->payment_method }})
                        </span>
                    @endif
                </div>
            </div>

            <!-- Campo de Comentario de Caja para el Cadete (Opcional) -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Comentario o Instrucción para el Cadete</span>
                    <span class="text-[10px] text-slate-400 font-normal lowercase">(opcional)</span>
                </label>
                <textarea id="dispatch-cadete-comment" rows="2"
                          placeholder="Ej: Llevar cambio de $20.000, tocar timbre blanco, cliente espera en la puerta..."
                          class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white bg-slate-50 transition"></textarea>
                <span class="text-[10px] text-slate-400 mt-1 block">Este mensaje se agregará destacado al WhatsApp que recibirá el cadete.</span>
            </div>

            <!-- Botones de Acción -->
            <div class="pt-2 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeDispatchCadeteModal()"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="btn-confirm-send-dispatch"
                        onclick="sendDispatchToCadeteWhatsApp()"
                        class="px-5 py-2.5 rounded-xl text-white font-black text-xs shadow-lg transition flex items-center space-x-2 cursor-pointer transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="fab fa-whatsapp text-base"></i>
                    <span>Enviar a WhatsApp</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentCadete = {
        name: '',
        phone: '',
        color: '#059669'
    };

    const orderData = {
        id: {{ $order->id }},
        customerName: @js($order->customer_name),
        customerPhone: @js($order->customer_phone),
        deliveryAddress: @js($order->delivery_address ?? ''),
        deliveryMapUrl: @js($order->delivery_map_url ?? ''),
        paymentMethod: @js($order->payment_method ?? 'Efectivo'),
        totalAmountFormatted: @js('$' . number_format($order->total_amount, 0, ',', '.')),
        customerNotes: @js($order->notes ?? ''),
        items: [
            @foreach($order->items as $item)
                {
                    quantity: {{ $item->quantity }},
                    name: @js($item->product_name),
                    category: @js($item->category_name ?? ''),
                    variant: @js($item->variant_name ?? ''),
                    cooking: @js($item->cooking_method ?? ''),
                    garnish: @js($item->garnish_name ?? ''),
                    garnishPrice: @js($item->garnish_price > 0 ? ' (+$' . number_format($item->garnish_price, 0, ',', '.') . ')' : ''),
                    subtotal: @js('$' . number_format($item->subtotal, 0, ',', '.')),
                    notes: @js($item->notes ?? '')
                },
            @endforeach
        ]
    };

    function openDispatchCadeteModal(name, phone, color) {
        currentCadete = { name, phone, color: color || '#059669' };

        const modal = document.getElementById('dispatch-cadete-modal');
        const card = document.getElementById('dispatch-modal-card');
        const header = document.getElementById('dispatch-modal-header');
        const sub = document.getElementById('dispatch-modal-cadete-sub');
        const btnSend = document.getElementById('btn-confirm-send-dispatch');
        const commentInput = document.getElementById('dispatch-cadete-comment');

        if (sub) sub.innerText = `Cadete: ${name} (${phone})`;
        if (header) header.style.backgroundColor = currentCadete.color;
        if (btnSend) {
            btnSend.style.backgroundColor = currentCadete.color;
            btnSend.innerHTML = `<i class="fab fa-whatsapp text-base"></i><span>Enviar a WhatsApp de ${name}</span>`;
        }
        if (commentInput) commentInput.value = '';

        if (modal && card) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }
    }

    function closeDispatchCadeteModal() {
        const modal = document.getElementById('dispatch-cadete-modal');
        const card = document.getElementById('dispatch-modal-card');

        if (card) card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            if (modal) modal.classList.add('hidden');
        }, 150);
    }

    function sendDispatchToCadeteWhatsApp() {
        if (!currentCadete.phone) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Teléfono no configurado',
                    text: 'El motomandado no tiene un número de teléfono o WhatsApp válido registrado.',
                    confirmButtonColor: '#dc2626',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl font-[Poppins]'
                    }
                });
            } else {
                alert('El cadete no tiene un número de teléfono válido.');
            }
            return;
        }

        const commentInput = document.getElementById('dispatch-cadete-comment');
        const extraComment = commentInput ? commentInput.value.trim() : '';

        // Construcción del mensaje para el repartidor
        let msg = `🛵 *PEDIDO PARA ENTREGA #${orderData.id}*\n`;
        msg += `━━━━━━━━━━━━━━━━━━━━━\n`;
        msg += `👤 *Cliente:* ${orderData.customerName}\n`;
        msg += `📞 *Teléfono:* ${orderData.customerPhone}\n`;

        if (orderData.deliveryAddress) {
            msg += `📍 *Dirección:* ${orderData.deliveryAddress}\n`;
        }
        if (orderData.deliveryMapUrl) {
            msg += `🗺️ *Ubicación GPS (Google Maps):* ${orderData.deliveryMapUrl}\n`;
        }
        if (!orderData.deliveryAddress && orderData.deliveryMapUrl) {
            msg += `📍 *Dirección:* Ubicación exacta en el mapa (toca el enlace de Google Maps)\n`;
        }

        msg += `━━━━━━━━━━━━━━━━━━━━━\n`;
        msg += `📋 *DETALLE DE LA COMIDA:*\n`;

        orderData.items.forEach(it => {
            let details = [];
            if (it.variant) details.push(it.variant);
            if (it.cooking) details.push(it.cooking);
            const detText = details.length > 0 ? ` (${details.join(' - ')})` : '';
            msg += `• *${it.quantity}x* ${it.name}${detText}\n`;
            if (it.garnish) {
                msg += `   └ 🥗 _Guarnición: ${it.garnish}${it.garnishPrice || ''}_\n`;
            }
            if (it.notes) {
                msg += `   └ _Nota: ${it.notes}_\n`;
            }
        });

        msg += `━━━━━━━━━━━━━━━━━━━━━\n`;

        // Regla de Cobro clara
        if (orderData.paymentMethod.toLowerCase().includes('efectivo')) {
            msg += `💵 *COBRAR AL CLIENTE EN EFECTIVO:* *${orderData.totalAmountFormatted}*\n`;
        } else {
            msg += `💳 *PAGO:* *PAGADO (${orderData.paymentMethod}) - NO COBRAR AL CLIENTE*\n`;
        }

        if (orderData.customerNotes) {
            msg += `📝 *Nota del cliente:* ${orderData.customerNotes}\n`;
        }

        if (extraComment) {
            msg += `⚠️ *INSTRUCCIÓN DE CAJA:* ${extraComment}\n`;
        }

        msg += `━━━━━━━━━━━━━━━━━━━━━\n`;
        msg += `_¡Buen viaje y con cuidado!_ 🛵💨`;

        const cleanNumber = currentCadete.phone.replace(/\D/g, '');
        const encodedMsg = encodeURIComponent(msg);
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent || navigator.vendor || window.opera) ||
                         (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        const btnSend = document.getElementById('dispatch-modal-btn-send');
        const originalContent = btnSend ? btnSend.innerHTML : '';
        if (btnSend) {
            btnSend.disabled = true;
            btnSend.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i><span>Despachando automáticamente...</span>';
            btnSend.classList.add('opacity-75', 'pointer-events-none');
        }

        // Petición al backend para despacho 100% automático en segundo plano
        fetch('{{ route("admin.orders.dispatch-whatsapp", $order) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                cadete_name: currentCadete.name,
                cadete_phone: currentCadete.phone,
                comment: extraComment
            })
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));

            if (btnSend) {
                btnSend.disabled = false;
                btnSend.innerHTML = originalContent;
                btnSend.classList.remove('opacity-75', 'pointer-events-none');
            }

            if (res.ok && data.success) {
                closeDispatchCadeteModal();
                Swal.fire({
                    icon: 'success',
                    title: '¡Comanda Enviada!',
                    text: `El pedido #${orderData.id} fue enviado automáticamente y en segundo plano al WhatsApp de ${currentCadete.name}.`,
                    confirmButtonColor: '#059669',
                    customClass: { popup: 'rounded-3xl shadow-2xl font-[Poppins]' }
                });
            } else {
                // Si el servicio automático no está vinculado, ofrecer apertura manual
                const fallbackUrl = data.fallback_url || `https://web.whatsapp.com/send?phone=${cleanNumber}&text=${encodedMsg}`;
                Swal.fire({
                    icon: 'info',
                    title: 'Envío Automático no vinculado',
                    text: data.error || 'La sesión de WhatsApp automático no está iniciada en la configuración. Puedes escanear el QR en Configuración o enviar manualmente.',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Abrir WhatsApp Manual',
                    cancelButtonText: 'Cerrar',
                    reverseButtons: true,
                    customClass: { popup: 'rounded-3xl shadow-2xl font-[Poppins]' }
                }).then(result => {
                    if (result.isConfirmed) {
                        closeDispatchCadeteModal();
                        if (isMobile) {
                            window.location.href = `whatsapp://send?phone=${cleanNumber}&text=${encodedMsg}`;
                        } else {
                            window.open(fallbackUrl, '_blank');
                        }
                    }
                });
            }
        })
        .catch(err => {
            console.error(err);
            if (btnSend) {
                btnSend.disabled = false;
                btnSend.innerHTML = originalContent;
                btnSend.classList.remove('opacity-75', 'pointer-events-none');
            }

            // Fallback en caso de fallo de red
            closeDispatchCadeteModal();
            if (isMobile) {
                window.location.href = `whatsapp://send?phone=${cleanNumber}&text=${encodedMsg}`;
            } else {
                window.open(`https://web.whatsapp.com/send?phone=${cleanNumber}&text=${encodedMsg}`, '_blank');
            }
        });
    }

    function confirmDeleteOrder() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar Pedido #{{ $order->id }}?',
                text: 'Esta acción quitará el pedido de las ventas del turno, del historial y de todas las métricas del sistema. No se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar pedido',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl font-[Poppins]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-order-form').submit();
                }
            });
        } else {
            if (confirm('¿Estás seguro de que deseas eliminar este pedido (#{{ $order->id }})? Esta acción lo quitará de las ventas y del historial.')) {
                document.getElementById('delete-order-form').submit();
            }
        }
    }
</script>
@endpush
@endsection
