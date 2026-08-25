<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Administración') - Rotisería La Abuela</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Fredoka+One&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Cropper.js (Para recortar y agrandar imágenes en círculo/redondeadas) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

    <!-- SortableJS (Para reordenar por Drag and Drop categorías, platos y fotos) -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <link rel="shortcut icon" class="rounded-full" href="{{ asset('imagenes/logo.jpg') }}" type="image/x-icon">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex">

    <!-- Overlay móvil para sidebar -->
    <div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="admin-sidebar" class="fixed top-0 left-0 bottom-0 z-40 w-72 bg-gradient-to-b from-gray-900 via-gray-900 to-slate-950 text-white flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 shadow-2xl border-r border-gray-800">
        <!-- Logo & Header -->
        <div class="p-6 border-b border-gray-800 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('imagenes/logo.jpg') }}" alt="Logo" class="w-11 h-11 rounded-full border-2 border-yellow-400 shadow-md group-hover:scale-105 transition-transform object-cover">
                <div>
                    <span class="font-black text-lg text-white tracking-wide block leading-tight">La Abuela</span>
                    <span class="text-[11px] font-semibold text-yellow-400 uppercase tracking-wider">Panel de Gestión</span>
                </div>
            </a>
            <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white p-1">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Perfil del Usuario Activo -->
        <div class="px-6 py-4 bg-gray-800/40 border-b border-gray-800/80 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-red-500 to-rose-600 flex items-center justify-center text-white font-bold shadow-md">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-grow overflow-hidden">
                <h4 class="text-sm font-bold text-gray-200 truncate">{{ Auth::user()->name ?? 'Usuario' }}</h4>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ Auth::user()->isAdmin() ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                    {{ Auth::user()->isAdmin() ? 'Administrador' : 'Personal' }}
                </span>
            </div>
        </div>

        <!-- Menú de Navegación -->
        <nav class="flex-grow p-4 space-y-1.5 overflow-y-auto">
            <div class="text-[11px] font-black uppercase text-gray-500 px-3 pt-2 pb-1 tracking-wider">Principal</div>

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg shadow-red-600/30' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white' }}">
                <i class="fas fa-chart-pie w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <div class="text-[11px] font-black uppercase text-gray-500 px-3 pt-3 pb-1 tracking-wider">Gestión de la Carta</div>

            <a href="{{ route('admin.products.index') }}"
               class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.products.*') ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg shadow-red-600/30' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white' }}">
                <i class="fas fa-utensils w-5 text-center"></i>
                <span>Platos y Precios</span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg shadow-red-600/30' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white' }}">
                <i class="fas fa-layer-group w-5 text-center"></i>
                <span>Categorías / Secciones</span>
            </a>

            <a href="{{ route('admin.events.index') }}"
               class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.events.*') ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg shadow-red-600/30' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white' }}">
                <i class="fas fa-bullhorn w-5 text-center"></i>
                <span>Sección Eventos y Promos</span>
            </a>

            <div class="text-[11px] font-black uppercase text-gray-500 px-3 pt-3 pb-1 tracking-wider">Ventas y Pedidos</div>

            <a href="{{ route('admin.orders.index') }}"
               class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg shadow-red-600/30' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white' }}">
                <i class="fab fa-whatsapp w-5 text-center text-emerald-400"></i>
                <span>Historial de Pedidos</span>
            </a>

            @if(Auth::user()->isAdmin())
                <div class="text-[11px] font-black uppercase text-gray-500 px-3 pt-3 pb-1 tracking-wider">Configuración</div>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg shadow-red-600/30' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white' }}">
                    <i class="fas fa-users-gear w-5 text-center"></i>
                    <span>Usuarios y Personal</span>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg shadow-red-600/30' : 'text-gray-300 hover:bg-gray-800/60 hover:text-white' }}">
                    <i class="fas fa-sliders w-5 text-center"></i>
                    <span>Datos del Negocio</span>
                </a>
            @endif
        </nav>

        <!-- Footer Sidebar -->
        <div class="p-4 border-t border-gray-800 space-y-2">
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center justify-center space-x-2 w-full py-2.5 px-3 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold transition">
                <i class="fas fa-external-link-alt text-xs"></i>
                <span>Ver Carta Pública</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center justify-center space-x-2 w-full py-2.5 px-3 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-bold transition">
                    <i class="fas fa-power-off text-xs"></i>
                    <span>Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="flex-grow md:ml-72 flex flex-col min-h-screen">
        <!-- Barra Superior -->
        <header class="bg-white border-b border-slate-200/80 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-600 hover:text-slate-900 p-1 rounded-lg">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">@yield('page-title', 'Panel de Control')</h2>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('home') }}" target="_blank"
                   class="hidden sm:inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-bold transition">
                    <i class="fas fa-utensils"></i>
                    <span>Carta en Vivo</span>
                </a>
            </div>
        </header>

        <!-- Cuerpo de la Página -->
        <main class="flex-grow p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            <!-- Mensajes Flash de Éxito / Error -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold shadow-xs">
                    <div class="flex items-center space-x-2 mb-1.5 font-bold">
                        <i class="fas fa-triangle-exclamation text-red-500"></i>
                        <span>Por favor corrige los siguientes errores:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs text-red-700 ml-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    </script>

    @stack('scripts')
</body>

</html>
