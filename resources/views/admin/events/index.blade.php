@extends('layouts.admin')

@section('title', 'Configurar Eventos y Promociones')
@section('page-title', 'Sección Eventos y Promociones Especiales')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80">
        <div class="border-b border-slate-100 pb-5 mb-6">
            <h3 class="text-xl font-black text-slate-800">Banner Promocional de Portada</h3>
            <p class="text-xs text-slate-500 mt-1">
                Configura la sección interactiva de eventos (como el Mundial, San Valentín, Noche de Blanco o promociones especiales del día).
            </p>
        </div>

        <form action="{{ route('admin.events.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <input type="hidden" name="cropped_image_base64" id="cropped_image_base64">

            <!-- Switch de Activación de la Sección -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-sm font-black text-slate-800 block">Mostrar Sección de Eventos en la Portada</span>
                    <span class="text-xs text-slate-500">Si está activa, los clientes verán el banner interactivo con animaciones, modal y confeti al ingresar.</span>
                </div>
                <label class="inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $event->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Título y Subtítulo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Título de la Promoción / Evento *
                    </label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                           placeholder="Ej: Oferta Mundial / Super Promo Fin de Semana"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Subtítulo o Mensaje Breve
                    </label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $event->subtitle) }}"
                           placeholder="Ej: ¡Aprovechá nuestras mejores promos exclusivas!"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Emoticones de los Costados (Badges) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Emoticones Animados (Izquierda)
                    </label>
                    <input type="text" name="badge_left_emoji" value="{{ old('badge_left_emoji', $event->badge_left_emoji) }}"
                           placeholder="⚽🇦🇷  o  🍕🔥  o  ❤️🌹"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-lg font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Emoticones Animados (Derecha)
                    </label>
                    <input type="text" name="badge_right_emoji" value="{{ old('badge_right_emoji', $event->badge_right_emoji) }}"
                           placeholder="⚽🇦🇷  o  🍕🔥  o  ❤️🌹"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-lg font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                </div>
            </div>

            <!-- CONFETI Y SELECTOR VISUAL DE COLORES MÚLTIPLES -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-800">Efectos de Confeti al Abrir el Evento</h4>
                        <p class="text-xs text-slate-500">Personaliza los emoticones y los múltiples colores del confeti que se dispara al tocar la promo.</p>
                    </div>
                    <button type="button" onclick="testConfettiPreview()" class="px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-xs flex items-center space-x-1.5 transition">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        <span>Probar Confeti</span>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Emoticones -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Emoticones para el Confeti (separados por coma)
                        </label>
                        <input type="text" id="confetti_emojis_input" name="confetti_emojis" value="{{ old('confetti_emojis', $event->confetti_emojis) }}"
                               placeholder="⚽, 🇦🇷, 🏆, 🎉  o  🍕, 🍔, 🍟"
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-base font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
                    </div>

                    <!-- SELECTOR VISUAL DE COLORES -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Paleta de Colores del Confeti (Selección Múltiple)
                        </label>

                        <input type="hidden" id="confetti_colors_hidden" name="confetti_colors" value="{{ old('confetti_colors', $event->confetti_colors ?: '#75AADB,#FFFFFF,#F6B40E') }}">

                        <!-- Contenedor de chips de colores seleccionados -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-600">Colores seleccionados para el confeti:</span>
                                <div class="flex items-center space-x-2">
                                    <input type="color" id="native_color_picker" value="#FF5722" class="w-8 h-8 rounded-lg cursor-pointer border-0 bg-transparent">
                                    <button type="button" onclick="addColorFromPicker()" class="px-3 py-1 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition flex items-center space-x-1">
                                        <i class="fas fa-plus text-[10px]"></i>
                                        <span>Añadir Color</span>
                                    </button>
                                </div>
                            </div>

                            <div id="colors_chips_container" class="flex flex-wrap gap-2 pt-1 min-h-[38px] items-center">
                                <!-- Chips dinámicos -->
                            </div>

                            <!-- Paletas predefinidas de 1 clic -->
                            <div class="pt-2 border-t border-slate-200/80">
                                <span class="text-[11px] font-bold text-slate-400 block mb-2">O elige una combinación rápida:</span>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" onclick="applyPresetColors(['#75AADB', '#FFFFFF', '#F6B40E'])" class="px-3 py-1 rounded-full text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:border-purple-400 flex items-center space-x-1.5 shadow-2xs">
                                        <span class="w-3 h-3 rounded-full bg-[#75AADB] inline-block border border-slate-300"></span>
                                        <span class="w-3 h-3 rounded-full bg-white inline-block border border-slate-300"></span>
                                        <span class="w-3 h-3 rounded-full bg-[#F6B40E] inline-block border border-slate-300"></span>
                                        <span>Argentina 🇦🇷</span>
                                    </button>
                                    <button type="button" onclick="applyPresetColors(['#DC2626', '#F59E0B', '#10B981'])" class="px-3 py-1 rounded-full text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:border-purple-400 flex items-center space-x-1.5 shadow-2xs">
                                        <span class="w-3 h-3 rounded-full bg-[#DC2626] inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-[#F59E0B] inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-[#10B981] inline-block"></span>
                                        <span>Rotisería / Pizza 🍕</span>
                                    </button>
                                    <button type="button" onclick="applyPresetColors(['#EC4899', '#EF4444', '#F43F5E', '#FFFFFF'])" class="px-3 py-1 rounded-full text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:border-purple-400 flex items-center space-x-1.5 shadow-2xs">
                                        <span class="w-3 h-3 rounded-full bg-[#EC4899] inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-[#EF4444] inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-white inline-block border border-slate-300"></span>
                                        <span>San Valentín ❤️</span>
                                    </button>
                                    <button type="button" onclick="applyPresetColors(['#8B5CF6', '#EC4899', '#3B82F6', '#10B981', '#F59E0B'])" class="px-3 py-1 rounded-full text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:border-purple-400 flex items-center space-x-1.5 shadow-2xs">
                                        <span class="w-3 h-3 rounded-full bg-[#8B5CF6] inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-[#EC4899] inline-block"></span>
                                        <span class="w-3 h-3 rounded-full bg-[#F59E0B] inline-block"></span>
                                        <span>Carnaval / Fiesta 🎉</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Imagen Promocional con Cropper.js (Redondeada y con zoom) -->
            <div class="border-t border-slate-100 pt-6 space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Imagen del Banner Promocional (Recorte y Zoom Redondeado)
                </label>
                <p class="text-xs text-slate-500">Sube la foto del banner y utiliza la herramienta para agrandar, recortar y centrar la imagen.</p>

                <div class="flex flex-col sm:flex-row items-center gap-5">
                    @if($event->image_path)
                        <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200 flex items-center space-x-3">
                            <img src="{{ asset($event->image_path) }}" alt="{{ $event->title }}" class="w-24 h-24 rounded-2xl object-cover shadow-sm border border-slate-200">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block">Imagen Actual</span>
                                <span class="text-[10px] text-slate-400">{{ basename($event->image_path) }}</span>
                            </div>
                        </div>
                    @endif

                    <input type="file" id="event_image_input" accept="image/*"
                           class="w-full sm:w-auto text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">

                    <!-- Preview recortada -->
                    <div id="event-cropped-preview-container" class="hidden items-center space-x-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-200">
                        <img id="event-cropped-preview-img" src="" alt="Vista previa" class="w-24 h-24 rounded-2xl object-cover shadow-md border-2 border-red-500">
                        <span class="text-xs font-bold text-emerald-600 flex items-center space-x-1">
                            <i class="fas fa-check-circle"></i>
                            <span>Imagen recortada lista</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Mensaje predefinido de WhatsApp para consultar la promo -->
            <div class="border-t border-slate-100 pt-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Texto Predefinido para el Botón de WhatsApp de la Promo
                </label>
                <input type="text" name="whatsapp_custom_text" value="{{ old('whatsapp_custom_text', $event->whatsapp_custom_text) }}"
                       placeholder="¡Hola! Quiero consultar por la promo especial ⚽🇦🇷"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:bg-white transition">
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="submit" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-sm shadow-lg shadow-red-500/20 hover:from-red-700 hover:to-rose-700 transition">
                    Guardar Configuración de Evento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Recorte Cropper.js para Banner de Evento -->
