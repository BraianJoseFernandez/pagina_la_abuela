@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Resumen General')

@section('content')
<div class="space-y-8">
    <!-- Tarjetas de Métricas Principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Platos en la Carta -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center text-2xl font-bold">
                <i class="fas fa-utensils"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Platos</span>
                <h3 class="text-3xl font-black text-slate-800">{{ $productsCount }}</h3>
                <span class="text-xs font-semibold text-emerald-600">{{ $availableProductsCount }} disponibles</span>
            </div>
        </div>

        <!-- Categorías -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl font-bold">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Categorías</span>
                <h3 class="text-3xl font-black text-slate-800">{{ $categoriesCount }}</h3>
                <span class="text-xs font-semibold text-slate-500">Secciones activas</span>
            </div>
        </div>

        <!-- Pedidos WhatsApp -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pedidos Armados</span>
                <h3 class="text-3xl font-black text-slate-800">{{ $ordersCount }}</h3>
                <span class="text-xs font-semibold text-emerald-600">Registrados</span>
            </div>
        </div>

        <!-- Estado Sección Eventos -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-2xl font-bold">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Promo Activa</span>
                <h3 class="text-lg font-black text-slate-800 truncate max-w-[130px]">
                    {{ $activeEvent ? $activeEvent->title : 'Inactiva' }}
                </h3>
                <span class="text-xs font-bold {{ $activeEvent ? 'text-emerald-600' : 'text-slate-400' }}">
                    {{ $activeEvent ? 'En portada' : 'Desactivada' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="bg-gradient-to-r from-red-600 via-rose-600 to-purple-700 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">¿Deseas agregar nuevos platos o modificar precios?</h2>
            <p class="text-red-100 text-sm max-w-xl">
                Desde aquí puedes gestionar la carta completa, editar precios al instante o configurar los banners promocionales.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
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

    <!-- Pedidos Recientes -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-800">Últimos Pedidos Armados</h3>
                <p class="text-xs text-slate-500">Historial de clientes que armaron su pedido para WhatsApp</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-red-600 hover:text-red-700">Ver todos</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-6">ID</th>
                        <th class="py-3.5 px-6">Cliente</th>
                        <th class="py-3.5 px-6">Entrega</th>
                        <th class="py-3.5 px-6">Total</th>
                        <th class="py-3.5 px-6">Fecha</th>
                        <th class="py-3.5 px-6 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-bold text-slate-800">#{{ $order->id }}</td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $order->customer_name }}</div>
                                <div class="text-xs text-slate-400">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $order->delivery_type === 'delivery' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $order->delivery_type === 'delivery' ? 'Delivery' : 'Retiro en Local' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-black text-slate-900">
                                ${{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-400">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                Aún no se han registrado pedidos en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
