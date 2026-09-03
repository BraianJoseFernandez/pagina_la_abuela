<section id="category-section-{{ $category->slug }}" class="menu-category animate-fade-in">
    <!-- Encabezado de la Categoría -->
    <div class="text-center mb-6">
        <div class="category-icon text-4xl sm:text-5xl text-purple-700 mb-1 flex justify-center items-center">
            <x-category-icon :icon="$category->icon ?? 'fas fa-utensils'" class="w-12 h-12 inline-block text-purple-700" />
        </div>
        <h2 class="text-3xl sm:text-4xl font-black text-gray-800 tracking-tight section-title mb-1">
            {{ $category->name }}
        </h2>
        @if($category->subtitle)
            <p class="text-gray-500 text-base sm:text-lg font-medium max-w-lg mx-auto">
                {{ $category->subtitle }}
            </p>
        @endif
    </div>

    <!-- Carrusel de Fotos de la Categoría (Swiper.js con miniaturas circulares limpias) -->
    @if($category->images->isNotEmpty())
        <div class="relative w-full max-w-3xl mx-auto rounded-3xl p-4 sm:p-6 mb-8 bg-white/70 backdrop-blur-md border border-purple-100 shadow-sm">
            <div class="swiper category-photos-swiper">
                <div class="swiper-wrapper py-2">
                    @foreach($category->images as $img)
                        <div class="swiper-slide !w-auto px-2">
                            <div class="category-thumb-wrapper group"
                                 onclick="showImageSweetAlert('{{ asset($img->image_path) }}', '{{ addslashes($img->alt_text ?: $category->name) }}')">
                                <div class="relative w-28 h-28 sm:w-36 sm:h-36 rounded-full overflow-hidden border-4 border-purple-300 group-hover:border-purple-600 transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:scale-105">
                                    <img src="{{ asset($img->image_path) }}"
                                         alt="{{ $img->alt_text ?: $category->name }}"
                                         class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-full">
                                        <i class="fas fa-search-plus text-white text-lg sm:text-xl"></i>
                                    </div>
                                </div>
                                @if($img->alt_text)
                                    <span class="text-xs font-bold text-gray-700 mt-2 max-w-[120px] truncate text-center block">
                                        {{ $img->alt_text }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="text-center text-xs text-purple-600 mt-2 flex items-center justify-center space-x-1 font-medium">
                <i class="fas fa-hand-pointer text-purple-500"></i>
                <span>Desliza para ver más fotos o toca para ampliar</span>
            </div>
        </div>
    @endif

    <!-- Listado de Platos / Productos de la Categoría -->
    <div class="space-y-4">
        @forelse($category->activeProducts as $product)
            <div class="menu-card rounded-2xl shadow-sm hover:shadow-md overflow-hidden relative border border-purple-100/60 bg-white/90 backdrop-blur-md transition-all duration-200 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ !$product->is_available ? 'opacity-60 grayscale' : '' }}">

                <!-- Badge Especial (ej: ⭐ La Abuela) -->
                @if($product->badge)
                    <div class="menu-item-badge">
                        {{ $product->badge }}
                    </div>
                @endif

                <!-- Información del Producto -->
                <div class="flex-grow pr-2">
                    <div class="flex items-center space-x-2 mb-1">
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">
                            {{ $product->name }}
                        </h3>
                    </div>

                    @if($product->description)
                        <p class="text-gray-600 text-sm leading-relaxed mb-3">
                            {{ $product->description }}
                        </p>
                    @endif

                    <!-- Precios / Variantes -->
                    <div class="flex flex-wrap gap-2 items-center">
                        @if($product->has_cooking_options)
                            <span class="text-xs font-bold text-amber-800 bg-amber-50 border border-amber-300/90 px-2.5 py-1 rounded-full shadow-2xs inline-flex items-center space-x-1">
                                <i class="fas fa-fire-burner text-amber-600 text-[11px]"></i>
                                <span>🔥 Horno o Frita</span>
                            </span>
                        @endif

                        @if($product->variants->isNotEmpty())
                            @foreach($product->variants as $variant)
                                <span class="price-tag text-xs sm:text-sm font-bold bg-purple-700 text-white px-3 py-1 rounded-full shadow-xs">
                                    {{ $variant->name }}: ${{ number_format($variant->price, 0, ',', '.') }}
                                </span>
                            @endforeach
                        @elseif($product->price !== null)
                            <span class="price-tag text-sm sm:text-base font-bold bg-purple-700 text-white px-3.5 py-1 rounded-full shadow-xs">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Botón de Agregar al Carrito -->
                <div class="flex-shrink-0 flex items-center justify-end sm:justify-center">
                    @if($product->is_available)
                        <button onclick="handleAddToCartClick(@js($product))"
                                class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold text-sm shadow-md shadow-red-500/25 transform hover:scale-105 active:scale-95 transition-all duration-200 flex items-center justify-center space-x-2 cursor-pointer">
                            <i class="fas fa-cart-plus text-base"></i>
                            <span>Agregar</span>
                        </button>
                    @else
                        <span class="px-4 py-2 rounded-xl bg-gray-100 text-gray-500 font-bold text-xs uppercase tracking-wider">
                            Agotado por hoy
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white/60 rounded-3xl border border-gray-100 p-8">
                <i class="fas fa-utensils text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 text-base font-medium">No hay platos disponibles en esta categoría actualmente.</p>
            </div>
        @endforelse
    </div>
</section>
