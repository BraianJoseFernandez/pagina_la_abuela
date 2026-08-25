@extends('layouts.admin')

@section('title', 'Nueva Categoría')
@section('page-title', 'Crear Nueva Sección')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center space-x-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Volver a Categorías</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nombre de la Categoría *
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Ej: Empanadas Especiales"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Slug / Identificador URL (opcional)
                    </label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           placeholder="empanadas-especiales"
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
                        <p class="text-xs text-slate-500">Toca el ícono que mejor represente a esta sección de la carta.</p>
                    </div>
                    <!-- Vista previa del icono seleccionado -->
                    <div class="flex items-center space-x-2 bg-purple-50 px-3.5 py-2 rounded-2xl border border-purple-200">
                        <span class="text-xs font-bold text-purple-700">Ícono actual:</span>
                        <div id="icon-preview-container" class="text-purple-700 flex items-center justify-center text-xl">
                            <x-category-icon :icon="old('icon', 'fas fa-utensils')" class="w-6 h-6 text-purple-700" />
                        </div>
                    </div>
                </div>

                <input type="hidden" name="icon" id="selected_icon_input" value="{{ old('icon', 'fas fa-utensils') }}">

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
                    @endphp

                    @foreach($foodIcons as $item)
                        <button type="button"
                                onclick="selectCategoryIcon('{{ $item['icon'] }}')"
                                data-icon="{{ $item['icon'] }}"
                                class="icon-choice-btn flex flex-col items-center justify-center p-2.5 rounded-xl border-2 transition-all {{ old('icon', 'fas fa-utensils') === $item['icon'] ? 'bg-purple-100 border-purple-600 text-purple-800 shadow-sm' : 'bg-white border-slate-200 text-slate-600 hover:border-purple-300 hover:bg-purple-50' }}">
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
                    <input type="number" name="order" value="{{ old('order', 1) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Subtítulo / Aclaración (opcional)
                    </label>
                    <input type="text" name="subtitle" value="{{ old('subtitle') }}"
                           placeholder="Ej: Fritas u Horno, Para compartir, etc."
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Fotos del Carrusel de la Categoría -->
            <div class="border-t border-slate-100 pt-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Fotos de Muestra para el Carrusel de la Categoría (opcional)
                </label>
                <p class="text-xs text-slate-500 mb-3">Puedes seleccionar una o varias imágenes de platos para que los clientes las vean en el carrusel superior.</p>
                <input type="file" name="carousel_images[]" multiple accept="image/*"
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
            </div>

            <!-- Visibilidad -->
            <div class="border-t border-slate-100 pt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
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
                    Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

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
</script>
@endpush
@endsection
