@extends('layouts.admin')

@section('title', 'Categorías y Secciones')
@section('page-title', 'Categorías de la Carta')

@section('content')
<div class="space-y-6">
    <!-- Encabezado con Botón de Creación y Helper de Drag & Drop -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-200/80">
        <div>
            <h3 class="text-xl font-black text-slate-800">Secciones del Menú</h3>
            <p class="text-xs text-slate-500 mt-0.5 flex items-center space-x-1">
                <i class="fas fa-arrows-up-down-left-right text-purple-500"></i>
                <span>Arrastra y suelta las filas para cambiar el orden de las categorías en la carta.</span>
            </p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
           class="px-5 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold text-sm shadow-md shadow-red-500/20 flex items-center justify-center space-x-2 transition">
            <i class="fas fa-plus"></i>
            <span>Nueva Sección / Categoría</span>
        </a>
    </div>

    <!-- Listado de Categorías con Drag & Drop -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-4 px-4 w-12 text-center"><i class="fas fa-grip-vertical text-slate-400"></i></th>
                        <th class="py-4 px-4 w-16 text-center">N°</th>
                        <th class="py-4 px-6">Categoría</th>
                        <th class="py-4 px-6">Platos</th>
                        <th class="py-4 px-6">Fotos Carrusel</th>
                        <th class="py-4 px-6">Estado</th>
                        <th class="py-4 px-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium" id="categories-sortable-body">
                    @forelse($categories as $category)
                        <tr class="hover:bg-purple-50/50 transition cursor-grab active:cursor-grabbing group category-sort-row"
                            data-id="{{ $category->id }}">
                            <!-- Handle de arrastre -->
                            <td class="py-4 px-4 text-center text-slate-300 group-hover:text-purple-600 transition">
                                <i class="fas fa-grip-vertical text-base"></i>
                            </td>
                            <!-- Número de orden -->
                            <td class="py-4 px-4 text-center font-black text-slate-700 text-sm category-order-num">
                                #{{ $category->order }}
                            </td>
                            <!-- Categoría -->
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center text-lg shadow-xs border border-purple-100">
                                        <x-category-icon :icon="$category->icon ?? 'fas fa-utensils'" class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-base">{{ $category->name }}</h4>
                                        <span class="text-xs text-slate-400 font-mono">/categoria/{{ $category->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <!-- Platos -->
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl transition">
                                    <i class="fas fa-utensils text-[10px]"></i>
                                    <span>{{ $category->products_count }} platos</span>
                                </a>
                            </td>
                            <!-- Fotos -->
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center space-x-1 text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                                    <i class="fas fa-images text-slate-400 text-xs"></i>
                                    <span>{{ $category->images->count() }} fotos</span>
                                </span>
                            </td>
                            <!-- Estado -->
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $category->is_active ? 'Visible en Menú' : 'Oculta' }}
                                </span>
                            </td>
                            <!-- Acciones -->
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-600 text-xs font-bold transition inline-block" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if(Auth::user()->isAdmin())
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar la categoría &quot;{{ $category->name }}&quot; y todos sus platos asociados?');">
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
                            <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                No se han registrado categorías aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('categories-sortable-body');
        if (!el) return;

        Sortable.create(el, {
            animation: 200,
            ghostClass: 'bg-purple-100',
            handle: '.category-sort-row',
            onEnd: function () {
                const rows = el.querySelectorAll('.category-sort-row');
                const orderIds = [];
                rows.forEach((row, index) => {
                    orderIds.push(row.getAttribute('data-id'));
                    const numBadge = row.querySelector('.category-order-num');
                    if (numBadge) numBadge.innerText = '#' + (index + 1);
                });

                fetch("{{ route('admin.categories.reorder') }}", {
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
                    Swal.fire('Error', 'No se pudo guardar el nuevo orden.', 'error');
                });
            }
        });
    });
</script>
@endpush
@endsection
