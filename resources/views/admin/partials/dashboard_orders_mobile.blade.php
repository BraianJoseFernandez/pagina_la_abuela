@forelse($recentOrders as $order)
    <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs space-y-2.5 order-mobile-item" data-order-id="{{ $order->id }}">
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
                <a href="{{ route('admin.orders.show', ['order' => $order, 'return_url' => route('admin.dashboard', array_filter(['date' => request('date'), 'shift' => request('shift'), 'status' => request('status')]))]) }}"
                   class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition flex items-center space-x-1">
                    <span>Ver Detalle</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
@empty
    <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-sm">
        Aún no se han registrado pedidos en este turno.
    </div>
@endforelse
