<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Rotisería La Abuela</title>

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

    <!-- Card Principal -->
    <div class="max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 sm:p-10 border border-white/40 relative overflow-hidden">

        <!-- Decoración de fondo -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-red-100 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-purple-100 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Encabezado con Logo -->
        <div class="text-center relative z-10 mb-6">
            <a href="{{ route('home') }}" class="inline-block group mb-3">
                <img src="{{ asset('imagenes/logo.jpg') }}" alt="Logo La Abuela" class="w-20 h-20 rounded-full mx-auto shadow-lg border-4 border-white group-hover:scale-105 transition-transform duration-200 object-cover">
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-800 tracking-tight">Nueva Contraseña</h1>
            <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">
                Ingresa tu nueva contraseña para acceder al panel.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm font-semibold flex items-center space-x-2">
                <i class="fas fa-exclamation-triangle text-base flex-shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4 relative z-10">
            @csrf

            <!-- Token oculto -->
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Campo Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Correo Electrónico
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus
                           placeholder="tu-correo@laabuela.com"
                           class="w-full pl-11 pr-4 py-3 bg-gray-50/80 border border-gray-200 rounded-2xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition-all">
                </div>
            </div>

            <!-- Campo Nueva Contraseña -->
            <div>
                <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Nueva Contraseña
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                           placeholder="Mínimo 6 caracteres"
                           class="w-full pl-11 pr-11 py-3 bg-gray-50/80 border border-gray-200 rounded-2xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition-all">
                    <button type="button" onclick="toggleVisibility('password', 'toggle-icon-1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="toggle-icon-1"></i>
                    </button>
                </div>
            </div>

            <!-- Campo Confirmar Contraseña -->
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Confirmar Contraseña
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-400">
                        <i class="fas fa-check-double"></i>
                    </span>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           placeholder="Repite la nueva contraseña"
                           class="w-full pl-11 pr-11 py-3 bg-gray-50/80 border border-gray-200 rounded-2xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition-all">
                    <button type="button" onclick="toggleVisibility('password_confirmation', 'toggle-icon-2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="toggle-icon-2"></i>
                    </button>
                </div>
            </div>

            <!-- Botón de Envío -->
            <button type="submit"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-black text-sm sm:text-base shadow-xl shadow-red-500/30 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2 cursor-pointer mt-2">
                <i class="fas fa-key text-base"></i>
                <span>Guardar Nueva Contraseña</span>
            </button>
        </form>

        <!-- Botón Volver al Login -->
        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <a href="{{ route('login') }}" class="inline-flex items-center space-x-2 text-xs sm:text-sm font-bold text-gray-600 hover:text-red-600 transition">
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Volver a Iniciar Sesión</span>
            </a>
        </div>
    </div>

    <script>
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
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
