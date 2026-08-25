@extends('layouts.admin')

@section('title', 'Editar Categoría')
@section('page-title', 'Editar Categoría: ' . $category->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center space-x-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Volver a Categorías</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nombre de la Categoría *
                    </label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Slug / Identificador URL
                    </label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- SELECCIONADOR VISUAL DE ÍCONOS -->
            <div class="border-t border-slate-100 pt-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Seleccionar Ícono Representativo *
                        </label>
                        <p class="text-xs text-slate-500">Toca el ícono para cambiar el símbolo que se muestra en la carta.</p>
                    </div>
                    <!-- Vista previa del icono seleccionado -->
                    <div class="flex items-center space-x-2 bg-purple-50 px-3.5 py-2 rounded-2xl border border-purple-200">
                        <span class="text-xs font-bold text-purple-700">Ícono actual:</span>
                        <div id="icon-preview-container" class="text-purple-700 flex items-center justify-center text-xl">
                            <x-category-icon :icon="old('icon', $category->icon)" class="w-6 h-6 text-purple-700" />
                        </div>
                    </div>
                </div>

                <input type="hidden" name="icon" id="selected_icon_input" value="{{ old('icon', $category->icon) }}">

                <!-- Grilla de Íconos Seleccionables -->
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2.5 max-h-56 overflow-y-auto p-2.5 bg-slate-50 rounded-2xl border border-slate-200" id="icon-picker-grid">
                    @php
                        $foodIcons = [
                            ['icon' => 'fas fa-pizza-slice', 'label' => 'Pizza'],
                            ['icon' => 'fas fa-box-tissue', 'label' => 'Empanadas'],
                            ['icon' => 'fas fa-burger', 'label' => 'Hamburguesa'],
                            ['icon' => 'icon-papas-fritas', 'label' => 'Papas Fritas 🍟'],
                            ['icon' => 'fas fa-bread-slice', 'label' => 'Sandwich'],
                            ['icon' => 'fas fa-hotdog', 'label' => 'Pancho / Lomito'],
                            ['icon' => 'fas fa-drumstick-bite', 'label' => 'Pollo'],
                            ['icon' => 'fas fa-bone', 'label' => 'Carne / Parrilla'],
                            ['icon' => 'fas fa-egg', 'label' => 'Tortilla / Huevos'],
                            ['icon' => 'fas fa-cheese', 'label' => 'Picada / Queso'],
                            ['icon' => 'fas fa-carrot', 'label' => 'Ensalada'],
                            ['icon' => 'fas fa-chart-pie', 'label' => 'Tarta / Figaza'],
                            ['icon' => 'fas fa-bowl-food', 'label' => 'Plato / Guiso'],
                            ['icon' => 'fas fa-bacon', 'label' => 'Tostados'],
                            ['icon' => 'fas fa-utensils', 'label' => 'Cubiertos'],
                            ['icon' => 'fas fa-plate-wheat', 'label' => 'Minutas'],
                            ['icon' => 'fas fa-glass-water', 'label' => 'Gaseosa'],
                            ['icon' => 'fas fa-beer-mug-empty', 'label' => 'Cerveza'],
                            ['icon' => 'fas fa-wine-glass', 'label' => 'Vino'],
                            ['icon' => 'fas fa-champagne-glasses', 'label' => 'Brindis'],
                            ['icon' => 'fas fa-mug-hot', 'label' => 'Café'],
                            ['icon' => 'fas fa-cake-candles', 'label' => 'Torta'],
                            ['icon' => 'fas fa-ice-cream', 'label' => 'Helado'],
                            ['icon' => 'fas fa-cookie-bite', 'label' => 'Dulce'],
                            ['icon' => 'fas fa-star', 'label' => 'Destacado'],
                            ['icon' => 'fas fa-heart', 'label' => 'La Abuela'],
                            ['icon' => 'fas fa-tag', 'label' => 'Promo'],
                            ['icon' => 'fas fa-trophy', 'label' => 'Favorito'],
                        ];
                        $currentIcon = old('icon', $category->icon);
                    @endphp

                    @foreach($foodIcons as $item)
                        <button type="button"
                                onclick="selectCategoryIcon('{{ $item['icon'] }}')"
                                data-icon="{{ $item['icon'] }}"
                                class="icon-choice-btn flex flex-col items-center justify-center p-2.5 rounded-xl border-2 transition-all {{ $currentIcon === $item['icon'] ? 'bg-purple-100 border-purple-600 text-purple-800 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:border-purple-300 hover:bg-purple-50' }}">
                            <div class="h-6 flex items-center justify-center mb-1">
                                <x-category-icon :icon="$item['icon']" class="w-6 h-6" />
                            </div>
                            <span class="text-[10px] font-bold text-center leading-tight truncate w-full">{{ $item['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Posición / Orden en la Barra
                    </label>
                    <input type="number" name="order" value="{{ old('order', $category->order) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Subtítulo / Aclaración (opcional)
                    </label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $category->subtitle) }}"
                           placeholder="Ej: Fritas u Horno"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- FOTOS EXISTENTES DEL CARRUSEL CON DRAG & DROP -->
            @if($category->images->isNotEmpty())
                <div class="border-t border-slate-100 pt-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Fotos Actuales del Carrusel (Arrastrar para Reordenar)
                            </label>
                            <p class="text-xs text-slate-400">Arrastra y suelta las tarjetas de fotos para cambiar el orden de visualización.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3.5" id="category-photos-sortable-grid">
                        @foreach($category->images->sortBy('order') as $img)
                            <div class="relative group rounded-2xl overflow-hidden border-2 border-slate-200 bg-white p-2 flex flex-col items-center cursor-grab active:cursor-grabbing hover:border-purple-400 hover:shadow-md transition photo-sort-card"
                                 data-id="{{ $img->id }}">
                                <div class="w-full h-24 rounded-xl overflow-hidden mb-1.5 bg-slate-100 flex items-center justify-center">
                                    <img src="{{ asset($img->image_path) }}" alt="{{ $img->alt_text }}" class="w-full h-full object-cover">
                                </div>
                                <span class="text-[11px] font-bold text-slate-700 truncate w-full text-center">{{ $img->alt_text ?: 'Foto' }}</span>
                                <span class="text-[10px] text-purple-600 font-black photo-order-num">#{{ $img->order }}</span>

                                <button type="button"
                                        onclick="if(confirm('¿Eliminar esta foto del carrusel?')) { document.getElementById('delete-img-form-{{ $img->id }}').submit(); }"
                                        class="absolute top-2 right-2 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-md" title="Eliminar foto">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Subir nuevas fotos -->
            <div class="border-t border-slate-100 pt-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Agregar Más Fotos al Carrusel
                </label>
                <input type="file" name="carousel_images[]" multiple accept="image/*"
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
            </div>

            <!-- Visibilidad -->
            <div class="border-t border-slate-100 pt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="w-5 h-5 text-red-600 rounded-lg border-slate-300 focus:ring-red-500">
                    <div>
                        <span class="text-sm font-bold text-slate-800 block">Visible en la carta online</span>
                        <span class="text-xs text-slate-400">Si está desmarcada, los clientes no verán esta categoría en la web.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-md shadow-red-500/20 hover:from-red-700 hover:to-rose-700 transition">
                    Actualizar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Formularios ocultos para eliminar fotos individuales -->
@foreach($category->images as $img)
    <form id="delete-img-form-{{ $img->id }}" action="{{ route('admin.categories.delete-image', $img) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endforeach

@push('scripts')
<script>
    const friesSvgHtml = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" class="inline-block flex-shrink-0 w-6 h-6" fill="none"><path fill="currentColor" d="M18 2C9.716 2 3 6.492 3 14.5c0 .059.052.758.125 1.509C3.391 15.995 18 28 18 28s14.607-12.006 14.871-11.992c.05-.494.129-1.431.129-1.508C33 6.492 26.284 2 18 2z"/><path fill="currentColor" d="M30.166 11.509c-.333-.038-.649.04-.918.196-.003-.432-.059-.811-.202-1.104l.376-1.128c.262-.786-.162-1.635-.948-1.897-.351-.119-.713-.093-1.032.036-.192-.72-.884-1.206-1.645-1.1-.123.017-.238.052-.348.096-.153-.565-.63-1.012-1.246-1.096-.831-.111-1.578.463-1.689 1.284L22.2 9.102c-.296-.397-.779-.642-1.306-.6-.643.046-1.157.491-1.329 1.075l-.073-.728c-.083-.825-.825-1.427-1.642-1.343-.816.082-1.412.804-1.343 1.619l-.02.01c.049-.531-.183-1.041-.6-1.346l-.417-2.084c-.163-.812-.95-1.341-1.765-1.177-.812.162-1.339.953-1.177 1.765l.718 3.591-.382 1.605-1.454-4.002c-.284-.779-1.145-1.181-1.922-.897-.779.284-1.181 1.144-.898 1.923l1.108 3.047c-.345.236-.594.61-.646 1.058l-.245 2.099-.327-1.963c-.136-.818-.911-1.372-1.726-1.233-.817.136-1.369.909-1.233 1.726l1.55 9.299h23.305c.049-.122 1.116-9.38 1.116-9.38.09-.824-.503-1.565-1.326-1.657z"/><path fill="white" d="M27.486 7.797c-.009-.064-.028-.123-.044-.184-.192-.72-.884-1.206-1.645-1.1-.123.017-.238.052-.348.096-.627.248-1.031.895-.935 1.593l1.956 14.343h3.028L27.486 7.797zm-4.99 2.096c-.021-.297-.131-.567-.297-.79-.296-.397-.779-.642-1.306-.6-.643.046-1.157.491-1.329 1.075-.049.167-.074.344-.061.528l.879 12.312.009.127H23.4l-.002-.027-.902-12.625zm-11.781 1.408c-.374-.043-.728.06-1.017.258-.345.236-.594.61-.646 1.058l-1.159 9.929h3.02l1.118-9.581c.096-.823-.493-1.568-1.316-1.664zm4.756-5.595c-.163-.812-.95-1.341-1.765-1.177-.812.162-1.339.953-1.177 1.765l3.25 16.252h3.059l-3.367-16.84z"/><path fill="currentColor" d="M29 17c0 2.762-4.373 5-11 5S7 19.762 7 17c0-1.104-1.896-1-3-1-.316 0-.609-.005-.875.009C3.186 16.644 5 31 5 31c0 2.209 1.791 4 4 4h18c2.209 0 4-1.791 4-4 0 0 1.858-14.864 1.871-14.992-.264-.014-.557-.008-.871-.008-1.104 0-3-.104-3 1z" stroke="white" stroke-width="2"/></svg>`;

    function selectCategoryIcon(iconClass) {
        document.getElementById('selected_icon_input').value = iconClass;
        const previewEl = document.getElementById('icon-preview-container');
        if (iconClass === 'icon-papas-fritas' || iconClass === 'fas fa-french-fries') {
            previewEl.innerHTML = friesSvgHtml;
        } else {
            previewEl.innerHTML = `<i class="${iconClass}"></i>`;
        }

        document.querySelectorAll('.icon-choice-btn').forEach(btn => {
            if (btn.getAttribute('data-icon') === iconClass) {
                btn.className = 'icon-choice-btn flex flex-col items-center justify-center p-2.5 rounded-xl border-2 transition-all bg-purple-100 border-purple-600 text-purple-800 shadow-sm';
            } else {
                btn.className = 'icon-choice-btn flex flex-col items-center justify-center p-2.5 rounded-xl border-2 transition-all bg-white border-slate-200 text-slate-600 hover:border-purple-300 hover:bg-purple-50';
            }
        });
    }

    // Sortable Drag & Drop para Fotos del Carrusel
    document.addEventListener('DOMContentLoaded', function () {
        const photoGrid = document.getElementById('category-photos-sortable-grid');
        if (!photoGrid) return;

        Sortable.create(photoGrid, {
            animation: 200,
            ghostClass: 'opacity-40',
            onEnd: function () {
                const cards = photoGrid.querySelectorAll('.photo-sort-card');
                const orderIds = [];
                cards.forEach((card, index) => {
                    orderIds.push(card.getAttribute('data-id'));
                    const numEl = card.querySelector('.photo-order-num');
                    if (numEl) numEl.innerText = '#' + (index + 1);
                });

                fetch("{{ route('admin.categories.images.reorder') }}", {
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
                    Swal.fire('Error', 'No se pudo guardar el nuevo orden de fotos.', 'error');
                });
            }
        });
    });
</script>
@endpush
@endsection
