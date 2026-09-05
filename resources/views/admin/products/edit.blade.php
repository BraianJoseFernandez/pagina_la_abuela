@extends('layouts.admin')

@section('title', 'Editar Plato')
@section('page-title', 'Editar Plato: ' . $product->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.products.index', ['category_id' => $product->category_id]) }}" class="inline-flex items-center space-x-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Volver a Platos</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form" class="space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="cropped_image_base64" id="cropped_image_base64">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Categoría -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Categoría / Sección *
                    </label>
                    <select name="category_id" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
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
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Descripción / Ingredientes
                </label>
                <textarea name="description" rows="2.5"
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Insignia / Badge -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Insignia Destacada (opcional)
                    </label>
                    <input type="text" name="badge" value="{{ old('badge', $product->badge) }}"
                           placeholder="Ej: ⭐ La Abuela, Bomba, Promo, Especial"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <!-- Orden de aparición -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Posición / Orden
                    </label>
                    <input type="number" name="order" value="{{ old('order', $product->order) }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- SISTEMA DE PRECIOS Y VARIANTES -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800">Precios y Variantes</h4>
                        <p class="text-xs text-slate-500">Puedes configurar un precio único o múltiples variantes (ej: Media / Entera, etc.)</p>
                    </div>
                </div>

                @php
                    $hasVariants = $product->variants->isNotEmpty();
                @endphp

                <!-- Selector de Modo de Precio -->
                <div class="flex items-center space-x-6">
                    <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="price_type" value="single" {{ !$hasVariants ? 'checked' : '' }} onchange="togglePriceMode('single')" class="text-red-600 focus:ring-red-500">
                        <span>Precio Fijo Único</span>
                    </label>
                    <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="price_type" value="variants" {{ $hasVariants ? 'checked' : '' }} onchange="togglePriceMode('variants')" class="text-red-600 focus:ring-red-500">
                        <span>Múltiples Variantes / Tamaños</span>
                    </label>
                </div>

                <!-- Contenedor Precio Único -->
                <div id="single-price-container" class="{{ $hasVariants ? 'hidden' : '' }}">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Precio ($)
                    </label>
                    <div class="relative max-w-xs">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-bold">$</span>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}"
                               class="w-full pl-9 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-black text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Contenedor Múltiples Variantes -->
                <div id="variants-container" class="{{ !$hasVariants ? 'hidden' : '' }} space-y-3">
                    <div id="variants-list" class="space-y-2.5">
                        @if($hasVariants)
                            @foreach($product->variants as $variant)
                                <div class="flex items-center space-x-3 variant-row">
                                    <input type="text" name="variant_names[]" value="{{ $variant->name }}" placeholder="Nombre (ej: Media)" class="w-1/2 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                                    <div class="relative w-1/2">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">$</span>
                                        <input type="number" step="0.01" name="variant_prices[]" value="{{ $variant->price }}" placeholder="Precio" class="w-full pl-7 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                                    </div>
                                    <button type="button" onclick="removeVariantRow(this)" class="text-slate-400 hover:text-red-500 p-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        @else
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
                        @endif
                    </div>

                    <button type="button" onclick="addVariantRow()" class="inline-flex items-center space-x-2 text-xs font-bold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 px-4 py-2 rounded-xl transition">
                        <i class="fas fa-plus"></i>
                        <span>Añadir otra variante</span>
                    </button>
                </div>
            </div>

            @php
                $hasCooking = old('has_cooking_options', $product->has_cooking_options);
                $selectedOptions = old('cooking_options', $product->getCookingOptionsList());
                if (!is_array($selectedOptions)) {
                    $selectedOptions = ['Horno', 'Frita'];
                }
            @endphp

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
                        <input type="checkbox" name="has_cooking_options" id="has_cooking_options" value="1" {{ $hasCooking ? 'checked' : '' }}
                               onchange="toggleCookingOptions(this.checked)"
                               class="w-5 h-5 text-amber-600 rounded-lg border-slate-300 focus:ring-amber-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Habilitar selección de Horno o Freír para este plato</span>
                            <span class="text-xs text-slate-500">Al agregar el plato o en el carrito, el cliente podrá seleccionar si lo quiere al horno o frito.</span>
                        </div>
                    </label>

                    <div id="cooking-options-details" class="{{ $hasCooking ? '' : 'hidden' }} pt-2 border-t border-amber-200/60 flex flex-wrap gap-4 items-center">
                        <span class="text-xs font-bold text-slate-700">Variantes disponibles para el cliente:</span>
                        <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-amber-200 shadow-2xs">
                            <input type="checkbox" name="cooking_options[]" value="Horno" {{ (in_array('Horno', $selectedOptions) || in_array('Al Horno', $selectedOptions)) ? 'checked' : '' }} class="text-amber-600 rounded focus:ring-amber-500">
                            <span>🔥 Horno</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-700 bg-white px-3 py-1.5 rounded-xl border border-amber-200 shadow-2xs">
                            <input type="checkbox" name="cooking_options[]" value="Frita" {{ in_array('Frita', $selectedOptions) ? 'checked' : '' }} class="text-amber-600 rounded focus:ring-amber-500">
                            <span>🍳 Frita</span>
                        </label>
                    </div>
                </div>
            </div>

            @php
                $hasGarnishes = old('has_garnishes', $product->has_garnishes);
            @endphp

            <!-- SISTEMA DE GUARNICIONES (ACOMPAÑAMIENTOS) -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center space-x-2">
                            <i class="fas fa-bowl-food text-emerald-600"></i>
                            <span>Guarniciones / Acompañamientos</span>
                        </h4>
                        <p class="text-xs text-slate-500">Configura opciones de guarnición para que el cliente elija (con foto redondeada, precio extra y descripción).</p>
                    </div>
                </div>

                <div class="bg-emerald-50/60 border border-emerald-200/70 rounded-2xl p-4 sm:p-5 space-y-4">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="has_garnishes" id="has_garnishes" value="1" {{ $hasGarnishes ? 'checked' : '' }}
                               onchange="toggleGarnishesSection(this.checked)"
                               class="w-5 h-5 text-emerald-600 rounded-lg border-slate-300 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Habilitar selección de Guarnición para este plato</span>
                            <span class="text-xs text-slate-500">El cliente podrá seleccionar qué guarnición prefiere al pedir este plato.</span>
                        </div>
                    </label>

                    <div id="garnishes-section-details" class="{{ $hasGarnishes ? '' : 'hidden' }} pt-3 border-t border-emerald-200/60 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-900">Opciones de Guarnición (sin límite):</span>
                            <button type="button" onclick="addGarnishRow()" class="inline-flex items-center space-x-1.5 text-xs font-black text-emerald-700 hover:text-emerald-900 bg-white hover:bg-emerald-100/60 px-3 py-1.5 rounded-xl border border-emerald-300 shadow-2xs transition">
                                <i class="fas fa-plus"></i>
                                <span>Añadir otra guarnición</span>
                            </button>
                        </div>

                        <div id="garnishes-list" class="space-y-3">
                            <!-- Filas dinámicas de guarniciones -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOTO DEL PLATO CON RECORTADOR CIRCULAR (CROPPER.JS) -->
            <div class="border-t border-slate-100 pt-6 space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Foto del Plato (Recorte y Zoom Redondeado)
                </label>

                <div class="flex flex-col sm:flex-row items-center gap-5">
                    @if($product->image_path)
                        <div class="flex items-center space-x-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-200">
                            <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-full object-cover shadow-md border-2 border-purple-300">
                            <span class="text-xs font-bold text-slate-600">Foto Actual</span>
                        </div>
                    @endif

                    <input type="file" id="dish_image_input" accept="image/*"
                           class="w-full sm:w-auto text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">

                    <!-- Preview circular recortada -->
                    <div id="cropped-preview-container" class="hidden items-center space-x-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-200">
                        <img id="cropped-preview-img" src="" alt="Vista previa" class="w-16 h-16 rounded-full object-cover shadow-md border-2 border-red-500">
                        <span class="text-xs font-bold text-emerald-600 flex items-center space-x-1">
                            <i class="fas fa-check-circle"></i>
                            <span>Nueva foto redondeada</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Disponibilidad -->
            <div class="border-t border-slate-100 pt-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_available" value="1" {{ old('is_available', $product->is_available) ? 'checked' : '' }}
                           class="w-5 h-5 text-red-600 rounded-lg border-slate-300 focus:ring-red-500">
                    <div>
                        <span class="text-sm font-bold text-slate-800 block">Disponible para pedidos</span>
                        <span class="text-xs text-slate-400">Si se desmarca, aparecerá como "Agotado por hoy" en la carta.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.products.index', ['category_id' => $product->category_id]) }}" class="px-5 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-md shadow-red-500/20 hover:from-red-700 hover:to-rose-700 transition">
                    Actualizar Plato
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

    // Lógica para Guarniciones
    function toggleGarnishesSection(isChecked) {
        const section = document.getElementById('garnishes-section-details');
        if (section) {
            if (isChecked) {
                section.classList.remove('hidden');
                const list = document.getElementById('garnishes-list');
                if (list && list.children.length === 0) {
                    addGarnishRow('Papas Fritas Tradicionales', 0, 'Papas bastón crocantes doradas al punto justo');
                    addGarnishRow('Puré de Papas Casero', 0, 'Puré suave y cremoso con manteca y nuez moscada');
                }
            } else {
                section.classList.add('hidden');
            }
        }
    }

    function addGarnishRow(name = '', price = '0', desc = '', image = '') {
        const list = document.getElementById('garnishes-list');
        const row = document.createElement('div');
        row.className = 'garnish-row bg-white p-3.5 sm:p-4 rounded-2xl border border-emerald-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center gap-3.5 transition hover:border-emerald-400';
        
        row.innerHTML = `
            <!-- Foto con Recortador Circular -->
            <div class="flex items-center space-x-3 flex-shrink-0">
                <div class="relative group cursor-pointer" onclick="this.parentElement.querySelector('.garnish-file-input').click()" title="Toca para subir y recortar foto en círculo">
                    <img src="${image || ''}" class="garnish-preview-img w-14 h-14 rounded-full object-cover border-2 border-emerald-500 shadow-sm ${image ? '' : 'hidden'}">
                    <div class="garnish-icon-placeholder w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center border-2 border-dashed border-emerald-400 group-hover:bg-emerald-200 transition ${image ? 'hidden' : ''}">
                        <i class="fas fa-camera text-base"></i>
                    </div>
                    <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white text-xs">
                        <i class="fas fa-crop-simple"></i>
                    </div>
                </div>
                <input type="file" accept="image/*" class="garnish-file-input hidden" onchange="handleGarnishFileSelect(this)">
                <input type="hidden" name="garnish_cropped_base64[]" class="garnish-cropped-input" value="">
                <input type="hidden" name="garnish_existing_images[]" class="garnish-existing-input" value="${image ? image.replace('{{ asset('') }}', '') : ''}">
            </div>

            <!-- Campos -->
            <div class="flex-grow grid grid-cols-1 sm:grid-cols-12 gap-2.5 w-full">
                <div class="sm:col-span-5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Nombre Guarnición *</label>
                    <input type="text" name="garnish_names[]" value="${escapeHtml(name)}" placeholder="Ej: Papas Fritas" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Precio Extra ($)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold text-xs">$</span>
                        <input type="number" step="0.01" min="0" name="garnish_prices[]" value="${price !== '' ? price : '0'}" placeholder="0 (Incluida)" class="w-full pl-7 pr-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-black text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Breve Descripción</label>
                    <input type="text" name="garnish_descriptions[]" value="${escapeHtml(desc)}" placeholder="Ej: Doradas y crocantes" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Botón Eliminar con SweetAlert2 -->
            <button type="button" onclick="removeGarnishRow(this)" class="p-2.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition flex-shrink-0" title="Eliminar opción">
                <i class="fas fa-trash-alt text-sm"></i>
            </button>
        `;
        list.appendChild(row);
    }

    function removeGarnishRow(btn) {
        const row = btn.closest('.garnish-row');
        const nameInput = row.querySelector('input[name="garnish_names[]"]');
        const garnishName = nameInput && nameInput.value.trim() ? `"${nameInput.value.trim()}"` : 'esta guarnición';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Quitar guarnición?',
                text: `¿Estás seguro de quitar ${garnishName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl font-[Poppins]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    row.remove();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Guarnición eliminada',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        } else {
            if (confirm(`¿Quitar ${garnishName}?`)) {
                row.remove();
            }
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Cargar guarniciones existentes en edición
    document.addEventListener('DOMContentLoaded', function() {
        const existingGarnishes = @json($product->garnishes ?? []);
        if (existingGarnishes && existingGarnishes.length > 0) {
            existingGarnishes.forEach(g => {
                const imgUrl = g.image_path ? '{{ asset('') }}' + g.image_path : '';
                addGarnishRow(g.name, g.price, g.description, imgUrl);
            });
        }
    });

    // Manejo de Cropper.js (Plato principal y Guarniciones)
    let cropper = null;
    let currentCroppingContext = { type: 'dish' };
    const dishFileInput = document.getElementById('dish_image_input');
    const modal = document.getElementById('cropper-modal');
    const imageTarget = document.getElementById('cropper-image-target');

    function openCropperWithFile(file) {
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

    dishFileInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            currentCroppingContext = { type: 'dish' };
            openCropperWithFile(files[0]);
        }
    });

    function handleGarnishFileSelect(input) {
        const files = input.files;
        if (files && files.length > 0) {
            currentCroppingContext = {
                type: 'garnish',
                row: input.closest('.garnish-row')
            };
            openCropperWithFile(files[0]);
        }
    }

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

        if (currentCroppingContext.type === 'dish') {
            document.getElementById('cropped_image_base64').value = base64Data;
            document.getElementById('cropped-preview-img').src = base64Data;
            document.getElementById('cropped-preview-container').classList.remove('hidden');
            document.getElementById('cropped-preview-container').classList.add('flex');
        } else if (currentCroppingContext.type === 'garnish' && currentCroppingContext.row) {
            const row = currentCroppingContext.row;
            row.querySelector('.garnish-cropped-input').value = base64Data;
            const img = row.querySelector('.garnish-preview-img');
            img.src = base64Data;
            img.classList.remove('hidden');
            const iconPlaceholder = row.querySelector('.garnish-icon-placeholder');
            if (iconPlaceholder) iconPlaceholder.classList.add('hidden');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '¡Foto circular de guarnición aplicada!',
                    showConfirmButton: false,
                    timer: 1800
                });
            }
        }

        closeCropperModal();
    }
</script>
@endpush
@endsection
