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

                @if($order->delivery_address)
                    <div>
                        <span class="text-xs text-slate-400 block">Dirección de Entrega</span>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $order->delivery_address }}</p>
                    </div>
                @endif

                <div>
                    <span class="text-xs text-slate-400 block">Forma de Pago</span>
                    <span class="text-xs font-bold text-slate-700">{{ $order->payment_method ?? 'Efectivo' }}</span>
                </div>
            </div>

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
                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este pedido (#{{ $order->id }})? Esta acción lo quitará de las ventas y del historial.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition flex items-center justify-center space-x-2">
                        <i class="fas fa-trash-alt text-xs"></i>
                        <span>Eliminar Pedido #{{ $order->id }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
