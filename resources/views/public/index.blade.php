@extends('layouts.app')

@section('title', 'Rotisería La Abuela - Carta Online')

@section('content')
    <!-- HEADER HERO RETRO -->
    <header class="hero-bg text-white relative">
        <!-- Floating Animated Icons -->
        <div class="floating-icons">
            <i class="floating-icon fas fa-pizza-slice" style="top: 10%; left: 8%; animation-delay: 0s"></i>
            <i class="floating-icon fas fa-hamburger" style="top: 30%; right: 10%; animation-delay: 1.5s"></i>
            <i class="floating-icon fas fa-bowl-food" style="top: 60%; left: 14%; animation-delay: 3s"></i>
            <i class="floating-icon fas fa-cookie-bite" style="top: 20%; right: 24%; animation-delay: 4.5s"></i>
            <i class="floating-icon fas fa-egg" style="top: 70%; right: 16%; animation-delay: 2s"></i>
        </div>

        <div class="logo-container container mx-auto px-4 text-center relative z-10">
            <!-- 1. LOGO PRINCIPAL (Lo primero que se ve al cargar la página) -->
            <div class="pt-4 pb-2 animate-on-load slide-in-top flex justify-center items-center relative" style="animation-duration: 0.7s; animation-delay: 0.1s;">
                <img src="{{ asset('imagenes/logo.jpg') }}" alt="Logo Rotisería La Abuela"
                     class="hero-logo-img mx-auto" />
            </div>

            <!-- 2. Títulos Retro -->
            <div class="text-4xl sm:text-5xl md:text-6xl mb-1 retro-title animate-on-load slide-in-left tracking-wide"
                 style="animation-duration: 0.8s; animation-delay: 0.3s;">
                Rotiseria
            </div>

            <h1 class="text-5xl sm:text-6xl md:text-7xl font-black mb-3 tracking-tight script-title animate-on-load slide-in-right"
                style="animation-duration: 0.8s; animation-delay: 0.5s;">
                La Abuela
            </h1>

            <p class="text-base sm:text-xl opacity-95 font-light mb-6 animate-on-load slide-in-bottom tracking-wide max-w-lg mx-auto"
               style="animation-duration: 0.8s; animation-delay: 0.7s;">
                {{ $settings['restaurant_slogan'] ?? 'Cocinar con amor te alimenta el alma' }}
            </p>

            <!-- 3. SECCIÓN DINÁMICA DE EVENTOS Y PROMOCIONES (Configurable desde Admin) -->
            @if(isset($event) && $event->is_active)
                <div class="mb-4 sm:mb-5 animate-on-load fade-in flex justify-center items-center gap-3 sm:gap-6 px-2"
                     style="animation-duration: 0.8s; animation-delay: 0.8s;">
                    <!-- Badge Izquierdo -->
                    <div class="animate-bounce flex flex-col items-center select-none" style="animation-duration: 2s; animation-delay: 0s;">
                        <span class="text-2xl sm:text-4xl">{{ $event->badge_left_emoji ?? '⚽🇦🇷' }}</span>
                    </div>

                    <!-- Banner de Evento Clickeable (muestra el flyer completo sin recortes) -->
                    @php $eventImageUrl = asset($event->image_path ?: 'imagenes/eventos/mundial/oferta_mundial.jpeg'); @endphp
                    <button type="button"
                            class="event-flyer-card group relative block overflow-hidden rounded-3xl shadow-xl max-w-md w-full border-4 border-yellow-300/80 transition-all duration-300 hover:scale-[1.03] hover:shadow-yellow-500/30 bg-gray-950 cursor-pointer text-white"
                            onclick="showEventAlertDynamic(window.EVENT_DATA)"
                            aria-label="Ver promoción: {{ $event->title }}">
                        <!-- Flyer completo sobre un fondo difuminado del mismo flyer -->
                        <span class="relative block overflow-hidden py-2.5">
                            <span class="absolute inset-0 bg-center bg-cover scale-125 blur-2xl opacity-80" style="background-image: url('{{ $eventImageUrl }}')" aria-hidden="true"></span>
                            <span class="absolute inset-0 bg-black/25" aria-hidden="true"></span>
                            <img src="{{ $eventImageUrl }}"
                                 alt="{{ $event->title }}"
                                 class="relative z-[1] w-auto max-w-full h-56 sm:h-72 object-contain mx-auto block drop-shadow-2xl group-hover:brightness-105 transition-all">
                            <span class="absolute top-2.5 right-2.5 z-[2] inline-flex items-center gap-1 bg-white/90 text-gray-900 text-[10px] sm:text-xs font-black px-2.5 py-1 rounded-full shadow uppercase tracking-wider">
                                <i class="fas fa-expand text-[10px]"></i> Ver flyer
                            </span>
                        </span>

                        <!-- Título debajo del flyer (no tapa el diseño) -->
                        <span class="relative block bg-gradient-to-b from-gray-900 to-black px-3 py-2.5 text-center border-t border-yellow-300/40">
                            <span class="inline-block bg-yellow-400 text-gray-900 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider shadow">
                                <i class="fas fa-star text-xs mr-1"></i> {{ $event->title }}
                            </span>
                            @if($event->subtitle)
                                <span class="block text-xs sm:text-sm text-gray-200 mt-1 font-medium">{{ $event->subtitle }}</span>
                            @endif
                        </span>
                    </button>

                    <!-- Badge Derecho -->
                    <div class="animate-bounce flex flex-col items-center select-none" style="animation-duration: 2s; animation-delay: 1s;">
                        <span class="text-2xl sm:text-4xl">{{ $event->badge_right_emoji ?? '⚽🇦🇷' }}</span>
                    </div>
                </div>
            @endif

            <!-- 4. SECCIÓN CONTACTOS Y ENLACES DIRECTOS -->
            <div class="flex flex-wrap justify-center gap-2.5 sm:gap-3 relative z-[20] mt-1">
                <!-- Teléfono / WhatsApp -->
                <a href="https://api.whatsapp.com/send?phone={{ $settings['whatsapp_phone'] ?? '5493794565528' }}&text=¡Hola!%20Quiero%20hacer%20un%20pedido%20👵🍕"
                   target="_blank"
                   class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 text-white px-4 sm:px-5 py-2 rounded-full backdrop-blur-md transition-all duration-200 hover:scale-105 border border-white/20 shadow-md text-sm sm:text-base font-semibold">
                    <i class="fas fa-phone text-yellow-300 phone-icon-animated"></i>
                    <span>{{ $settings['display_phone'] ?? '3794-565528' }}</span>
                </a>

                <!-- Dirección / Maps -->
                <a href="{{ $settings['maps_url'] ?? 'https://maps.app.goo.gl/JAgMpxXPBgX4BGqbA?g_st=aw' }}"
                   target="_blank"
                   class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 text-white px-4 sm:px-5 py-2 rounded-full backdrop-blur-md transition-all duration-200 hover:scale-105 border border-white/20 shadow-md text-sm sm:text-base font-semibold">
                    <i class="fas fa-map-marker-alt text-yellow-300 location-icon-animated"></i>
                    <span>{{ $settings['address'] ?? 'Av. libertad 5445' }}</span>
                </a>

                <!-- Instagram -->
                <a href="{{ $settings['instagram_url'] ?? 'https://www.instagram.com/rotilaabuela' }}"
                   target="_blank"
                   class="flex items-center space-x-2 bg-white/20 hover:bg-white/30 text-white px-4 sm:px-5 py-2 rounded-full backdrop-blur-md transition-all duration-200 hover:scale-105 border border-white/20 shadow-md text-sm sm:text-base font-semibold">
                    <i class="fab fa-instagram text-yellow-300 instagram-icon-animated"></i>
                    <span>{{ $settings['instagram_user'] ?? '@RotiLaAbuela' }}</span>
                </a>
            </div>
        </div>
    </header>

    <!-- NAVEGACIÓN DE CATEGORÍAS (Formato píldoras con distribución elegante) -->
    <div class="menu-categories-wrapper py-3.5 px-3 relative md:sticky md:top-0 z-30 shadow-sm">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3" id="categories-tabs-nav">
                @foreach($categories as $category)
                    <button onclick="loadCategoryDynamic('{{ $category->slug }}', false, true)"
                            data-slug="{{ $category->slug }}"
                            class="category-btn {{ $loop->first ? 'active' : '' }}">
                        <x-category-icon :icon="$category->icon ?? 'fas fa-utensils'" class="w-5 h-5 mr-1.5" />
                        <span>{{ $category->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL DEL MENÚ (Carga dinámica vía GSAP & AJAX) -->
    <main class="flex-grow max-w-5xl mx-auto px-4 py-6 w-full relative z-10 scroll-mt-28" id="menu-content">
        <div id="menu-sections-container" class="min-h-[400px]">
            <!-- Contenido cargado dinámicamente -->
            <div class="flex items-center justify-center py-20 text-purple-600">
                <i class="fas fa-spinner fa-spin text-4xl mr-3"></i>
                <span class="text-xl font-semibold">Cargando menú delicioso...</span>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    window.APP_CONFIG = {
        whatsappPhone: "{{ $settings['whatsapp_phone'] ?? '5493794565528' }}",
        restaurantName: "{{ $settings['restaurant_name'] ?? 'Rotisería La Abuela' }}",
        initialCategorySlug: "{{ $categories->first()?->slug ?? 'pizzas' }}",
        categoryRouteUrl: "{{ url('/categoria') }}",
        orderSaveUrl: "{{ route('order.save') }}",
        csrfToken: "{{ csrf_token() }}",
        googleMapsApiKey: "{{ $settings['google_maps_api_key'] ?? env('GOOGLE_MAPS_API_KEY', '') }}",
        resolveMapsUrl: "{{ route('maps.resolve') }}"
    };

    // Datos del evento / flyer activo (null si la sección está apagada)
    window.EVENT_DATA = @js(isset($event) && $event->is_active ? $event->toPublicPayload() : null);
</script>
@endpush
