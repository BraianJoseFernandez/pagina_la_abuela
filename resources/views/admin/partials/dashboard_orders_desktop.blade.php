@forelse($recentOrders as $order)
    <tr class="hover:bg-slate-50/80 transition group order-row-item" data-order-id="{{ $order->id }}">
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
            <a href="{{ route('admin.orders.show', ['order' => $order, 'return_url' => route('admin.dashboard', array_filter(['date' => request('date'), 'shift' => request('shift'), 'status' => request('status')]))]) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition inline-block whitespace-nowrap">
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
