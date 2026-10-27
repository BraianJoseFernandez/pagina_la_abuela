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
                    <span class="text-xs text-slate-500">Si está activa, los clientes verán el flyer en la portada con animaciones, modal y confeti.</span>
                </div>
                <label class="inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $event->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Modal automático al entrar a la carta -->
            @php
                $autoPopup = (bool) old('auto_popup', $event->auto_popup ?? true);
                $popupFrequency = old('popup_frequency', $event->popup_frequency ?? 'always');
                $frequencyOptions = [
                    'always'  => ['icon' => 'fa-bolt',          'label' => 'Siempre',             'hint' => 'Cada vez que alguien entra o recarga la carta'],
                    'session' => ['icon' => 'fa-door-open',     'label' => 'Una vez por visita',  'hint' => 'Se vuelve a mostrar cuando cierran y abren el navegador'],
                    'daily'   => ['icon' => 'fa-calendar-day',  'label' => 'Una vez al día',      'hint' => 'Ideal si tus clientes entran varias veces por día'],
                ];
            @endphp
            <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-orange-50 p-5 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/30 flex-shrink-0">
                            <i class="fas fa-window-restore"></i>
                        </div>
                        <div>
                            <span class="text-sm font-black text-slate-800 block">Abrir el flyer automáticamente al entrar</span>
                            <span class="text-xs text-slate-500">Lo primero que ve el cliente es el modal con el flyer completo, el confeti y el botón de WhatsApp.</span>
                        </div>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none flex-shrink-0">
                        <input type="checkbox" name="auto_popup" value="1" id="auto_popup_toggle" {{ $autoPopup ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-14 h-7 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>

                <div id="popup_frequency_group" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 transition-opacity {{ $autoPopup ? '' : 'opacity-40 pointer-events-none' }}">
                    @foreach($frequencyOptions as $value => $opt)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="popup_frequency" value="{{ $value }}" class="sr-only peer" {{ $popupFrequency === $value ? 'checked' : '' }}>
                            <div class="h-full rounded-xl border-2 border-slate-200 bg-white p-3 transition peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:shadow-sm hover:border-amber-300">
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $opt['icon'] }} text-amber-600 text-xs"></i>
                                    <span class="text-xs font-black text-slate-800">{{ $opt['label'] }}</span>
                                </div>
                                <span class="text-[11px] text-slate-500 leading-snug block mt-1">{{ $opt['hint'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
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

            <!-- Flyer / Imagen Promocional (completo o recortado con Cropper.js) -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Flyer del Evento / Promoción
                    </label>
                    <p class="text-xs text-slate-500 mt-1">Subí el flyer tal cual lo diseñaste: por defecto se usa <strong>completo</strong>. Si querés, podés recortarlo, girarlo o elegir un formato.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">
                    <!-- Flyer actual -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 flex flex-col">
                        <div class="flex items-center justify-between mb-2 px-1">
                            <span class="text-[11px] font-black uppercase tracking-wider text-slate-500" id="flyer-preview-label">Flyer actual</span>
                            <span id="flyer-preview-badge" class="hidden px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700"></span>
                        </div>
                        <div class="flyer-checker relative flex-1 min-h-[220px] rounded-xl overflow-hidden flex items-center justify-center">
                            @if($event->image_path)
                                <img id="flyer-preview-img" src="{{ asset($event->image_path) }}" alt="{{ $event->title }}" class="max-h-[340px] w-auto max-w-full object-contain rounded-lg shadow-lg">
                            @else
                                <img id="flyer-preview-img" src="" alt="" class="hidden max-h-[340px] w-auto max-w-full object-contain rounded-lg shadow-lg">
                                <span id="flyer-preview-empty" class="text-xs text-slate-400 font-bold">Todavía no hay flyer cargado</span>
                            @endif
                        </div>
                        <span id="flyer-preview-meta" class="text-[11px] text-slate-400 mt-2 px-1 truncate">{{ $event->image_path ? basename($event->image_path) : '' }}</span>
                    </div>

                    <!-- Zona de carga -->
                    <label for="event_image_input" id="flyer-dropzone"
                           class="rounded-2xl border-2 border-dashed border-red-200 bg-red-50/40 hover:bg-red-50 hover:border-red-400 transition p-6 flex flex-col items-center justify-center text-center cursor-pointer min-h-[220px]">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-lg shadow-red-500/30 mb-3">
                            <i class="fas fa-cloud-arrow-up text-xl"></i>
                        </div>
                        <span class="text-sm font-black text-slate-800">Elegir o arrastrar un flyer nuevo</span>
                        <span class="text-xs text-slate-500 mt-1">JPG, PNG o WEBP · vertical, cuadrado u horizontal</span>
                        <span class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-bold text-red-700 bg-white border border-red-200 px-3 py-1 rounded-full">
                            <i class="fas fa-expand"></i> Se sube completo, sin cortes
                        </span>
                        <input type="file" id="event_image_input" accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                    <!-- Solo se usa si eligen "Subir original sin cambios" -->
                    <input type="file" id="event_image_original" name="image_file" class="hidden" disabled>
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

<!-- Modal para ajustar el flyer (Cropper.js con formato libre) -->
<div id="event-cropper-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-5 sm:p-6 shadow-2xl space-y-4 max-h-[96vh] overflow-y-auto">
        <div class="flex justify-between items-start pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-black text-slate-800">Ajustar Flyer de la Promoción</h3>
                <p class="text-xs text-slate-500">Por defecto se toma el flyer <strong>completo</strong>. Elegí un formato solo si querés recortarlo.</p>
            </div>
            <button type="button" onclick="closeEventCropperModal(true)" class="text-slate-400 hover:text-slate-600 p-1"><i class="fas fa-times text-lg"></i></button>
        </div>

        <!-- Formatos -->
        <div class="flex flex-wrap gap-2" id="event-aspect-presets">
            <button type="button" data-aspect="full" class="aspect-chip is-active"><i class="fas fa-expand"></i> Flyer completo</button>
            <button type="button" data-aspect="free" class="aspect-chip"><i class="fas fa-crop-simple"></i> Libre</button>
            <button type="button" data-aspect="0.8" class="aspect-chip"><i class="fas fa-mobile-screen"></i> Vertical 4:5</button>
            <button type="button" data-aspect="0.5625" class="aspect-chip"><i class="fas fa-mobile"></i> Historia 9:16</button>
            <button type="button" data-aspect="1" class="aspect-chip"><i class="far fa-square"></i> Cuadrado</button>
            <button type="button" data-aspect="1.7778" class="aspect-chip"><i class="fas fa-tv"></i> Horizontal 16:9</button>
        </div>

        <div class="event-cropper-stage flyer-checker rounded-2xl overflow-hidden">
            <img id="event-cropper-image-target" src="" alt="" class="max-w-full block">
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="eventCropper && eventCropper.zoom(0.1)" class="cropper-tool-btn"><i class="fas fa-search-plus"></i><span>Agrandar</span></button>
                <button type="button" onclick="eventCropper && eventCropper.zoom(-0.1)" class="cropper-tool-btn"><i class="fas fa-search-minus"></i><span>Alejar</span></button>
                <button type="button" onclick="rotateEventFlyer()" class="cropper-tool-btn"><i class="fas fa-rotate-right"></i><span>Girar</span></button>
                <button type="button" onclick="setEventAspect('full')" class="cropper-tool-btn"><i class="fas fa-arrows-rotate"></i><span>Restablecer</span></button>
            </div>
            <span id="event-crop-size" class="text-[11px] font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full"></span>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 pt-4 border-t border-slate-100">
            <button type="button" onclick="closeEventCropperModal(true)" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                Cancelar
            </button>
            <div class="flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="useOriginalEventFlyer()" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1.5"
                        title="Sube el archivo exactamente como está, sin girar ni recortar">
                    <i class="fas fa-file-image"></i> Subir original sin cambios
                </button>
                <button type="button" onclick="applyEventCrop()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-check"></i> Aplicar Flyer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* El layout admin redondea el recorte (para platos); el flyer necesita un recorte rectangular */
    #event-cropper-modal .cropper-view-box,
    #event-cropper-modal .cropper-face { border-radius: 0 !important; }
    #event-cropper-modal .cropper-view-box { outline: 2px solid #ef4444; outline-color: rgba(239, 68, 68, .9); }
    #event-cropper-modal .cropper-line,
    #event-cropper-modal .cropper-point { background-color: #ef4444; }
    .event-cropper-stage { height: min(58vh, 560px); }
    .event-cropper-stage img { max-height: 100%; }
    .flyer-checker {
        background-color: #0f172a;
        background-image:
            linear-gradient(45deg, #1e293b 25%, transparent 25%),
            linear-gradient(-45deg, #1e293b 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #1e293b 75%),
            linear-gradient(-45deg, transparent 75%, #1e293b 75%);
        background-size: 18px 18px;
        background-position: 0 0, 0 9px, 9px -9px, -9px 0;
    }
    .aspect-chip {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .45rem .8rem; border-radius: 9999px;
        font-size: .7rem; font-weight: 800; color: #475569;
        background: #f1f5f9; border: 2px solid transparent; transition: all .15s ease;
    }
    .aspect-chip:hover { background: #fee2e2; color: #b91c1c; }
    .aspect-chip.is-active { background: #fef2f2; color: #b91c1c; border-color: #ef4444; box-shadow: 0 4px 12px -4px rgba(239,68,68,.45); }
    .cropper-tool-btn {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .55rem .8rem; border-radius: .75rem;
        background: #f1f5f9; color: #334155; font-size: .72rem; font-weight: 700; transition: background .15s ease;
    }
    .cropper-tool-btn:hover { background: #e2e8f0; }
    #flyer-dropzone.is-dragover { background: #fee2e2; border-color: #ef4444; transform: scale(1.01); }
</style>

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

    // ===== Modal automático: habilitar / deshabilitar opciones de frecuencia =====
    (function () {
        const toggle = document.getElementById('auto_popup_toggle');
        const group = document.getElementById('popup_frequency_group');
        if (!toggle || !group) return;
        toggle.addEventListener('change', () => {
            group.classList.toggle('opacity-40', !toggle.checked);
            group.classList.toggle('pointer-events-none', !toggle.checked);
        });
    })();

    // ===== Flyer del evento con Cropper.js (completo por defecto, recorte opcional) =====
    const FLYER_MAX_WIDTH = 1600;
    const FLYER_MAX_HEIGHT = 2400;

    let eventCropper = null;
    let eventSelectedFile = null;
    let eventAspectMode = 'full';

    const eventFileInput = document.getElementById('event_image_input');
    const eventModal = document.getElementById('event-cropper-modal');
    const eventImageTarget = document.getElementById('event-cropper-image-target');
    const eventBase64Input = document.getElementById('cropped_image_base64');
    const flyerDropzone = document.getElementById('flyer-dropzone');

    function openEventCropperWithFile(file) {
        if (!file || !file.type.startsWith('image/')) {
            Swal.fire({ icon: 'error', title: 'Archivo no válido', text: 'Elegí una imagen JPG, PNG o WEBP.' });
            return;
        }

        eventSelectedFile = file;
        eventAspectMode = 'full';
        // Se limpia el selector para poder volver a elegir el mismo archivo (el navegador no dispara "change" si no)
        eventFileInput.value = '';
        const reader = new FileReader();
        reader.onload = function (event) {
            eventImageTarget.src = event.target.result;
            eventModal.classList.remove('hidden');
            eventModal.classList.add('flex');

            if (eventCropper) eventCropper.destroy();
            eventCropper = new Cropper(eventImageTarget, {
                aspectRatio: NaN,          // Sin forzar formato: se respeta el flyer completo
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,           // El recorte inicial cubre el 100% del flyer
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                background: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                ready() {
                    setEventAspect(eventAspectMode);
                },
                crop() {
                    updateEventCropSizeLabel();
                },
            });
        };
        reader.readAsDataURL(file);
    }

    function setEventAspect(mode) {
        if (!eventCropper) return;
        eventAspectMode = mode;

        document.querySelectorAll('#event-aspect-presets .aspect-chip').forEach(chip => {
            chip.classList.toggle('is-active', chip.dataset.aspect === String(mode));
        });

        if (mode === 'full') {
            eventCropper.setAspectRatio(NaN);
            const canvas = eventCropper.getCanvasData();
            eventCropper.setCropBoxData({ left: canvas.left, top: canvas.top, width: canvas.width, height: canvas.height });
        } else if (mode === 'free') {
            eventCropper.setAspectRatio(NaN);
        } else {
            eventCropper.setAspectRatio(parseFloat(mode));
        }
        updateEventCropSizeLabel();
    }

    // Gira la imagen de verdad (no solo la vista) para que "Flyer completo" siga tomando todo el diseño
    function rotateEventFlyer() {
        if (!eventCropper) return;
        const source = new Image();
        source.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = source.naturalHeight;
            canvas.height = source.naturalWidth;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.translate(canvas.width / 2, canvas.height / 2);
            ctx.rotate(Math.PI / 2);
            ctx.drawImage(source, -source.naturalWidth / 2, -source.naturalHeight / 2);
            eventCropper.replace(canvas.toDataURL('image/jpeg', 0.95));
        };
        source.src = eventCropper.url;
    }

    function getEventOutputSize() {
        if (!eventCropper) return null;
        const data = eventCropper.getData(true);
        if (!data.width || !data.height) return null;
        const scale = Math.min(1, FLYER_MAX_WIDTH / data.width, FLYER_MAX_HEIGHT / data.height);
        return { width: Math.round(data.width * scale), height: Math.round(data.height * scale) };
    }

    function updateEventCropSizeLabel() {
        const label = document.getElementById('event-crop-size');
        const size = getEventOutputSize();
        if (label && size) {
            label.textContent = `Tamaño final: ${size.width} × ${size.height} px`;
        }
    }

    document.querySelectorAll('#event-aspect-presets .aspect-chip').forEach(chip => {
        chip.addEventListener('click', () => setEventAspect(chip.dataset.aspect));
    });

    if (eventFileInput) {
        eventFileInput.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) openEventCropperWithFile(files[0]);
        });
    }

    // Arrastrar y soltar el flyer sobre la zona de carga
    if (flyerDropzone) {
        ['dragenter', 'dragover'].forEach(evt => flyerDropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            flyerDropzone.classList.add('is-dragover');
        }));
        ['dragleave', 'drop'].forEach(evt => flyerDropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            flyerDropzone.classList.remove('is-dragover');
        }));
        flyerDropzone.addEventListener('drop', (e) => {
            const file = e.dataTransfer?.files?.[0];
            if (file) openEventCropperWithFile(file);
        });
    }

    function closeEventCropperModal(cancelled = false) {
        eventModal.classList.add('hidden');
        eventModal.classList.remove('flex');
        if (eventCropper) {
            eventCropper.destroy();
            eventCropper = null;
        }
        // Al cancelar se descarta solo el archivo recién elegido; lo aplicado antes se mantiene
        if (cancelled) eventSelectedFile = null;
    }

    function setOriginalFlyerFile(file) {
        const originalInput = document.getElementById('event_image_original');
        if (!file) {
            originalInput.value = '';
            originalInput.disabled = true;
            return true;
        }
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            originalInput.files = dt.files;
            originalInput.disabled = false;
            return true;
        } catch (err) {
            return false; // navegador muy viejo sin DataTransfer
        }
    }

    function showFlyerPreview(src, badgeText, metaText) {
        const img = document.getElementById('flyer-preview-img');
        const empty = document.getElementById('flyer-preview-empty');
        const badge = document.getElementById('flyer-preview-badge');
        const label = document.getElementById('flyer-preview-label');
        const meta = document.getElementById('flyer-preview-meta');

        img.src = src;
        img.classList.remove('hidden');
        if (empty) empty.classList.add('hidden');
        label.textContent = 'Nuevo flyer (se guarda al confirmar)';
        badge.textContent = badgeText;
        badge.classList.remove('hidden');
        meta.textContent = metaText;
    }

    function applyEventCrop() {
        if (!eventCropper) return;

        const size = getEventOutputSize();
        const canvas = eventCropper.getCroppedCanvas({
            width: size?.width,
            height: size?.height,
            fillColor: '#ffffff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        const base64Data = canvas.toDataURL('image/jpeg', 0.92);
        eventBase64Input.value = base64Data;
        // Se envía solo la versión ajustada (evita subir el archivo dos veces)
        setOriginalFlyerFile(null);

        const badge = eventAspectMode === 'full' ? 'Completo' : 'Recortado';
        showFlyerPreview(base64Data, badge, `${canvas.width} × ${canvas.height} px · ${eventSelectedFile?.name || ''}`);
        closeEventCropperModal(false);
    }

    function useOriginalEventFlyer() {
        if (!eventSelectedFile) return;

        if (!setOriginalFlyerFile(eventSelectedFile)) {
            Swal.fire({ icon: 'info', title: 'Navegador no compatible', text: 'Usá "Aplicar Flyer": se sube completo igual.' });
            return;
        }
        eventBase64Input.value = '';

        const sizeMb = (eventSelectedFile.size / (1024 * 1024)).toFixed(1);
        showFlyerPreview(URL.createObjectURL(eventSelectedFile), 'Original', `${eventSelectedFile.name} · ${sizeMb} MB`);
        closeEventCropperModal(false);
    }
</script>
@endpush
@endsection