<div id="event-cropper-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h3 class="text-lg font-black text-slate-800">Ajustar Imagen de Promoción</h3>
            <button onclick="closeEventCropperModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="max-h-[50vh] overflow-hidden bg-slate-900 rounded-2xl flex items-center justify-center">
            <img id="event-cropper-image-target" src="" class="max-w-full block">
        </div>

        <!-- Controles de Zoom -->
        <div class="flex items-center justify-center space-x-4 pt-2">
            <button type="button" onclick="eventCropper.zoom(0.1)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                <i class="fas fa-search-plus"></i>
                <span>Agrandar</span>
            </button>
            <button type="button" onclick="eventCropper.zoom(-0.1)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                <i class="fas fa-search-minus"></i>
                <span>Alejar</span>
            </button>
            <button type="button" onclick="eventCropper.rotate(90)" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                <i class="fas fa-rotate-right"></i>
                <span>Girar</span>
            </button>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
            <button type="button" onclick="closeEventCropperModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                Cancelar
            </button>
            <button type="button" onclick="applyEventCrop()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs shadow-md transition">
                Aplicar Imagen
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.js"></script>
<script>
    // Gestión de Colores de Confeti
    let selectedColors = [];

    function initColors() {
        const raw = document.getElementById('confetti_colors_hidden').value;
        if (raw) {
            selectedColors = raw.split(',').map(c => c.trim().toUpperCase()).filter(c => c.length > 0);
        } else {
            selectedColors = ['#75AADB', '#FFFFFF', '#F6B40E'];
        }
        renderColorChips();
    }

    function renderColorChips() {
        const container = document.getElementById('colors_chips_container');
        if (!container) return;

        if (selectedColors.length === 0) {
            container.innerHTML = '<span class="text-xs text-slate-400 italic">No hay colores seleccionados (se usarán colores variados por defecto).</span>';
        } else {
            container.innerHTML = selectedColors.map((hex, idx) => `
                <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white border border-slate-200 shadow-2xs">
                    <span class="w-4 h-4 rounded-full inline-block border border-slate-300 shadow-xs" style="background-color: ${hex}"></span>
                    <span class="font-mono text-slate-700">${hex}</span>
                    <button type="button" onclick="removeColor(${idx})" class="text-slate-400 hover:text-red-500 ml-1 p-0.5" title="Eliminar color">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
            `).join('');
        }

        document.getElementById('confetti_colors_hidden').value = selectedColors.join(',');
    }

    function addColorFromPicker() {
        const color = document.getElementById('native_color_picker').value.toUpperCase();
        if (!selectedColors.includes(color)) {
            selectedColors.push(color);
            renderColorChips();
        }
    }

    function removeColor(index) {
        selectedColors.splice(index, 1);
        renderColorChips();
    }

    function applyPresetColors(colorsArray) {
        selectedColors = [...colorsArray];
        renderColorChips();
    }

    function parseAdminEmojis(rawEmojis) {
        if (!rawEmojis || typeof rawEmojis !== 'string') return [];
        let list = [];
        if (rawEmojis.includes(',')) {
            list = rawEmojis.split(',').map(e => e.trim()).filter(e => e.length > 0);
        } else if (typeof Intl !== 'undefined' && Intl.Segmenter) {
            try {
                const segmenter = new Intl.Segmenter('es', { granularity: 'grapheme' });
                list = Array.from(segmenter.segment(rawEmojis.trim()))
                    .map(s => s.segment.trim())
                    .filter(s => s.length > 0);
            } catch (e) {
                list = [rawEmojis.trim()];
            }
        } else {
            list = rawEmojis.trim().split(/\s+/).filter(e => e.length > 0);
        }
        return list;
    }

    function testConfettiPreview() {
        const emojisRaw = document.getElementById('confetti_emojis_input').value;
        const emojis = parseAdminEmojis(emojisRaw);
        const colors = selectedColors.length > 0 ? selectedColors : ['#75AADB', '#FFFFFF', '#F6B40E'];

        // 1. Confeti de colores
        confetti({
            colors: colors,
            particleCount: 60,
            spread: 80,
            origin: { y: 0.6 }
        });

        // 2. Confeti de emoticones
        if (emojis.length > 0 && typeof confetti.shapeFromText === 'function') {
            const scalar = 3;
            const shapes = [];
            emojis.forEach(emoji => {
                try {
                    const s = confetti.shapeFromText({ text: emoji, scalar });
                    if (s) shapes.push(s);
                } catch (err) {
                    console.warn('Error convirtiendo emoticón:', emoji, err);
                }
            });

            if (shapes.length > 0) {
                confetti({
                    shapes: shapes,
                    scalar: 3,
                    particleCount: 30,
                    spread: 90,
                    origin: { y: 0.6 },
                    flat: true
                });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', initColors);

    // Cropper.js para imagen de Evento
    let eventCropper = null;
    const eventFileInput = document.getElementById('event_image_input');
    const eventModal = document.getElementById('event-cropper-modal');
    const eventImageTarget = document.getElementById('event-cropper-image-target');

    if (eventFileInput) {
        eventFileInput.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function (event) {
                    eventImageTarget.src = event.target.result;
                    eventModal.classList.remove('hidden');

                    if (eventCropper) eventCropper.destroy();
                    eventCropper = new Cropper(eventImageTarget, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.95,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function closeEventCropperModal() {
        eventModal.classList.add('hidden');
        if (eventCropper) eventCropper.destroy();
    }

    function applyEventCrop() {
        if (!eventCropper) return;
        const canvas = eventCropper.getCroppedCanvas({
            width: 1200,
            height: 675,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        const base64Data = canvas.toDataURL('image/jpeg', 0.9);
        document.getElementById('cropped_image_base64').value = base64Data;

        // Preview
        document.getElementById('event-cropped-preview-img').src = base64Data;
        document.getElementById('event-cropped-preview-container').classList.remove('hidden');
        document.getElementById('event-cropped-preview-container').classList.add('flex');

        closeEventCropperModal();
    }
</script>
@endpush
@endsection
