<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Rotisería La Abuela</title>

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
            <h1 class="text-2xl sm:text-3xl font-black text-gray-800 tracking-tight">Recuperar Acceso</h1>
            <p class="text-xs sm:text-sm text-gray-500 font-medium mt-1">
                Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
            </p>
        </div>

        <!-- Alertas de estado -->
        @if(session('status'))
            <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-start space-x-2.5">
                <i class="fas fa-check-circle text-emerald-600 mt-0.5 text-base flex-shrink-0"></i>
                <span class="leading-relaxed">{{ session('status') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm font-semibold flex items-center space-x-2">
                <i class="fas fa-exclamation-triangle text-base flex-shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('password.email') }}" class="space-y-5 relative z-10">
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
                           placeholder="tu-correo@laabuela.com"
                           class="w-full pl-11 pr-4 py-3.5 bg-gray-50/80 border border-gray-200 rounded-2xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition-all @error('email') border-red-400 @enderror">
                </div>
                @error('email')
                    <p class="text-xs text-red-600 mt-1.5 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botón de Envío -->
            <button type="submit"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-black text-sm sm:text-base shadow-xl shadow-red-500/30 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2 cursor-pointer">
                <i class="fas fa-paper-plane text-base"></i>
                <span>Enviar Enlace por Email</span>
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

</body>
</html>
