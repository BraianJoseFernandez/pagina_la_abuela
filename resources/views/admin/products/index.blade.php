@extends('layouts.admin')

@section('title', 'Platos y Carta')
@section('page-title', 'Gestión de Platos y Precios')

@section('content')
<div class="space-y-6">
    <!-- Barra Superior de Filtros y Creación -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-slate-200/80">
        <!-- Filtro por Categoría y Buscador -->
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
            <div class="relative min-w-[180px] flex-grow sm:flex-grow-0">
                <select name="category_id" onchange="this.form.submit()"
                        class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500 appearance-none cursor-pointer">
                    <option value="">Todas las Categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>

            <div class="relative flex-grow sm:min-w-[220px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar plato o ingrediente..."
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            </div>

            @if($selectedCategoryId || $search)
                <a href="{{ route('admin.products.index') }}" class="px-3.5 py-2.5 rounded-2xl text-xs font-bold text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 transition text-center">
                    Limpiar Filtros
                </a>
            @endif
        </form>

        <!-- Botón Crear Nuevo Plato -->
        <a href="{{ route('admin.products.create') }}"
           class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold text-sm shadow-md shadow-red-500/20 flex items-center justify-center space-x-2 transition flex-shrink-0">
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
            <span class="font-bold text-slate-700 whitespace-nowrap ml-2">{{ $products->total() }} platos registrados</span>
        </div>

        <!-- Vista Desktop: Tabla con Drag & Drop por Handle -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-2.5 w-10 text-center"><i class="fas fa-grip-vertical text-slate-400"></i></th>
                        <th class="py-3 px-2.5 w-12 text-center">N°</th>
                        <th class="py-3 px-4 min-w-[200px]">Plato / Ítem</th>
                        <th class="py-3 px-3.5 whitespace-nowrap">Categoría</th>
                        <th class="py-3 px-3.5 min-w-[180px]">Precios / Variantes</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap">Disponible</th>
                        <th class="py-3 px-4 text-right sticky-action-col bg-slate-50 whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium" id="products-sortable-body">
                    @forelse($products as $product)
                        <tr class="hover:bg-purple-50/40 transition group product-sort-row"
                            data-id="{{ $product->id }}" id="product-row-{{ $product->id }}">
                            <!-- Handle de arrastre exclusivo -->
                            <td class="py-3 px-2.5 text-center text-slate-300 group-hover:text-purple-600 transition drag-handle">
                                <i class="fas fa-grip-vertical text-base"></i>
                            </td>

                            <!-- Orden numérico -->
                            <td class="py-3 px-2.5 text-center font-black text-slate-700 text-sm product-order-num">
                                #{{ $product->order }}
                            </td>

                            <!-- Plato -->
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-3">
                                    @if($product->image_path)
                                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-11 h-11 rounded-full object-cover shadow-xs border border-slate-200 flex-shrink-0">
                                    @else
                                        <div class="w-11 h-11 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-base flex-shrink-0">
                                            <i class="fas fa-utensils"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex items-center space-x-1.5 flex-wrap gap-y-1">
                                            <h4 class="font-bold text-slate-800 text-sm leading-tight">{{ $product->name }}</h4>
                                            @if($product->badge)
                                                <span class="bg-red-100 text-red-700 text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-wider whitespace-nowrap">
                                                    {{ $product->badge }}
                                                </span>
                                            @endif
                                            @if($product->has_cooking_options)
                                                <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-1.5 py-0.5 rounded-full inline-flex items-center space-x-1 border border-amber-200 whitespace-nowrap" title="Permite elegir Horno o Freír">
                                                    <i class="fas fa-fire-burner text-[8px]"></i>
                                                    <span>🔥 Horno/Frita</span>
                                                </span>
                                            @endif
                                        </div>
                                        @if($product->description)
                                            <p class="text-xs text-slate-400 max-w-xs truncate mt-0.5">{{ $product->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Categoría (Siempre en una sola línea sin salto feo) -->
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 text-xs font-bold border border-purple-100 whitespace-nowrap inline-block">
                                    {{ $product->category->name }}
                                </span>
                            </td>

                            <!-- Precios / Variantes (Diseño compacto vertical para que nunca ensanche la tabla de más) -->
                            <td class="py-3 px-3.5">
                                @if($product->variants->isNotEmpty())
                                    <div class="space-y-1 max-w-[210px]">
                                        @foreach($product->variants as $variant)
                                            <div class="flex items-center justify-between text-[11px] bg-slate-50 text-slate-700 px-2 py-0.5 rounded-lg border border-slate-200/80">
                                                <span class="truncate mr-1.5 font-semibold text-slate-600">{{ $variant->name }}:</span>
                                                <span class="text-purple-700 font-black flex-shrink-0">${{ number_format($variant->price, 0, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($product->price !== null)
                                    <span class="text-sm font-black text-purple-700">
                                        ${{ number_format($product->price, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Switch de Disponibilidad -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" onchange="toggleProductAvailability({{ $product->id }})"
                                           {{ $product->is_available ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="relative w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                    <span id="avail-text-{{ $product->id }}" class="ms-1.5 text-xs font-bold whitespace-nowrap {{ $product->is_available ? 'text-emerald-700' : 'text-slate-400' }}">
                                        {{ $product->is_available ? 'Disponible' : 'Agotado' }}
                                    </span>
                                </label>
                            </td>

                            <!-- Acciones (Sticky a la derecha: nunca se sale de pantalla ni se corta) -->
                            <td class="py-3 px-4 text-right space-x-1.5 sticky-action-col bg-white group-hover:bg-purple-50 transition-colors whitespace-nowrap">
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

        <!-- Vista Mobile: Tarjetas Adaptativas (Sin scroll horizontal, arrastre SOLO por puntitos) -->
        <div class="block lg:hidden p-3 space-y-3 bg-slate-100/60" id="products-sortable-mobile">
            @forelse($products as $product)
                <div class="product-sort-card bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs space-y-3"
                     data-id="{{ $product->id }}" id="product-card-{{ $product->id }}">
                    <!-- Fila Superior: Handle exclusivo + Orden + Categoría + Switch Disponibilidad -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center space-x-2 min-w-0">
                            <!-- Puntitos de arrastre con padding táctil -->
                            <div class="drag-handle w-9 h-9 rounded-xl bg-slate-100 active:bg-purple-100 text-slate-400 active:text-purple-700 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-grip-vertical text-base"></i>
                            </div>
                            <span class="product-order-num font-black text-slate-700 text-xs bg-slate-100 px-2 py-1 rounded-lg flex-shrink-0">
                                #{{ $product->order }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 text-[11px] font-bold border border-purple-100 truncate">
                                {{ $product->category->name }}
                            </span>
                        </div>

                        <!-- Switch de Disponibilidad -->
                        <label class="inline-flex items-center cursor-pointer select-none flex-shrink-0">
                            <input type="checkbox" onchange="toggleProductAvailability({{ $product->id }})"
                                   {{ $product->is_available ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="relative w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span id="avail-text-m-{{ $product->id }}" class="ms-1.5 text-[11px] font-bold {{ $product->is_available ? 'text-emerald-700' : 'text-slate-400' }}">
                                {{ $product->is_available ? 'Disponible' : 'Agotado' }}
                            </span>
                        </label>
                    </div>

                    <!-- Fila Central: Foto + Nombre + Badges + Descripción -->
                    <div class="flex items-start space-x-3">
                        @if($product->image_path)
                            <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-14 h-14 rounded-2xl object-cover border border-slate-200 flex-shrink-0 shadow-xs">
                        @else
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl flex-shrink-0 border border-slate-200">
                                <i class="fas fa-utensils"></i>
                            </div>
                        @endif
                        <div class="flex-grow min-w-0">
                            <div class="flex items-center space-x-1.5 flex-wrap gap-y-1">
                                <h4 class="font-bold text-slate-800 text-sm leading-snug">{{ $product->name }}</h4>
                                @if($product->badge)
                                    <span class="bg-red-100 text-red-700 text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider">
                                        {{ $product->badge }}
                                    </span>
                                @endif
                                @if($product->has_cooking_options)
                                    <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-1.5 py-0.5 rounded-md inline-flex items-center space-x-1 border border-amber-200">
                                        <i class="fas fa-fire-burner text-[8px]"></i>
                                        <span>Horno/Frita</span>
                                    </span>
                                @endif
                            </div>
                            @if($product->description)
                                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $product->description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Fila Inferior: Precios y Acciones perfectamente adaptados a la pantalla -->
                    <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div class="flex-grow min-w-0">
                            @if($product->variants->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($product->variants as $variant)
                                        <span class="inline-flex items-center space-x-1 text-[11px] bg-slate-50 text-slate-700 px-2 py-0.5 rounded-md font-bold border border-slate-200">
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

                        <!-- Botones de Acción Mobile -->
                        <div class="flex items-center space-x-2 flex-shrink-0">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition flex items-center space-x-1">
                                <i class="fas fa-edit text-xs"></i>
                                <span>Editar</span>
                            </a>
                            @if(Auth::user()->isAdmin())
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar el plato &quot;{{ $product->name }}&quot; de la carta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-slate-400 text-xs bg-white rounded-2xl p-6">
                    No se encontraron platos con los filtros seleccionados.
                </div>
            @endforelse
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
        const textDesktop = document.getElementById(`avail-text-${productId}`);
        const textMobile = document.getElementById(`avail-text-m-${productId}`);

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
                const label = data.is_available ? 'Disponible' : 'Agotado';
                const cls = `ms-2 text-xs font-bold ${data.is_available ? 'text-emerald-700' : 'text-slate-400'}`;
                const clsMobile = `ms-1.5 text-[11px] font-bold ${data.is_available ? 'text-emerald-700' : 'text-slate-400'}`;

                if (textDesktop) {
                    textDesktop.innerText = label;
                    textDesktop.className = cls;
                }
                if (textMobile) {
                    textMobile.innerText = label;
                    textMobile.className = clsMobile;
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

    // Configuración de SortableJS con handle exclusivo (.drag-handle)
    document.addEventListener('DOMContentLoaded', function () {
        function setupSortableList(containerId, itemSelector) {
            const el = document.getElementById(containerId);
            if (!el) return;

            Sortable.create(el, {
                animation: 200,
                ghostClass: 'bg-purple-100',
                handle: '.drag-handle', // Solo se mueve al arrastrar desde los puntitos
                onEnd: function () {
                    const items = el.querySelectorAll(itemSelector);
                    const orderIds = [];
                    items.forEach((item, index) => {
                        orderIds.push(item.getAttribute('data-id'));
                        const numBadge = item.querySelector('.product-order-num');
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
        }

        setupSortableList('products-sortable-body', '.product-sort-row');
        setupSortableList('products-sortable-mobile', '.product-sort-card');
    });
</script>
@endpush
@endsection
