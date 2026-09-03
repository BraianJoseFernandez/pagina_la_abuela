<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Rotisería La Abuela</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Kalam:wght@700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" class="rounded-full" href="{{ asset('imagenes/logo.jpg') }}" type="image/x-icon">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .script-title { font-family: 'Kalam', cursive; }
    </style>
</head>

<body class="bg-gradient-to-br from-red-600 via-rose-600 to-purple-800 min-h-screen flex items-center justify-center p-4">

    <!-- Card Principal de Login -->
    <div class="max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/40 relative overflow-hidden">

        <!-- Decoración de fondo -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-red-100 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-purple-100 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Encabezado con Logo -->
        <div class="text-center relative z-10 mb-8">
            <a href="{{ route('home') }}" class="inline-block group mb-3">
                <img src="{{ asset('imagenes/logo.jpg') }}" alt="Logo La Abuela" class="w-20 h-20 rounded-full mx-auto shadow-lg border-4 border-white group-hover:scale-105 transition-transform duration-200 object-cover">
            </a>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Acceso al Sistema</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Panel de Administradores y Personal</p>
        </div>

        <!-- Alertas de sesión -->
        @if(session('success'))
            <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center space-x-2">
                <i class="fas fa-check-circle text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold flex items-center space-x-2">
                <i class="fas fa-exclamation-triangle text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-5 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 text-sm font-semibold flex items-center space-x-2">
                <i class="fas fa-info-circle text-base"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5 relative z-10">
            @csrf

            <!-- Campo Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Correo Electrónico
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="ejemplo@laabuela.com"
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50/80 border border-gray-200 rounded-2xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition-all @error('email') border-red-400 @enderror">
                </div>
                @error('email')
                    <p class="text-xs text-red-600 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campo Contraseña -->
            <div>
                <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Contraseña
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••"
                           class="w-full pl-11 pr-11 py-3.5 bg-gray-50/80 border border-gray-200 rounded-2xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition-all @error('password') border-red-400 @enderror">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="password-toggle-icon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-600 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Recordarme y Olvidé mi Contraseña -->
            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 text-xs font-semibold text-gray-600 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <span>Recordarme</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-xs font-bold text-red-600 hover:text-red-700 hover:underline transition">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <!-- Botón de Ingreso -->
            <button type="submit"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-black text-base shadow-xl shadow-red-500/30 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2 cursor-pointer">
                <i class="fas fa-arrow-right-to-bracket text-lg"></i>
                <span>Ingresar al Panel</span>
            </button>
        </form>

        <!-- Botón Volver al Menú -->
        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 text-sm font-bold text-gray-600 hover:text-red-600 transition">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Volver a la Carta Pública</span>
            </a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>
