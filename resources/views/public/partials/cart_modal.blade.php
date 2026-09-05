<!-- Modal / Drawer del Carrito de Compras para WhatsApp (Flujo Paso a Paso) -->
<div id="cart-drawer-backdrop" class="fixed inset-0 z-[9990] bg-black/60 backdrop-blur-sm hidden transition-opacity duration-300 opacity-0" onclick="closeCartModal()"></div>

<div id="cart-drawer" class="fixed top-0 right-0 bottom-0 w-full max-w-lg bg-white z-[9995] shadow-2xl flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out">

    <!-- ========================================== -->
    <!-- PASO 1: REVISIÓN Y AJUSTE DE PLATOS        -->
    <!-- ========================================== -->
    <div id="cart-step-1" class="flex flex-col h-full">
        <!-- Header Paso 1 -->
        <div class="p-4 sm:p-5 bg-gradient-to-r from-red-600 to-rose-600 text-white flex items-center justify-between shadow-md flex-shrink-0">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-shopping-bag text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black tracking-wide">Tu Pedido</h2>
                    <p class="text-xs text-red-100 font-medium">Revisa y ajusta tus platos</p>
                </div>
            </div>
            <button onclick="closeCartModal()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition" title="Cerrar">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Lista 100% visible y scrolleable de platos seleccionados -->
        <div class="flex-grow overflow-y-auto p-4 sm:p-5 space-y-4" id="cart-items-container">
            <!-- Renderizado dinámico vía cart.js -->
        </div>

        <!-- Footer Paso 1: Totales y Botón para Avanzar a los Datos -->
        <div id="cart-step1-footer" class="p-4 sm:p-5 bg-gray-50 border-t border-gray-200 space-y-3 flex-shrink-0">
            <div class="space-y-1 pb-1">
                <div class="flex justify-between text-sm text-gray-500 font-medium">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal-display" class="font-bold text-gray-700">$0</span>
                </div>
                <div class="flex justify-between items-center text-xl font-black text-gray-900">
                    <span>Total a Pagar:</span>
                    <span id="cart-total-display" class="text-2xl text-red-600 font-black">$0</span>
                </div>
            </div>

            <button type="button"
                    onclick="goToCartStep(2)"
                    id="btn-goto-step-2"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-black text-base shadow-xl shadow-red-500/25 transition-all duration-200 flex items-center justify-center space-x-2 cursor-pointer transform hover:scale-[1.01] active:scale-[0.99]">
                <span>Continuar con mis datos</span>
                <i class="fas fa-arrow-right text-sm"></i>
            </button>

            <button type="button" onclick="closeCartModal()" class="w-full text-center text-xs font-bold text-gray-500 hover:text-gray-800 transition py-1">
                + Agregar más platos de la carta
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PASO 2: DATOS DEL CLIENTE Y ENVÍO A WHATSAPP-->
    <!-- ========================================== -->
    <div id="cart-step-2" class="hidden flex-col h-full">
        <!-- Header Paso 2 con botón de retorno -->
        <div class="p-4 sm:p-5 bg-gradient-to-r from-red-600 to-rose-600 text-white flex items-center justify-between shadow-md flex-shrink-0">
            <div class="flex items-center space-x-3">
                <button type="button" onclick="goToCartStep(1)" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition" title="Volver al pedido">
                    <i class="fas fa-arrow-left text-base"></i>
                </button>
                <div>
                    <h2 class="text-lg sm:text-xl font-black tracking-wide leading-tight">Datos de Entrega</h2>
                    <p class="text-xs text-red-100 font-medium">Completa para armar el WhatsApp</p>
                </div>
            </div>
            <button onclick="closeCartModal()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition" title="Cerrar">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Formulario scrolleable de datos del cliente -->
        <div class="flex-grow overflow-y-auto p-4 sm:p-5 space-y-4">
            <!-- Mini Resumen del Pedido (Toca para volver a modificar) -->
            <div onclick="goToCartStep(1)" class="p-3.5 bg-purple-50 hover:bg-purple-100/80 border border-purple-200 rounded-2xl flex items-center justify-between cursor-pointer transition shadow-2xs">
                <div class="flex items-center space-x-2.5">
                    <span class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xs font-black" id="step2-items-badge">0</span>
                    <div>
                        <span class="text-xs font-black text-purple-900 block" id="step2-items-text">Resumen de platos</span>
                        <span class="text-[11px] text-purple-600 font-semibold">Toca aquí para modificar los platos</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-base font-black text-purple-900" id="step2-total-badge">$0</span>
                </div>
            </div>

            <!-- Campos de Datos del Cliente -->
            <div class="space-y-3.5 bg-gray-50 p-4 rounded-2xl border border-gray-200">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Tu Nombre y Apellido *</label>
                    <input type="text" id="order-customer-name" placeholder="Ej: Juan Perez" class="w-full text-sm px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition bg-white font-medium" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Teléfono / WhatsApp *</label>
                    <input type="tel" id="order-customer-phone" placeholder="Ej: 3794-123456" class="w-full text-sm px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition bg-white font-medium" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1 flex items-center justify-between">
                        <span>Correo Electrónico (opcional)</span>
                        <span class="text-[10px] text-gray-400 font-normal lowercase">Para recibir copia del pedido</span>
                    </label>
                    <input type="email" id="order-customer-email" placeholder="Ej: tuemail@gmail.com" class="w-full text-sm px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition bg-white font-medium">
                </div>

                <!-- Tipo de entrega -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Tipo de Entrega *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center p-3 rounded-xl border-2 border-gray-200 cursor-pointer transition hover:bg-gray-100 has-[:checked]:border-red-600 has-[:checked]:bg-red-50 has-[:checked]:text-red-700 font-bold text-xs text-center space-x-2">
                            <input type="radio" name="order_delivery_type" value="delivery" checked class="hidden" onchange="toggleAddressField(true)">
                            <i class="fas fa-motorcycle text-base"></i>
                            <span>Envío a Domicilio</span>
                        </label>
                        <label class="flex items-center justify-center p-3 rounded-xl border-2 border-gray-200 cursor-pointer transition hover:bg-gray-100 has-[:checked]:border-red-600 has-[:checked]:bg-red-50 has-[:checked]:text-red-700 font-bold text-xs text-center space-x-2">
                            <input type="radio" name="order_delivery_type" value="takeaway" class="hidden" onchange="toggleAddressField(false)">
                            <i class="fas fa-store text-base"></i>
                            <span>Retiro en Local</span>
                        </label>
                    </div>
                </div>

                <!-- Dirección de entrega & Pin en Mapa -->
                <div id="delivery-address-container" class="space-y-2.5">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                Dirección de Envío <span id="address-required-asterisk" class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-normal">Calle y número / barrio</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" id="order-customer-address" oninput="handleAddressOrPinChanged()" onkeydown="if(event.key==='Enter'){event.preventDefault();searchAddressOnMap();}" placeholder="Ej: Av Libertad 5445" class="w-full text-sm px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition bg-white font-medium">
                            <button type="button" onclick="searchAddressOnMap()" class="px-3.5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center space-x-1.5 flex-shrink-0 transition shadow-xs cursor-pointer" title="Ubicar esta dirección en el mapa">
                                <i class="fas fa-search text-xs"></i>
                                <span class="hidden sm:inline">Ubicar</span>
                            </button>
                        </div>

                        <!-- Opciones rápidas de Google Maps -->
                        <div class="flex items-center justify-between px-1 text-[11px] text-gray-500 pt-0.5">
                            <span class="truncate">¿Tenés tu ubicación en Google Maps?</span>
                            <div class="flex items-center space-x-2 flex-shrink-0">
                                <button type="button" onclick="promptPasteGoogleMapsLink()" class="text-blue-600 hover:text-blue-800 font-bold flex items-center space-x-1 underline cursor-pointer">
                                    <i class="fab fa-google text-[10px]"></i>
                                    <span>Pegar link / enlace</span>
                                </button>
                                <span class="text-gray-300">|</span>
                                <a id="btn-open-external-gmaps" href="https://maps.google.com" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900 font-bold flex items-center space-x-1">
                                    <i class="fas fa-external-link-alt text-[9px]"></i>
                                    <span>Abrir Maps</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Apartado de Pin en el Mapa (Google Maps / GPS) -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-3.5 space-y-2.5 shadow-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-7 h-7 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-xs font-bold">
                                    <i class="fas fa-map-pin"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-gray-800 block leading-tight">Ubicación exacta en Mapa</span>
                                    <span class="text-[10px] text-gray-500 block">Pin para que el cadete llegue directo</span>
                                </div>
                            </div>
                            <button type="button"
                                    id="btn-toggle-cart-map"
                                    onclick="toggleCartMap()"
                                    class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100/80 px-2.5 py-1.5 rounded-xl transition flex items-center space-x-1 cursor-pointer">
                                <i class="fas fa-map-marked-alt text-[11px]"></i>
                                <span id="toggle-map-btn-text">Abrir Mapa</span>
                            </button>
                        </div>

                        <!-- Badge de estado de Pin -->
                        <div id="cart-map-status-badge" class="hidden items-center justify-between p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                            <div class="flex items-center space-x-1.5 truncate">
                                <i class="fas fa-check-circle text-emerald-600 text-sm flex-shrink-0"></i>
                                <span id="cart-map-coords-label" class="truncate text-[11px]">Ubicación fijada</span>
                            </div>
                            <div class="flex items-center space-x-1 flex-shrink-0">
                                <a id="cart-map-external-link" href="#" target="_blank" class="text-[10px] text-emerald-700 underline font-semibold hover:text-emerald-900 px-1">
                                    Ver en Maps
                                </a>
                                <button type="button" onclick="clearCartMapPin()" class="text-gray-400 hover:text-red-500 p-1 rounded transition" title="Quitar pin">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Contenedor del Mapa (Oculto por defecto o expandible) -->
                        <div id="cart-map-wrapper" class="hidden space-y-2 pt-1">
                            <!-- Buscador integrado dentro del mapa y Botón GPS -->
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-1.5">
                                    <div class="relative flex-grow">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text"
                                               id="cart-map-search-input"
                                               placeholder="Buscar calle o punto en Corrientes..."
                                               onkeydown="if(event.key==='Enter'){event.preventDefault();searchAddressOnMap(this.value);}"
                                               class="w-full text-xs pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none">
                                    </div>
                                    <button type="button"
                                            onclick="searchAddressOnMap(document.getElementById('cart-map-search-input').value)"
                                            class="px-3 py-2 rounded-xl bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs flex-shrink-0 transition">
                                        Ir
                                    </button>
                                </div>

                                <button type="button"
                                        id="btn-cart-gps-locate"
                                        onclick="locateUserGPS()"
                                        class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-sm flex items-center justify-center space-x-2 transition cursor-pointer">
                                    <i class="fas fa-crosshairs text-xs"></i>
                                    <span>Usar mi ubicación actual (GPS)</span>
                                </button>
                            </div>

                            <!-- Mapa interactivo (Google Maps oficial o Leaflet fallback) -->
                            <div class="relative w-full h-48 sm:h-52 rounded-xl overflow-hidden border-2 border-red-200 shadow-inner">
                                <div id="cart-leaflet-map" class="w-full h-full z-10"></div>
                                <div class="absolute bottom-1.5 left-1.5 right-1.5 z-20 bg-white/90 backdrop-blur-xs px-2.5 py-1 rounded-lg text-[10px] text-gray-600 font-medium text-center shadow-xs pointer-events-none">
                                    👉 Toca el mapa o arrastra el marcador rojo para señalar tu casa exacta
                                </div>
                            </div>
                        </div>

                        <!-- Inputs ocultos para coordenadas -->
                        <input type="hidden" id="order-delivery-lat" value="">
                        <input type="hidden" id="order-delivery-lng" value="">
                        <input type="hidden" id="order-delivery-map-url" value="">
                    </div>
                </div>

                <!-- Método de Pago -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Forma de Pago</label>
                    <select id="order-payment-method" class="w-full text-sm px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition bg-white font-bold text-gray-800">
                        <option value="Efectivo">💵 Efectivo al recibir</option>
                        <option value="Transferencia / Mercado Pago">💳 Transferencia / Mercado Pago</option>
                        <option value="Tarjeta">💳 Tarjeta Débito/Crédito</option>
                    </select>
                </div>

                <!-- Notas del pedido -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Aclaraciones o Notas para la cocina (opcional)</label>
                    <textarea id="order-general-notes" rows="2" placeholder="Ej: Timbre no funciona, enviar aderezos, bien cocido..." class="w-full text-sm px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition bg-white font-medium"></textarea>
                </div>
            </div>
        </div>

        <!-- Footer Paso 2: Botón Principal de Envío a WhatsApp -->
        <div class="p-4 sm:p-5 bg-white border-t border-gray-200 space-y-2 flex-shrink-0">
            <button onclick="submitOrderToWhatsApp()"
                    id="btn-submit-whatsapp"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-black text-base shadow-xl shadow-green-600/30 transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center space-x-3 cursor-pointer">
                <i class="fab fa-whatsapp text-2xl animate-bounce"></i>
                <span>Preparar mi Pedido en WhatsApp</span>
            </button>
            <button type="button" onclick="goToCartStep(1)" class="w-full text-center text-xs font-bold text-gray-500 hover:text-gray-800 transition py-1">
                ← Volver a modificar platos
            </button>
        </div>
    </div>

</div>
