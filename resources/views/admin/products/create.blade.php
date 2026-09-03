@extends('layouts.admin')

@section('title', 'Nuevo Plato')
@section('page-title', 'Agregar Plato a la Carta')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center space-x-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Volver a Platos</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form" class="space-y-6">
            @csrf

            <!-- Campo oculto para la imagen recortada en Base64 con Cropper.js -->
            <input type="hidden" name="cropped_image_base64" id="cropped_image_base64">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Categoría -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Categoría / Sección *
                    </label>
                    <select name="category_id" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                        <option value="">Selecciona una categoría</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Nombre del Plato -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nombre del Plato / Ítem *
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Ej: Pizza Napolitana Especial"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Descripción / Ingredientes
                </label>
                <textarea name="description" rows="2.5"
                          placeholder="Muzzarella, salsa de tomate, jamón, huevo, rodajas de tomate, orégano..."
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Insignia / Badge -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Insignia Destacada (opcional)
                    </label>
                    <input type="text" name="badge" value="{{ old('badge') }}"
                           placeholder="Ej: ⭐ La Abuela, Bomba, Promo, Especial"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <!-- Orden de aparición -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Posición / Orden
                    </label>
                    <input type="number" name="order" value="{{ old('order', 1) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- SISTEMA DE PRECIOS Y VARIANTES -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800">Precios y Variantes</h4>
                        <p class="text-xs text-slate-500">Puedes ingresar un precio único o múltiples opciones (ej: Media / Entera, Cuarto / Entera, etc.)</p>
                    </div>
                </div>

                <!-- Selector de Modo de Precio -->
                <div class="flex items-center space-x-6">
                    <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="price_type" value="single" checked onchange="togglePriceMode('single')" class="text-red-600 focus:ring-red-500">
                        <span>Precio Fijo Único</span>
                    </label>
                    <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="price_type" value="variants" onchange="togglePriceMode('variants')" class="text-red-600 focus:ring-red-500">
                        <span>Múltiples Variantes / Tamaños</span>
                    </label>
                </div>

                <!-- Contenedor Precio Único -->
                <div id="single-price-container">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Precio ($)
                    </label>
                    <div class="relative max-w-xs">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-bold">$</span>
                        <input type="number" step="0.01" name="price" value="{{ old('price') }}"
                               placeholder="12000"
                               class="w-full pl-9 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Contenedor Múltiples Variantes -->
                <div id="variants-container" class="hidden space-y-3">
                    <div id="variants-list" class="space-y-2.5">
                        <div class="flex items-center space-x-3 variant-row">
                            <input type="text" name="variant_names[]" placeholder="Nombre (ej: Media)" class="w-1/2 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                            <div class="relative w-1/2">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">$</span>
                                <input type="number" step="0.01" name="variant_prices[]" placeholder="Precio" class="w-full pl-7 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                            </div>
                            <button type="button" onclick="removeVariantRow(this)" class="text-slate-400 hover:text-red-500 p-2">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="flex items-center space-x-3 variant-row">
                            <input type="text" name="variant_names[]" placeholder="Nombre (ej: Entera)" class="w-1/2 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                            <div class="relative w-1/2">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">$</span>
                                <input type="number" step="0.01" name="variant_prices[]" placeholder="Precio" class="w-full pl-7 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                            </div>
                            <button type="button" onclick="removeVariantRow(this)" class="text-slate-400 hover:text-red-500 p-2">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="button" onclick="addVariantRow()" class="inline-flex items-center space-x-2 text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-4 py-2 rounded-xl transition">
                        <i class="fas fa-plus"></i>
                        <span>Añadir otra variante</span>
                    </button>
                </div>
            </div>

            <!-- SISTEMA DE OPCIÓN DE COCCIÓN (HORNO / FREÍR) -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center space-x-2">
                            <i class="fas fa-fire-burner text-amber-500"></i>
                            <span>Opción de Cocción (Horno / Freír)</span>
                        </h4>
                        <p class="text-xs text-slate-500">Permite que el cliente elija la preparación (ideal para empanadas, pasteles, etc.). Solo se mostrará en el carrito si esta opción está activada.</p>
                    </div>
                </div>

                <div class="bg-amber-50/60 border border-amber-200/70 rounded-2xl p-4 space-y-3">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="has_cooking_options" id="has_cooking_options" value="1" {{ old('has_cooking_options') ? 'checked' : '' }}
                               onchange="toggleCookingOptions(this.checked)"
                               class="w-5 h-5 text-amber-600 rounded-lg border-slate-300 focus:ring-amber-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Habilitar selección de Horno o Freír para este plato</span>
                            <span class="text-xs text-slate-500">Al agregar el plato o en el carrito, el cliente podrá seleccionar si lo quiere al horno o frito.</span>
                        </div>
                    </label>

                    <div id="cooking-options-details" class="{{ old('has_cooking_options') ? '' : 'hidden' }} pt-2 border-t border-amber-200/60 flex flex-wrap gap-4 items-center">
                        <span class="text-xs font-bold text-slate-700">Variantes disponibles para el cliente:</span>
                        <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-amber-200 shadow-2xs">
                            <input type="checkbox" name="cooking_options[]" value="Al Horno" checked class="text-amber-600 rounded focus:ring-amber-500">
                            <span>🔥 Al Horno</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-amber-200 shadow-2xs">
                            <input type="checkbox" name="cooking_options[]" value="Frita" checked class="text-amber-600 rounded focus:ring-amber-500">
                            <span>🍳 Frita</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- FOTO DEL PLATO CON RECORTADOR CIRCULAR (CROPPER.JS) -->
            <div class="border-t border-slate-100 pt-6 space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Foto del Plato (Recorte y Zoom Redondeado)
                </label>
                <p class="text-xs text-slate-500">Sube una foto y podrás recortarla, agrandarla o ajustarla en formato circular perfecto para la carta.</p>

                <div class="flex flex-col sm:flex-row items-center gap-5">
                    <input type="file" id="dish_image_input" accept="image/*"
                           class="w-full sm:w-auto text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">

                    <!-- Preview circular -->
                    <div id="cropped-preview-container" class="hidden items-center space-x-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-200">
                        <img id="cropped-preview-img" src="" alt="Vista previa" class="w-16 h-16 rounded-full object-cover shadow-md border-2 border-red-500">
                        <span class="text-xs font-bold text-emerald-600 flex items-center space-x-1">
                            <i class="fas fa-check-circle"></i>
                            <span>Foto ajustada y redondeada</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Disponibilidad -->
            <div class="border-t border-slate-100 pt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }}
                           class="w-5 h-5 text-red-600 rounded-lg border-slate-300 focus:ring-red-500">
                    <div>
                        <span class="text-sm font-bold text-slate-800 block">Disponible para pedidos</span>
                        <span class="text-xs text-slate-400">Si se desmarca, aparecerá como "Agotado por hoy" en la carta.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-md shadow-red-500/20 hover:from-red-700 hover:to-rose-700 transition">
                    Guardar Plato
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Recorte Cropper.js -->
<div id="cropper-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h3 class="text-lg font-black text-slate-800">Ajustar y Recortar Foto Redondeada</h3>
            <button onclick="closeCropperModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="max-h-[50vh] overflow-hidden bg-slate-900 rounded-2xl flex items-center justify-center">
            <img id="cropper-image-target" src="" class="max-w-full block">
        </div>

        <!-- Controles de Zoom -->
        <div class="flex items-center justify-center space-x-4 pt-2">
            <button type="button" onclick="cropper.zoom(0.1)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                <i class="fas fa-search-plus"></i>
                <span>Agrandar</span>
            </button>
            <button type="button" onclick="cropper.zoom(-0.1)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                <i class="fas fa-search-minus"></i>
                <span>Alejar</span>
            </button>
            <button type="button" onclick="cropper.rotate(90)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                <i class="fas fa-rotate-right"></i>
                <span>Girar</span>
            </button>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
            <button type="button" onclick="closeCropperModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                Cancelar
            </button>
            <button type="button" onclick="applyCrop()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs shadow-md transition">
                Aplicar Recorte Circular
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePriceMode(mode) {
        if (mode === 'single') {
            document.getElementById('single-price-container').classList.remove('hidden');
            document.getElementById('variants-container').classList.add('hidden');
        } else {
            document.getElementById('single-price-container').classList.add('hidden');
            document.getElementById('variants-container').classList.remove('hidden');
        }
    }

    function toggleCookingOptions(isChecked) {
        const details = document.getElementById('cooking-options-details');
        if (details) {
            if (isChecked) {
                details.classList.remove('hidden');
            } else {
                details.classList.add('hidden');
            }
        }
    }

    function addVariantRow() {
        const list = document.getElementById('variants-list');
        const row = document.createElement('div');
        row.className = 'flex items-center space-x-3 variant-row';
        row.innerHTML = `
            <input type="text" name="variant_names[]" placeholder="Nombre (ej: Docena)" class="w-1/2 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
            <div class="relative w-1/2">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">$</span>
                <input type="number" step="0.01" name="variant_prices[]" placeholder="Precio" class="w-full pl-7 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
            </div>
            <button type="button" onclick="removeVariantRow(this)" class="text-slate-400 hover:text-red-500 p-2">
                <i class="fas fa-trash"></i>
            </button>
        `;
        list.appendChild(row);
    }

    function removeVariantRow(btn) {
        btn.closest('.variant-row').remove();
    }

    // Inicializador de Cropper.js
    let cropper = null;
    const fileInput = document.getElementById('dish_image_input');
    const modal = document.getElementById('cropper-modal');
    const imageTarget = document.getElementById('cropper-image-target');

    fileInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            const reader = new FileReader();
            reader.onload = function (event) {
                imageTarget.src = event.target.result;
                modal.classList.remove('hidden');

                if (cropper) cropper.destroy();
                cropper = new Cropper(imageTarget, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    restore: false,
                    guides: false,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            reader.readAsDataURL(file);
        }
    });

    function closeCropperModal() {
        modal.classList.add('hidden');
        if (cropper) cropper.destroy();
    }

    function applyCrop() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({
            width: 600,
            height: 600,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        // Crear versión redondeada / circular
        const roundedCanvas = document.createElement('canvas');
        roundedCanvas.width = 600;
        roundedCanvas.height = 600;
        const ctx = roundedCanvas.getContext('2d');
        ctx.beginPath();
        ctx.arc(300, 300, 300, 0, 2 * Math.PI, true);
        ctx.closePath();
        ctx.clip();
        ctx.drawImage(canvas, 0, 0, 600, 600);

        const base64Data = roundedCanvas.toDataURL('image/png');
        document.getElementById('cropped_image_base64').value = base64Data;

        // Preview
        document.getElementById('cropped-preview-img').src = base64Data;
        document.getElementById('cropped-preview-container').classList.remove('hidden');
        document.getElementById('cropped-preview-container').classList.add('flex');

        closeCropperModal();
    }
</script>
@endpush
@endsection
