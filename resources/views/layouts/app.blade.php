<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rotisería La Abuela - Carta del Restaurante')</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Kalam:wght@400;700&family=Caveat:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Swiper.js for photo carousels -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- SweetAlert2 & Confetti & GSAP -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('style.css') }}" />
    <link rel="shortcut icon" class="rounded-full" href="{{ asset('imagenes/logo.jpg') }}" type="image/x-icon">

    <style>
        /* Estilos complementarios para Carrito y Animaciones */
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .cart-badge {
            animation: pulse-badge 1.5s infinite;
        }
        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        .floating-cart-btn {
            box-shadow: 0 10px 30px -5px rgba(220, 38, 38, 0.6), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .floating-cart-btn:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 14px 35px -5px rgba(220, 38, 38, 0.75);
        }
        .swiper-slide {
            width: auto !important;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-purple-50 via-white to-pink-50 font-sans text-gray-800 antialiased selection:bg-red-500 selection:text-white min-h-screen flex flex-col">

    <!-- Overlay de Transición GSAP (Curva animada) -->
    <div id="transition-overlay" class="fixed inset-0 z-[100] hidden pointer-events-none">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <path id="curve-path" fill="#E14640" d="M 0 0 L 100 0 L 100 100 L 0 100 Z" />
        </svg>
    </div>

    <!-- Botón de Acceso Administrativo Superior (Formato exacto Imagen 2) -->
    <div class="absolute top-3 right-3 sm:top-4 sm:right-4 z-40">
        @auth
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center space-x-2 bg-white hover:bg-gray-100 text-gray-800 px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow-md transition-all duration-200 hover:scale-105 border border-gray-200/80">
                <i class="fas fa-user-shield text-red-600"></i>
                <span>Panel ({{ Auth::user()->name }})</span>
            </a>
        @else
            <a href="{{ route('login') }}"
               title="Acceso Administrador"
               class="inline-flex items-center space-x-2 bg-white hover:bg-gray-100 text-gray-800 hover:text-red-600 px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow-md transition-all duration-200 hover:scale-105 border border-gray-200/80">
                <i class="fas fa-user-circle text-red-600 text-base"></i>
                <span>Panel (Administrador)</span>
            </a>
        @endauth
    </div>

    <!-- Contenido Principal -->
    <div class="flex-grow">
        @yield('content')
    </div>

    <!-- Modal del Carrito de Pedido -->
    @include('public.partials.cart_modal')

    <!-- Modal Selector de Variantes / Tamaños -->
    <div id="variant-modal" class="fixed inset-0 z-[9999] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 duration-300" id="variant-modal-card">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 id="variant-modal-title" class="text-2xl font-bold text-gray-800">Seleccionar Opción</h3>
                    <p id="variant-modal-desc" class="text-sm text-gray-500 mt-1"></p>
                </div>
                <button onclick="closeVariantModal()" class="text-gray-400 hover:text-gray-600 w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-100 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div id="variant-options-container" class="space-y-3 my-4 max-h-60 overflow-y-auto pr-1">
                <!-- Opciones dinámicas -->
            </div>

            <div class="mt-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Notas especiales (opcional):</label>
                <input type="text" id="variant-item-notes" placeholder="Ej: sin cebolla, bien cocido..." class="w-full text-sm px-3.5 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition">
            </div>

            <div class="mt-6 flex space-x-3">
                <button onclick="closeVariantModal()" class="w-1/3 py-3 px-4 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button id="variant-add-confirm-btn" class="w-2/3 py-3 px-4 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-lg shadow-red-500/30 hover:from-red-700 hover:to-rose-700 transition flex items-center justify-center space-x-2">
                    <i class="fas fa-cart-plus"></i>
                    <span>Añadir al Pedido</span>
                </button>
            </div>
        </div>
    </div>

    <!-- BOTÓN FLOTANTE DEL CARRITO (Con z-index máximo para quedar siempre por encima de todo) -->
    <div id="floating-cart-container" class="fixed bottom-6 right-6 z-[9990] pointer-events-auto">
        <button onclick="openCartModal()"
                id="floating-cart-button"
                class="floating-cart-btn bg-gradient-to-r from-red-600 via-rose-600 to-red-600 text-white px-5 py-3.5 rounded-full flex items-center space-x-3 font-black text-base shadow-2xl relative cursor-pointer border-2 border-white/60 group">
            <div class="relative">
                <i class="fas fa-shopping-basket text-xl group-hover:scale-110 transition-transform"></i>
                <span id="floating-cart-count" class="cart-badge absolute -top-3 -right-3 bg-yellow-400 text-gray-900 font-black text-xs w-6 h-6 rounded-full flex items-center justify-center shadow-md border-2 border-white">
                    0
                </span>
            </div>
            <span class="font-bold text-sm sm:text-base">Ver Mi Pedido</span>
            <span id="floating-cart-total" class="bg-black/25 px-2.5 py-1 rounded-full text-xs font-black text-yellow-300">
                $0
            </span>
        </button>
    </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white py-10 px-4 mt-16 border-t border-gray-700/50">
        <div class="container mx-auto max-w-5xl text-center space-y-4">
            <div class="flex items-center justify-center space-x-2">
                <img src="{{ asset('imagenes/logo.jpg') }}" alt="Logo" class="w-12 h-12 rounded-full border-2 border-yellow-400 shadow-md">
                <span class="font-black text-2xl tracking-wide text-yellow-400">Rotisería La Abuela</span>
            </div>
            <p class="text-gray-300 text-sm italic">"Cocinar con amor te alimenta el alma"</p>
            <div class="flex flex-wrap justify-center gap-6 text-sm text-gray-400 pt-2">
                <span><i class="fas fa-map-marker-alt text-yellow-400 mr-1.5"></i> {{ $settings['address'] ?? 'Av. libertad 5445' }}</span>
                <span><i class="fab fa-whatsapp text-emerald-400 mr-1.5"></i> {{ $settings['display_phone'] ?? '3794-565528' }}</span>
                <span><i class="fab fa-instagram text-pink-400 mr-1.5"></i> {{ $settings['instagram_user'] ?? '@RotiLaAbuela' }}</span>
            </div>
            <div class="pt-4 border-t border-gray-800 text-xs text-gray-500">
                © {{ date('Y') }} Rotisería La Abuela. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- Scripts de la aplicación -->
    <script src="{{ asset('js/cart.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>

    @stack('scripts')
</body>

</html>
