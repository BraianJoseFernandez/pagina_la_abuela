@extends('layouts.admin')

@section('title', 'Datos del Negocio')
@section('page-title', 'Configuración General del Negocio')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <div class="border-b border-slate-100 pb-5 mb-6">
            <h3 class="text-xl font-black text-slate-800">Información de Contacto y Enlaces</h3>
            <p class="text-xs text-slate-500 mt-1">Configura los datos que se muestran en el encabezado, footer y enlaces de WhatsApp de la carta.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nombre y Eslogan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nombre del Restaurante *
                    </label>
                    <input type="text" name="restaurant_name" value="{{ old('restaurant_name', $settings['restaurant_name'] ?? 'Rotisería La Abuela') }}" required
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Eslogan / Frase del Encabezado
                    </label>
                    <input type="text" name="restaurant_slogan" value="{{ old('restaurant_slogan', $settings['restaurant_slogan'] ?? 'Cocinar con amor te alimenta el alma') }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- WhatsApp y Teléfono -->
            <div class="border-t border-slate-100 pt-6">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-4 flex items-center space-x-2">
                    <i class="fab fa-whatsapp text-emerald-500 text-lg"></i>
                    <span>WhatsApp y Teléfonos de Recepción</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Número de WhatsApp para Pedidos (con código de país sin + ni guiones) *
                        </label>
                        <input type="text" name="whatsapp_phone" value="{{ old('whatsapp_phone', $settings['whatsapp_phone'] ?? '5493794565528') }}" required
                               placeholder="5493794565528"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                        <span class="text-[11px] text-slate-400 mt-1 block">A este número llegarán todos los pedidos armados por los clientes.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Teléfono Visible en el Encabezado
                        </label>
                        <input type="text" name="display_phone" value="{{ old('display_phone', $settings['display_phone'] ?? '3794-565528') }}"
                               placeholder="3794-565528"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <!-- Dirección y Google Maps -->
            <div class="border-t border-slate-100 pt-6">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-4 flex items-center space-x-2">
                    <i class="fas fa-map-marker-alt text-yellow-500 text-lg"></i>
                    <span>Ubicación y Dirección</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Dirección del Local *
                        </label>
                        <input type="text" name="address" value="{{ old('address', $settings['address'] ?? 'Av. libertad 5445') }}" required
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Enlace de Google Maps
                        </label>
                        <input type="text" name="maps_url" value="{{ old('maps_url', $settings['maps_url'] ?? 'https://maps.app.goo.gl/JAgMpxXPBgX4BGqbA?g_st=aw') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <!-- Instagram -->
            <div class="border-t border-slate-100 pt-6">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-4 flex items-center space-x-2">
                    <i class="fab fa-instagram text-pink-500 text-lg"></i>
                    <span>Redes Sociales (Instagram)</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Usuario de Instagram
                        </label>
                        <input type="text" name="instagram_user" value="{{ old('instagram_user', $settings['instagram_user'] ?? '@RotiLaAbuela') }}"
                               placeholder="@RotiLaAbuela"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Enlace Directo de Instagram
                        </label>
                        <input type="text" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? 'https://www.instagram.com/rotilaabuela') }}"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="submit" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-lg shadow-red-500/20 hover:from-red-700 hover:to-rose-700 transition">
                    Guardar Cambios de Configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
