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

            <!-- Conexión de WhatsApp Automático (API Baileys) -->
            <div class="border-t border-slate-100 pt-6">
                <div class="bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/80 rounded-3xl p-5 sm:p-6 border border-emerald-200 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-sm flex-shrink-0">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center space-x-2">
                                    <span>WhatsApp Automático (Sin WhatsApp Web)</span>
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Conecta el WhatsApp del negocio una sola vez para enviar comandas y pedidos automáticamente en segundo plano.
                                </p>
                            </div>
                        </div>

                        <!-- Badge de estado -->
                        <div id="wa-status-badge" class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-600 flex-shrink-0">
                            <span class="w-2 h-2 rounded-full bg-slate-400 animate-pulse"></span>
                            <span id="wa-status-text">Verificando conexión...</span>
                        </div>
                    </div>

                    <!-- Contenedor QR para Escaneo -->
                    <div id="wa-qr-container" class="hidden p-5 bg-white rounded-2xl border border-emerald-200/90 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xs">
                        <div class="space-y-2.5 text-center sm:text-left">
                            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <i class="fas fa-qrcode text-xs text-amber-600"></i>
                                <span>Vincular Cuenta de WhatsApp</span>
                            </span>
                            <h5 class="text-sm font-black text-slate-800">Escanea este código QR desde tu celular</h5>
                            <ol class="text-xs text-slate-600 space-y-1.5 list-decimal list-inside leading-relaxed">
                                <li>Abre WhatsApp en tu teléfono celular.</li>
                                <li>Ve a <b>Ajustes / Configuración</b> o los 3 puntos y elige <b>Dispositivos vinculados</b>.</li>
                                <li>Toca en <b>Vincular un dispositivo</b> y apunta la cámara a este código QR.</li>
                            </ol>
                            <p class="text-[11px] text-emerald-700 font-semibold flex items-center justify-center sm:justify-start space-x-1.5 pt-1">
                                <i class="fas fa-sync-alt fa-spin text-[10px]"></i>
                                <span>El código QR se actualiza en tiempo real automáticamente.</span>
                            </p>
                        </div>
                        <div class="w-48 h-48 p-2.5 bg-white rounded-2xl border-2 border-emerald-400 shadow-md flex items-center justify-center flex-shrink-0">
                            <img id="wa-qr-image" src="" alt="Código QR WhatsApp" class="w-full h-full object-contain">
                        </div>
                    </div>

                    <!-- Contenedor cuando está Conectado -->
                    <div id="wa-connected-container" class="hidden p-4 bg-emerald-100/70 rounded-2xl border border-emerald-300 flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs flex-shrink-0">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-black text-emerald-950 block">¡WhatsApp Conectado y Listo!</span>
                                <span id="wa-connected-phone" class="text-xs text-emerald-800 font-mono truncate block">Sesión activa para envíos en segundo plano</span>
                            </div>
                        </div>
                        <button type="button" onclick="disconnectWhatsApp()" class="px-3.5 py-2 rounded-xl bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold transition shadow-xs flex-shrink-0 cursor-pointer">
                            <i class="fas fa-sign-out-alt mr-1"></i> Desvincular
                        </button>
                    </div>

                    <!-- Contenedor cuando el Microservicio está apagado -->
                    <div id="wa-offline-container" class="hidden p-4 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 flex items-center justify-between gap-3">
                        <div class="flex items-center space-x-2.5">
                            <i class="fas fa-exclamation-triangle text-amber-600 text-base flex-shrink-0"></i>
                            <span>El microservicio local de WhatsApp no está iniciado en el puerto 3001.</span>
                        </div>
                        <button type="button" onclick="checkWhatsAppStatus()" class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition cursor-pointer flex-shrink-0">
                            Reintentar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Motomandados / Cadetes de Entrega -->
            <div class="border-t border-slate-100 pt-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center space-x-2">
                            <i class="fas fa-motorcycle text-red-600 text-lg"></i>
                            <span>Motomandados / Cadetes de Entrega</span>
                        </h4>
                        <p class="text-xs text-slate-500 mt-1">
                            Carga todos los cadetes que necesites (sin límite). Puedes habilitar o deshabilitar cada uno según esté trabajando; solo los activos podrán recibir pedidos por WhatsApp.
                        </p>
                    </div>

                    <button type="button" onclick="addCadeteCard()"
                            class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition flex-shrink-0 cursor-pointer">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Agregar Cadete</span>
                    </button>
                </div>

                <div id="cadetes-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @php
                        $presetPalette = ['#059669', '#2563eb', '#7c3aed', '#ea580c', '#0891b2', '#db2777', '#d97706'];
                        $itemsToRender = count($cadetes) > 0 ? $cadetes : [
                            ['name' => '', 'phone' => '', 'color' => '#059669', 'is_active' => true]
                        ];
                    @endphp

                    @foreach($itemsToRender as $i => $cadete)
                        @php
                            $defaultColor = $presetPalette[$i % count($presetPalette)];
                            $currentColor = old("cadetes.$i.color", $cadete['color'] ?? $defaultColor);
                            $isActive = (bool)old("cadetes.$i.is_active", $cadete['is_active'] ?? true);
                        @endphp
                        <div class="cadete-card bg-slate-50/90 rounded-2xl p-5 border border-slate-200 relative space-y-4 hover:border-slate-300 transition shadow-2xs">
                            <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                                <div class="flex items-center space-x-2.5">
                                    <span class="cadete-number-badge w-7 h-7 rounded-xl flex items-center justify-center text-white text-xs font-black shadow-xs" style="background-color: {{ $currentColor }};">
                                        {{ $i + 1 }}
                                    </span>
                                    <div>
                                        <h5 class="cadete-card-title text-xs font-black text-slate-800 uppercase tracking-wide">Cadete {{ $i + 1 }}</h5>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <!-- Toggle Habilitar / Deshabilitar -->
                                    <label class="inline-flex items-center cursor-pointer space-x-1.5 select-none" title="Habilitar o deshabilitar cadete para recibir pedidos">
                                        <input type="checkbox" name="cadetes[{{ $i }}][is_active]" value="1" {{ $isActive ? 'checked' : '' }}
                                               class="sr-only peer cadete-status-checkbox" onchange="updateCadeteStatusText(this)">
                                        <div class="relative w-8 h-4 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600"></div>
                                        <span class="cadete-status-label text-[11px] font-black {{ $isActive ? 'text-emerald-700' : 'text-slate-400' }}">
                                            {{ $isActive ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </label>

                                    <!-- Botón Eliminar Cadete -->
                                    <button type="button" onclick="removeCadeteCard(this)" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition ml-1 cursor-pointer" title="Eliminar este repartidor">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Nombre del Cadete
                                    </label>
                                    <input type="text" name="cadetes[{{ $i }}][name]" value="{{ old("cadetes.$i.name", $cadete['name'] ?? '') }}"
                                           placeholder="Ej: Juan Pérez"
                                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Teléfono / WhatsApp
                                    </label>
                                    <input type="text" name="cadetes[{{ $i }}][phone]" value="{{ old("cadetes.$i.phone", $cadete['phone'] ?? '') }}"
                                           placeholder="Ej: 5493794123456 o 3794123456"
                                           class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                    <span class="text-[10px] text-slate-400 mt-1 block">Número para el botón de WhatsApp</span>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Color Distintivo del Botón
                                    </label>
                                    <div class="flex items-center space-x-2.5">
                                        <input type="color" name="cadetes[{{ $i }}][color]" value="{{ $currentColor }}"
                                               onchange="this.closest('.cadete-card').querySelector('.cadete-hex-label').innerText = this.value; this.closest('.cadete-card').querySelector('.cadete-number-badge').style.backgroundColor = this.value;"
                                               class="cadete-color-input w-9 h-8 p-0.5 rounded-lg border border-slate-300 cursor-pointer bg-white">
                                        <span class="cadete-hex-label text-[11px] font-mono text-slate-600 font-bold uppercase">{{ $currentColor }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 block">Para diferenciar su botón en el pedido</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="submit" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-lg shadow-red-500/20 hover:from-red-700 hover:to-rose-700 transition cursor-pointer">
                    Guardar Cambios de Configuración
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const CADETE_PALETTE = ['#059669', '#2563eb', '#7c3aed', '#ea580c', '#0891b2', '#db2777', '#d97706', '#16a34a', '#4f46e5', '#c026d3'];

    function updateCadeteStatusText(checkbox) {
        const label = checkbox.closest('label').querySelector('.cadete-status-label');
        if (checkbox.checked) {
            label.innerText = 'Activo';
            label.classList.remove('text-slate-400');
            label.classList.add('text-emerald-700');
        } else {
            label.innerText = 'Inactivo';
            label.classList.remove('text-emerald-700');
            label.classList.add('text-slate-400');
        }
    }

    function reindexCadetes() {
        const cards = document.querySelectorAll('#cadetes-container .cadete-card');
        cards.forEach((card, idx) => {
            const num = idx + 1;
            const badge = card.querySelector('.cadete-number-badge');
            const title = card.querySelector('.cadete-card-title');
            if (badge) badge.innerText = num;
            if (title) title.innerText = `Cadete ${num}`;

            const nameInput = card.querySelector('input[name*="[name]"]');
            const phoneInput = card.querySelector('input[name*="[phone]"]');
            const colorInput = card.querySelector('input[name*="[color]"]');
            const activeInput = card.querySelector('input[name*="[is_active]"]');

            if (nameInput) nameInput.name = `cadetes[${idx}][name]`;
            if (phoneInput) phoneInput.name = `cadetes[${idx}][phone]`;
            if (colorInput) colorInput.name = `cadetes[${idx}][color]`;
            if (activeInput) activeInput.name = `cadetes[${idx}][is_active]`;
        });
    }

    function addCadeteCard() {
        const container = document.getElementById('cadetes-container');
        if (!container) return;

        const currentCount = container.querySelectorAll('.cadete-card').length;
        const newIndex = currentCount;
        const newNum = newIndex + 1;
        const chosenColor = CADETE_PALETTE[newIndex % CADETE_PALETTE.length];

        const cardHtml = `
            <div class="cadete-card bg-slate-50/90 rounded-2xl p-5 border border-slate-200 relative space-y-4 hover:border-slate-300 transition shadow-2xs animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <span class="cadete-number-badge w-7 h-7 rounded-xl flex items-center justify-center text-white text-xs font-black shadow-xs" style="background-color: ${chosenColor};">
                            ${newNum}
                        </span>
                        <div>
                            <h5 class="cadete-card-title text-xs font-black text-slate-800 uppercase tracking-wide">Cadete ${newNum}</h5>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label class="inline-flex items-center cursor-pointer space-x-1.5 select-none" title="Habilitar o deshabilitar cadete">
                            <input type="checkbox" name="cadetes[${newIndex}][is_active]" value="1" checked
                                   class="sr-only peer cadete-status-checkbox" onchange="updateCadeteStatusText(this)">
                            <div class="relative w-8 h-4 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="cadete-status-label text-[11px] font-black text-emerald-700">Activo</span>
                        </label>

                        <button type="button" onclick="removeCadeteCard(this)" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition ml-1 cursor-pointer" title="Eliminar este repartidor">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nombre del Cadete
                        </label>
                        <input type="text" name="cadetes[${newIndex}][name]" value=""
                               placeholder="Ej: Nuevo Repartidor"
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Teléfono / WhatsApp
                        </label>
                        <input type="text" name="cadetes[${newIndex}][phone]" value=""
                               placeholder="Ej: 5493794123456 o 3794123456"
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                        <span class="text-[10px] text-slate-400 mt-1 block">Número para el botón de WhatsApp</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Color Distintivo del Botón
                        </label>
                        <div class="flex items-center space-x-2.5">
                            <input type="color" name="cadetes[${newIndex}][color]" value="${chosenColor}"
                                   onchange="this.closest('.cadete-card').querySelector('.cadete-hex-label').innerText = this.value; this.closest('.cadete-card').querySelector('.cadete-number-badge').style.backgroundColor = this.value;"
                                   class="cadete-color-input w-9 h-8 p-0.5 rounded-lg border border-slate-300 cursor-pointer bg-white">
                            <span class="cadete-hex-label text-[11px] font-mono text-slate-600 font-bold uppercase">${chosenColor}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">Para diferenciar su botón en el pedido</span>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', cardHtml);
        reindexCadetes();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Nuevo motomandado agregado',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        }
    }

    function removeCadeteCard(btn) {
        const card = btn.closest('.cadete-card');
        if (!card) return;

        const name = card.querySelector('input[name*="[name]"]')?.value.trim();
        const confirmText = name 
            ? `¿Estás seguro de que deseas quitar a "${name}" de la lista de motomandados?` 
            : '¿Estás seguro de que deseas quitar este motomandado de la lista?';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Quitar motomandado?',
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl font-[Poppins]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    card.remove();
                    reindexCadetes();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Motomandado quitado de la lista',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        } else {
            if (confirm(confirmText)) {
                card.remove();
                reindexCadetes();
            }
        }
    }

    // ====================================================
    // GESTIÓN DE WHATSAPP AUTOMÁTICO (API BAILEYS)
    // ====================================================
    let waCheckTimer = null;

    function checkWhatsAppStatus() {
        fetch('{{ route("admin.whatsapp.status") }}')
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('wa-status-badge');
                const text = document.getElementById('wa-status-text');
                const qrBox = document.getElementById('wa-qr-container');
                const connBox = document.getElementById('wa-connected-container');
                const offBox = document.getElementById('wa-offline-container');
                const qrImg = document.getElementById('wa-qr-image');
                const phoneLabel = document.getElementById('wa-connected-phone');

                if (!badge || !text) return;

                if (data.connected) {
                    badge.className = 'inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-emerald-100 border border-emerald-300 text-xs font-bold text-emerald-800 flex-shrink-0';
                    text.innerText = '🟢 Conectado';
                    if (connBox) connBox.classList.remove('hidden');
                    if (qrBox) qrBox.classList.add('hidden');
                    if (offBox) offBox.classList.add('hidden');
                    if (phoneLabel && data.user) {
                        phoneLabel.innerText = `Número vinculado: ${data.user.id ? data.user.id.split(':')[0] : ''} (${data.user.name || 'Negocio'})`;
                    }
                } else if (data.status === 'qr_ready') {
                    badge.className = 'inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-amber-100 border border-amber-300 text-xs font-bold text-amber-800 flex-shrink-0';
                    text.innerText = '🟡 Esperando escaneo de QR';
                    if (connBox) connBox.classList.add('hidden');
                    if (offBox) offBox.classList.add('hidden');
                    if (qrBox) qrBox.classList.remove('hidden');

                    fetch('{{ route("admin.whatsapp.qr") }}')
                        .then(r => r.json())
                        .then(qrData => {
                            if (qrData.qr_image && qrImg) {
                                qrImg.src = qrData.qr_image;
                            }
                        });
                } else if (data.status === 'offline') {
                    badge.className = 'inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-500 flex-shrink-0';
                    text.innerText = '⚪ Servicio Inactivo';
                    if (connBox) connBox.classList.add('hidden');
                    if (qrBox) qrBox.classList.add('hidden');
                    if (offBox) offBox.classList.remove('hidden');
                } else {
                    badge.className = 'inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-xs font-bold text-blue-700 flex-shrink-0';
                    text.innerText = '🔵 Conectando...';
                }
            })
            .catch(() => {
                const badge = document.getElementById('wa-status-badge');
                const text = document.getElementById('wa-status-text');
                const offBox = document.getElementById('wa-offline-container');
                if (badge && text) {
                    badge.className = 'inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-slate-100 text-xs font-bold text-slate-500';
                    text.innerText = '⚪ Desconectado';
                }
                if (offBox) offBox.classList.remove('hidden');
            });
    }

    function disconnectWhatsApp() {
        Swal.fire({
            title: '¿Desvincular WhatsApp?',
            text: 'Se cerrará la sesión de WhatsApp del sistema. Deberás volver a escanear el código QR para usar el envío automático.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, desvincular',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: { popup: 'rounded-3xl shadow-2xl font-[Poppins]' }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("admin.whatsapp.disconnect") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Sesión desvinculada',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(checkWhatsAppStatus, 1000);
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        checkWhatsAppStatus();
        waCheckTimer = setInterval(checkWhatsAppStatus, 3500);
    });
</script>
@endpush
@endsection
