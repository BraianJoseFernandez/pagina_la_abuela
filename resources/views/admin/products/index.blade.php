@extends('layouts.admin')

@section('title', 'Platos y Carta')
@section('page-title', 'Gestión de Platos y Precios')

@section('content')
<div class="space-y-6">
    <!-- Barra Superior de Filtros y Creación -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200/80">
        <!-- Filtro por Categoría y Buscador -->
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative min-w-[200px]">
                <select name="category_id" onchange="this.form.submit()"
                        class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500 appearance-none">
                    <option value="">Todas las Categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>

            <div class="relative flex-grow sm:flex-grow-0 sm:min-w-[240px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar plato o ingrediente..."
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>

            @if($selectedCategoryId || $search)
                <a href="{{ route('admin.products.index') }}" class="px-3 py-2.5 rounded-2xl text-xs font-bold text-slate-500 hover:text-slate-700 bg-slate-100 transition">
                    Limpiar Filtros
                </a>
            @endif
        </form>

        <!-- Botón Crear Nuevo Plato -->
        <a href="{{ route('admin.products.create') }}"
           class="px-5 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold text-sm shadow-md shadow-red-500/20 flex items-center justify-center space-x-2 transition flex-shrink-0">
            <i class="fas fa-plus"></i>
            <span>Nuevo Plato / Ítem</span>
        </a>
    </div>

    <!-- Tabla de Platos con Drag & Drop -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span class="flex items-center space-x-1.5 font-medium">
                <i class="fas fa-arrows-up-down-left-right text-purple-600"></i>
                <span>Arrastra y suelta las filas para cambiar el orden de aparición de los platos.</span>
            </span>
            <span class="font-bold text-slate-700">{{ $products->total() }} platos registrados</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-4 px-4 w-12 text-center"><i class="fas fa-grip-vertical text-slate-400"></i></th>
                        <th class="py-4 px-4 w-16 text-center">N°</th>
                        <th class="py-4 px-6">Plato / Ítem</th>
                        <th class="py-4 px-6">Categoría</th>
                        <th class="py-4 px-6">Precios / Variantes</th>
                        <th class="py-4 px-6 text-center">Disponible</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium" id="products-sortable-body">
                    @forelse($products as $product)
                        <tr class="hover:bg-purple-50/40 transition cursor-grab active:cursor-grabbing group product-sort-row"
                            data-id="{{ $product->id }}" id="product-row-{{ $product->id }}">
                            <!-- Handle de arrastre -->
                            <td class="py-4 px-4 text-center text-slate-300 group-hover:text-purple-600 transition">
                                <i class="fas fa-grip-vertical text-base"></i>
                            </td>

                            <!-- Orden numérico -->
                            <td class="py-4 px-4 text-center font-black text-slate-700 text-sm product-order-num">
                                #{{ $product->order }}
                            </td>

                            <!-- Plato -->
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    @if($product->image_path)
                                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-full object-cover shadow-xs border border-slate-200 flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-lg flex-shrink-0">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                            <h4 class="font-bold text-slate-800 text-base leading-tight">{{ $product->name }}</h4>
                                            @if($product->badge)
                                                <span class="bg-red-100 text-red-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                                                    {{ $product->badge }}
                                                </span>
                                            @endif
                                            @if($product->has_cooking_options)
                                                <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center space-x-1 border border-amber-200" title="Permite elegir Horno o Freír">
                                                    <i class="fas fa-fire-burner text-[9px]"></i>
                                                    <span>Horno / Frita</span>
                                                </span>
                                            @endif
                                        </div>
                                        @if($product->description)
                                            <p class="text-xs text-slate-400 max-w-sm truncate mt-0.5">{{ $product->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Categoría -->
                            <td class="py-4 px-6">
                                <span class="px-3 py-1 rounded-xl bg-purple-50 text-purple-700 text-xs font-bold border border-purple-100">
                                    {{ $product->category->name }}
                                </span>
                            </td>

                            <!-- Precios / Variantes -->
                            <td class="py-4 px-6">
                                <div class="space-y-1.5">
                                    @if($product->variants->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($product->variants as $variant)
                                                <span class="inline-flex items-center space-x-1 text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-bold border border-slate-200">
                                                    <span>{{ $variant->name }}:</span>
                                                    <span class="text-purple-700 font-black">${{ number_format($variant->price, 0, ',', '.') }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($product->price !== null)
                                        <span class="text-base font-black text-purple-700">
                                            ${{ number_format($product->price, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Switch de Disponibilidad -->
                            <td class="py-4 px-6 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" onchange="toggleProductAvailability({{ $product->id }})"
                                           {{ $product->is_available ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                    <span id="avail-text-{{ $product->id }}" class="ms-2 text-xs font-bold {{ $product->is_available ? 'text-emerald-700' : 'text-slate-400' }}">
                                        {{ $product->is_available ? 'Disponible' : 'Agotado' }}
                                    </span>
                                </label>
                            </td>

                            <!-- Acciones -->
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-600 text-xs font-bold transition inline-block" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if(Auth::user()->isAdmin())
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar el plato &quot;{{ $product->name }}&quot; de la carta?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-600 text-xs font-bold transition" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                No se encontraron platos con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-6 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function toggleProductAvailability(productId) {
        const url = `/admin/products/${productId}/toggle-availability`;
        const textEl = document.getElementById(`avail-text-${productId}`);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (textEl) {
                    textEl.innerText = data.is_available ? 'Disponible' : 'Agotado';
                    textEl.className = `ms-2 text-xs font-bold ${data.is_available ? 'text-emerald-700' : 'text-slate-400'}`;
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 1800,
                    timerProgressBar: true
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'No se pudo actualizar la disponibilidad.', 'error');
        });
    }

    // Sortable Drag & Drop para Platos
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('products-sortable-body');
        if (!el) return;

        Sortable.create(el, {
            animation: 200,
            ghostClass: 'bg-purple-100',
            handle: '.product-sort-row',
            onEnd: function () {
                const rows = el.querySelectorAll('.product-sort-row');
                const orderIds = [];
                rows.forEach((row, index) => {
                    orderIds.push(row.getAttribute('data-id'));
                    const numBadge = row.querySelector('.product-order-num');
                    if (numBadge) numBadge.innerText = '#' + (index + 1);
                });

                fetch("{{ route('admin.products.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ order: orderIds })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'No se pudo guardar el nuevo orden de platos.', 'error');
                });
            }
        });
    });
</script>
@endpush
@endsection
