/**
 * Sistema de Carrito de Compras & Armado de Pedidos por WhatsApp
 * Rotisería La Abuela
 */

const CART_STORAGE_KEY = 'roti_abuela_cart_v1';
let currentSelectedProductForModal = null;

function formatCookingLabel(method) {
    if (!method) return '';
    const clean = String(method).trim();
    if (clean === 'Horno' || clean === 'Al Horno' || clean.toLowerCase().includes('horno')) {
        return '🔥 Horno';
    }
    if (clean === 'Frita' || clean === 'Frito' || clean.toLowerCase().includes('frit')) {
        return '🍳 Frita';
    }
    return clean;
}

class CartManager {
    constructor() {
        this.items = this.loadCart();
        this.updateBadge();
    }

    loadCart() {
        try {
            const data = localStorage.getItem(CART_STORAGE_KEY);
            const items = data ? JSON.parse(data) : [];
            return items.map(item => {
                if (item.cookingMethod === 'Al Horno') {
                    item.cookingMethod = 'Horno';
                }
                if (Array.isArray(item.cookingOptions)) {
                    item.cookingOptions = item.cookingOptions.map(opt => (opt === 'Al Horno' ? 'Horno' : opt));
                }
                return item;
            });
        } catch (e) {
            console.error('Error cargando carrito:', e);
            return [];
        }
    }

    saveCart() {
        try {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(this.items));
            this.updateBadge();
            this.renderCartUI();
        } catch (e) {
            console.error('Error guardando carrito:', e);
        }
    }

    addItem(product, variant = null, notes = '', quantity = 1, cookingMethod = null, garnish = null) {
        const variantId = variant ? variant.id : null;
        const variantName = variant ? variant.name : null;
        const basePrice = variant ? parseFloat(variant.price) : parseFloat(product.price || 0);
        const garnishPrice = garnish ? parseFloat(garnish.price || 0) : 0;
        const unitPrice = basePrice + garnishPrice;

        const hasCooking = !!product.has_cooking_options;
        let finalCookingMethod = cookingMethod;
        if (hasCooking) {
            if (!finalCookingMethod || finalCookingMethod === 'Al Horno') {
                finalCookingMethod = 'Horno';
            }
        }

        const garnishId = garnish ? (garnish.id || garnish.name) : null;
        const garnishName = garnish ? garnish.name : null;

        // Clave única para agrupar ítems idénticos considerando variantes, guarnición y cocción
        const itemKey = `${product.id}_${variantId || 'base'}_${garnishId || 'none'}_${finalCookingMethod || 'none'}_${notes.trim().toLowerCase()}`;

        const existingIndex = this.items.findIndex(item => item.key === itemKey);

        if (existingIndex > -1) {
            this.items[existingIndex].quantity += quantity;
            this.items[existingIndex].subtotal = this.items[existingIndex].quantity * unitPrice;
            if (this.items[existingIndex].basePrice === undefined) {
                this.items[existingIndex].basePrice = basePrice;
            }
        } else {
            let availableCookingOptions = ['Horno', 'Frita'];
            if (product.cooking_options) {
                if (Array.isArray(product.cooking_options)) {
                    availableCookingOptions = product.cooking_options;
                } else if (typeof product.cooking_options === 'string') {
                    try {
                        availableCookingOptions = JSON.parse(product.cooking_options);
                    } catch (e) {
                        availableCookingOptions = ['Horno', 'Frita'];
                    }
                }
            }

            availableCookingOptions = availableCookingOptions.map(opt => (opt === 'Al Horno' || opt === 'Horno') ? 'Horno' : opt);
            availableCookingOptions = [...new Set(availableCookingOptions)];

            const categoryName = product.category?.name || product.category_name || '';

            this.items.push({
                key: itemKey,
                productId: product.id,
                categoryName: categoryName,
                productName: product.name,
                variantId: variantId,
                variantName: variantName,
                basePrice: basePrice,
                garnishId: garnishId,
                garnishName: garnishName,
                garnishPrice: garnishPrice,
                hasCookingOptions: hasCooking,
                cookingOptions: availableCookingOptions,
                cookingMethod: finalCookingMethod,
                unitPrice: unitPrice,
                quantity: quantity,
                subtotal: unitPrice * quantity,
                notes: notes.trim(),
                imagePath: product.image_path || null
            });
        }

        this.saveCart();
        let toastDetails = [];
        if (variantName) toastDetails.push(variantName);
        if (finalCookingMethod) toastDetails.push(formatCookingLabel(finalCookingMethod));
        if (garnishName) toastDetails.push(garnishName);
        const toastSubtitle = toastDetails.length > 0 ? ` (${toastDetails.join(' - ')})` : '';
        this.showToast(`¡Añadido! ${product.name}${toastSubtitle}`);
    }

    setCookingMethod(itemKey, newMethod) {
        const index = this.items.findIndex(item => item.key === itemKey);
        if (index === -1) return;

        const item = this.items[index];
        const normalizedMethod = (newMethod === 'Al Horno' || newMethod === 'Horno') ? 'Horno' : newMethod;
        if (item.cookingMethod === normalizedMethod) return;

        // Clave del nuevo ítem resultante
        const newKey = `${item.productId}_${item.variantId || 'base'}_${item.garnishId || 'none'}_${normalizedMethod || 'none'}_${(item.notes || '').trim().toLowerCase()}`;
        const duplicateIndex = this.items.findIndex(other => other.key === newKey);

        if (duplicateIndex > -1 && duplicateIndex !== index) {
            // Fusionar con el ítem existente del mismo tipo y cocción
            this.items[duplicateIndex].quantity += item.quantity;
            this.items[duplicateIndex].subtotal = this.items[duplicateIndex].quantity * this.items[duplicateIndex].unitPrice;
            this.items.splice(index, 1);
        } else {
            item.cookingMethod = normalizedMethod;
            item.key = newKey;
        }

        this.saveCart();
        this.showToast(`Cambiado a ${formatCookingLabel(normalizedMethod)}`);
    }

    updateQuantity(itemKey, delta) {
        const index = this.items.findIndex(item => item.key === itemKey);
        if (index > -1) {
            this.items[index].quantity += delta;
            if (this.items[index].quantity <= 0) {
                this.items.splice(index, 1);
            } else {
                this.items[index].subtotal = this.items[index].quantity * this.items[index].unitPrice;
            }
            this.saveCart();
        }
    }

    removeItem(itemKey) {
        this.items = this.items.filter(item => item.key !== itemKey);
        this.saveCart();
    }

    clearCart() {
        this.items = [];
        this.saveCart();
    }

    getTotalCount() {
        return this.items.reduce((total, item) => total + item.quantity, 0);
    }

    getTotalAmount() {
        return this.items.reduce((total, item) => total + item.subtotal, 0);
    }

    updateBadge() {
        const count = this.getTotalCount();
        const total = this.getTotalAmount();

        const badge = document.getElementById('floating-cart-count');
        const totalDisplay = document.getElementById('floating-cart-total');

        if (badge) badge.innerText = count;
        if (totalDisplay) totalDisplay.innerText = '$' + total.toLocaleString('es-AR');

        const btn = document.getElementById('floating-cart-button');
        if (btn) {
            if (count > 0) {
                btn.classList.remove('scale-0', 'opacity-0');
            }
        }
    }

    renderCartUI() {
        const container = document.getElementById('cart-items-container');
        const subtotalEl = document.getElementById('cart-subtotal-display');
        const totalEl = document.getElementById('cart-total-display');
        const btnStep2 = document.getElementById('btn-goto-step-2');

        if (!container) return;

        if (this.items.length === 0) {
            container.innerHTML = `
                <div class="text-center py-16 text-gray-400 space-y-4">
                    <div class="w-20 h-20 mx-auto rounded-full bg-red-50 flex items-center justify-center text-red-400">
                        <i class="fas fa-shopping-basket text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700">Tu pedido está vacío</h3>
                    <p class="text-sm text-gray-500 max-w-xs mx-auto">Selecciona los platos más ricos de nuestra carta para preparar tu pedido.</p>
                </div>
            `;
            if (subtotalEl) subtotalEl.innerText = '$0';
            if (totalEl) totalEl.innerText = '$0';
            if (btnStep2) btnStep2.classList.add('opacity-50', 'pointer-events-none');
            return;
        }

        if (btnStep2) btnStep2.classList.remove('opacity-50', 'pointer-events-none');

        let html = '<div class="space-y-3.5">';
        this.items.forEach(item => {
            html += `
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200/80 shadow-sm flex flex-col space-y-2 relative transition hover:border-red-200">
                    <div class="flex justify-between items-start">
                        <div class="pr-6">
                            ${item.categoryName ? `<span class="inline-block text-[10px] uppercase font-black tracking-wider text-rose-600 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded-md mb-1">${item.categoryName}</span>` : ''}
                            <h4 class="font-bold text-gray-800 text-base leading-tight">${item.productName}</h4>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                ${item.variantName ? `<span class="inline-block bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-md">${item.variantName}</span>` : ''}
                            </div>
                            ${item.garnishName ? `
                                <div class="mt-1.5 inline-flex items-center space-x-1.5 bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-xl shadow-2xs">
                                    <i class="fas fa-bowl-food text-emerald-600 text-xs"></i>
                                    <span>Guarnición: ${item.garnishName}${item.garnishPrice > 0 ? ` (+ $${item.garnishPrice.toLocaleString('es-AR')})` : ''}</span>
                                </div>
                            ` : ''}
                            ${(item.hasCookingOptions || item.cookingMethod) ? `
                                <div class="mt-2 flex flex-wrap items-center gap-1.5 bg-amber-50/90 border border-amber-200/80 p-1.5 rounded-xl">
                                    <span class="text-[11px] font-bold text-amber-900 flex items-center pr-1">
                                        <i class="fas fa-fire-burner text-amber-600 mr-1 text-xs"></i> Cocción:
                                    </span>
                                    ${(item.cookingOptions || ['Horno', 'Frita']).map(opt => {
                                        const cleanOpt = (opt === 'Al Horno' || opt === 'Horno') ? 'Horno' : opt;
                                        const label = formatCookingLabel(cleanOpt);
                                        const isSelected = item.cookingMethod === cleanOpt || (cleanOpt === 'Horno' && (item.cookingMethod === 'Horno' || item.cookingMethod === 'Al Horno'));
                                        return `
                                            <button type="button" onclick="cartManager.setCookingMethod('${item.key}', '${cleanOpt}')"
                                                    class="px-2.5 py-1 text-xs rounded-lg font-black transition-all ${isSelected ? 'bg-amber-600 text-white shadow-xs' : 'bg-white text-gray-600 hover:bg-amber-100/50 hover:text-amber-800 border border-amber-200'}">
                                                ${label}
                                            </button>
                                        `;
                                    }).join('')}
                                </div>
                            ` : ''}
                            ${item.notes ? `<p class="text-xs text-gray-500 italic mt-1.5"><i class="fas fa-pencil-alt text-[10px] mr-1 text-gray-400"></i>${item.notes}</p>` : ''}
                        </div>
                        <button onclick="cartManager.removeItem('${item.key}')" class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition" title="Quitar">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                        <div class="flex items-center space-x-2 bg-white rounded-xl border border-gray-200 px-2 py-1 shadow-xs">
                            <button onclick="cartManager.updateQuantity('${item.key}', -1)" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-red-100 hover:text-red-600 text-gray-600 font-bold flex items-center justify-center transition">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="font-black text-sm w-7 text-center text-gray-800">${item.quantity}</span>
                            <button onclick="cartManager.updateQuantity('${item.key}', 1)" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-green-100 hover:text-green-600 text-gray-600 font-bold flex items-center justify-center transition">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-400 font-medium">$${item.unitPrice.toLocaleString('es-AR')} c/u</div>
                            <div class="text-base font-black text-gray-900">$${item.subtotal.toLocaleString('es-AR')}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;

        const total = this.getTotalAmount();
        const count = this.getTotalCount();
        if (subtotalEl) subtotalEl.innerText = '$' + total.toLocaleString('es-AR');
        if (totalEl) totalEl.innerText = '$' + total.toLocaleString('es-AR');

        const step2Badge = document.getElementById('step2-items-badge');
        const step2Text = document.getElementById('step2-items-text');
        const step2Total = document.getElementById('step2-total-badge');
        if (step2Badge) step2Badge.innerText = count;
        if (step2Text) step2Text.innerText = `${count} ${count === 1 ? 'plato seleccionado' : 'platos seleccionados'}`;
        if (step2Total) step2Total.innerText = '$' + total.toLocaleString('es-AR');
    }

    showToast(message) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            background: '#ffffff',
            color: '#1f2937'
        });
    }
}

// Inicializar gestor de carrito
const cartManager = new CartManager();

// Control de pasos del Carrito (Paso 1: Ver pedido, Paso 2: Datos y WhatsApp)
function goToCartStep(step) {
    const step1 = document.getElementById('cart-step-1');
    const step2 = document.getElementById('cart-step-2');

    if (step === 2) {
        if (cartManager.items.length === 0) {
            cartManager.showToast('Tu carrito está vacío. Agrega platos antes de continuar.');
            return;
        }

        const total = cartManager.getTotalAmount();
        const count = cartManager.getTotalCount();
        const badgeEl = document.getElementById('step2-items-badge');
        const textEl = document.getElementById('step2-items-text');
        const totalEl = document.getElementById('step2-total-badge');

        if (badgeEl) badgeEl.innerText = count;
        if (textEl) textEl.innerText = `${count} ${count === 1 ? 'plato seleccionado' : 'platos seleccionados'}`;
        if (totalEl) totalEl.innerText = '$' + total.toLocaleString('es-AR');

        if (step1) {
            step1.classList.add('hidden');
            step1.classList.remove('flex');
        }
        if (step2) {
            step2.classList.remove('hidden');
            step2.classList.add('flex');
            initCartMap();
            setupAddressAutocomplete();
            if (cartLeafletMap) {
                setTimeout(() => cartLeafletMap.invalidateSize(), 150);
            }
            if (cartGoogleMap && window.google && window.google.maps) {
                setTimeout(() => google.maps.event.trigger(cartGoogleMap, 'resize'), 150);
            }
            initGooglePlacesAutocomplete();
        }
    } else {
        if (step2) {
            step2.classList.add('hidden');
            step2.classList.remove('flex');
        }
        if (step1) {
            step1.classList.remove('hidden');
            step1.classList.add('flex');
        }
    }
}

// Control de apertura/cierre de modal de carrito
function openCartModal() {
    const backdrop = document.getElementById('cart-drawer-backdrop');
    const drawer = document.getElementById('cart-drawer');
    const floatingContainer = document.getElementById('floating-cart-container');
    const floatingBtn = document.getElementById('floating-cart-button');

    // Ocultar botón flotante para que no tape el botón de preparar pedido
    if (floatingContainer) floatingContainer.classList.add('hidden');
    if (floatingBtn) floatingBtn.classList.add('hidden');

    // Bloquear scroll de la página de fondo
    document.body.style.overflow = 'hidden';

    // Iniciar siempre en el Paso 1 (Ver pedido completo)
    goToCartStep(1);

    if (backdrop && drawer) {
        cartManager.renderCartUI();
        backdrop.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            drawer.classList.remove('translate-x-full');
        }, 10);
    }
}

function closeCartModal() {
    const backdrop = document.getElementById('cart-drawer-backdrop');
    const drawer = document.getElementById('cart-drawer');
    const floatingContainer = document.getElementById('floating-cart-container');
    const floatingBtn = document.getElementById('floating-cart-button');

    // Restaurar scroll de la página
    document.body.style.overflow = '';

    if (backdrop && drawer) {
        backdrop.classList.add('opacity-0');
        drawer.classList.add('translate-x-full');
        setTimeout(() => {
            backdrop.classList.add('hidden');
            // Restaurar botón flotante
            if (floatingContainer) floatingContainer.classList.remove('hidden');
            if (floatingBtn) floatingBtn.classList.remove('hidden');
        }, 300);
    }
}

// ==========================================
// CONTROL DE MAPA INTERACTIVO Y PIN DE ENTREGA (GOOGLE MAPS / LEAFLET)
// ==========================================
let cartLeafletMap = null;
let cartLeafletMarker = null;
let cartGoogleMap = null;
let cartGoogleMarker = null;
let isGoogleMapsLoaded = false;
let isGoogleMapsLoading = false;
let isPinConfirmedByUser = false;
let isGpsLocatedByUser = false;
const DEFAULT_CORRIENTES_COORDS = [-27.4692, -58.8306];

function updatePinStatusUi(isConfirmed) {
    isPinConfirmedByUser = isConfirmed;
    const statusText = document.getElementById('cart-map-status-text');
    if (statusText) {
        if (isConfirmed) {
            statusText.innerHTML = `<span class="text-emerald-600 font-bold flex items-center gap-1"><i class="fas fa-check-circle"></i> ¡Ubicación confirmada!</span>`;
        } else {
            statusText.innerHTML = `<span class="text-amber-600 font-bold flex items-center gap-1 animate-pulse"><i class="fas fa-hand-pointer"></i> Toca el mapa para fijar tu casa</span>`;
        }
    }
}

function updateGpsNoticeUi(isLocated) {
    const notice = document.getElementById('gps-mandatory-notice');
    if (!notice) return;
    if (isLocated) {
        notice.className = 'mt-2 flex items-center gap-2 px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-[11px] font-bold transition shadow-xs';
        notice.innerHTML = `
            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">
                <i class="fas fa-check"></i>
            </span>
            <span>✅ ¡Ubicación satelital fijada con éxito!</span>
        `;
    } else {
        notice.className = 'mt-2 flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-xl text-red-800 text-[11px] font-semibold transition shadow-xs';
        notice.innerHTML = `
            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-red-600 text-white flex items-center justify-center text-[10px] animate-bounce">
                <i class="fas fa-arrow-up"></i>
            </span>
            <span><b>Paso obligatorio:</b> Toca el botón rojo <b>"Ubicarme"</b> para detectar tu ubicación satelital.</span>
        `;
    }
}

// Carga dinámica de la Google Maps JavaScript API
function loadGoogleMapsScript(callback) {
    if (window.google && window.google.maps) {
        isGoogleMapsLoaded = true;
        if (callback) callback();
        return;
    }
    const apiKey = window.APP_CONFIG?.googleMapsApiKey;
    if (!apiKey) return;
    if (isGoogleMapsLoading || document.getElementById('google-maps-api-script')) return;

    isGoogleMapsLoading = true;
    const script = document.createElement('script');
    script.id = 'google-maps-api-script';
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places&language=es&region=AR`;
    script.async = true;
    script.defer = true;
    script.onload = () => {
        isGoogleMapsLoaded = true;
        isGoogleMapsLoading = false;
        initGooglePlacesAutocomplete();
        if (callback) callback();
    };
    script.onerror = () => {
        isGoogleMapsLoading = false;
        console.warn('No se pudo cargar Google Maps con la API Key configurada. Se usará el mapa de respaldo.');
    };
    document.head.appendChild(script);
}

// Inicializar Autocompletado predictivo de Google Places (calles y alturas exactas)
function initGooglePlacesAutocomplete() {
    if (!window.google || !window.google.maps || !window.google.maps.places) return;

    const addressInput = document.getElementById('order-customer-address');
    const mapSearchInput = document.getElementById('cart-map-search-input');

    const options = {
        bounds: { north: -27.420, south: -27.580, west: -58.900, east: -58.720 },
        strictBounds: true,
        componentRestrictions: { country: 'ar' },
        fields: ['geometry', 'name', 'formatted_address', 'address_components']
    };

    [addressInput, mapSearchInput].forEach(input => {
        if (!input || input.dataset.gmapsAutocompleteInit === 'true') return;
        input.dataset.gmapsAutocompleteInit = 'true';

        const ac = new google.maps.places.Autocomplete(input, options);
        ac.addListener('place_changed', () => {
            const place = ac.getPlace();
            if (place.geometry && place.geometry.location) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                const cleanAddress = (place.formatted_address || place.name || input.value)
                    .replace(', Corrientes, Argentina', '')
                    .replace(', Argentina', '');

                if (addressInput) addressInput.value = cleanAddress;
                if (mapSearchInput) mapSearchInput.value = cleanAddress;

                const wrapper = document.getElementById('cart-map-wrapper');
                if (wrapper && wrapper.classList.contains('hidden')) {
                    toggleCartMap();
                }

                initCartMap(lat, lng);
                setCartPin(lat, lng, true, false);
                updatePinStatusUi(false);

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: '📍 Calle ubicada',
                    text: 'Toca el mapa para confirmar la puerta exacta de tu casa.',
                    showConfirmButton: false,
                    timer: 3500
                });
            }
        });
    });
}

function toggleCartMap() {
    const wrapper = document.getElementById('cart-map-wrapper');
    const btnText = document.getElementById('toggle-map-btn-text');
    if (!wrapper) return;

    if (wrapper.classList.contains('hidden')) {
        wrapper.classList.remove('hidden');
        if (btnText) btnText.innerText = 'Ocultar Mapa';
        setTimeout(() => {
            initCartMap();
        }, 80);
    } else {
        wrapper.classList.add('hidden');
        if (btnText) btnText.innerText = 'Abrir Mapa';
    }
}

function initCartMap(customLat = null, customLng = null) {
    const mapEl = document.getElementById('cart-leaflet-map');
    if (!mapEl) return;

    const latInput = document.getElementById('order-delivery-lat');
    const lngInput = document.getElementById('order-delivery-lng');

    let initialLat = customLat || (latInput?.value ? parseFloat(latInput.value) : null) || DEFAULT_CORRIENTES_COORDS[0];
    let initialLng = customLng || (lngInput?.value ? parseFloat(lngInput.value) : null) || DEFAULT_CORRIENTES_COORDS[1];

    // MODO 1: GOOGLE MAPS OFICIAL (Si está disponible)
    if (window.google && window.google.maps) {
        if (!cartGoogleMap) {
            mapEl.innerHTML = '';
            cartGoogleMap = new google.maps.Map(mapEl, {
                center: { lat: initialLat, lng: initialLng },
                zoom: 17,
                mapTypeControl: true,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                },
                streetViewControl: false,
                fullscreenControl: false,
                zoomControl: true
            });

            cartGoogleMap.addListener('click', function(e) {
                setCartPin(e.latLng.lat(), e.latLng.lng(), false, true);
                updatePinStatusUi(true);
            });
        } else {
            if (customLat && customLng) {
                cartGoogleMap.panTo({ lat: customLat, lng: customLng });
                cartGoogleMap.setZoom(17);
            }
        }

        if (latInput?.value && lngInput?.value && !cartGoogleMarker) {
            setCartPin(parseFloat(latInput.value), parseFloat(lngInput.value), false, false);
        }
        return;
    }

    // MODO 2: LEAFLET / OPENSTREETMAP (Fallback sin API Key)
    if (typeof L === 'undefined') return;

    if (!cartLeafletMap) {
        cartLeafletMap = L.map('cart-leaflet-map', {
            zoomControl: true,
            attributionControl: false
        }).setView([initialLat, initialLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(cartLeafletMap);

        cartLeafletMap.on('click', function(e) {
            setCartPin(e.latlng.lat, e.latlng.lng(), false, true);
            updatePinStatusUi(true);
        });
    } else {
        cartLeafletMap.invalidateSize();
        if (customLat && customLng) {
            cartLeafletMap.setView([customLat, customLng], 17);
        }
    }

    if (latInput?.value && lngInput?.value && !cartLeafletMarker) {
        setCartPin(parseFloat(latInput.value), parseFloat(lngInput.value), false, false);
    }
}

function setCartPin(lat, lng, centerMap = false, doReverseGeocode = false) {
    const formattedLat = Number(lat).toFixed(6);
    const formattedLng = Number(lng).toFixed(6);
    const googleMapsUrl = `https://maps.google.com/?q=${formattedLat},${formattedLng}`;

    const latInput = document.getElementById('order-delivery-lat');
    const lngInput = document.getElementById('order-delivery-lng');
    const mapUrlInput = document.getElementById('order-delivery-map-url');

    if (latInput) latInput.value = formattedLat;
    if (lngInput) lngInput.value = formattedLng;
    if (mapUrlInput) mapUrlInput.value = googleMapsUrl;

    const openGmapsBtn = document.getElementById('btn-open-external-gmaps');
    if (openGmapsBtn) {
        openGmapsBtn.href = googleMapsUrl;
    }

    // MODO GOOGLE MAPS
    if (cartGoogleMap && window.google && window.google.maps) {
        const pos = { lat: parseFloat(formattedLat), lng: parseFloat(formattedLng) };
        if (!cartGoogleMarker) {
            cartGoogleMarker = new google.maps.Marker({
                position: pos,
                map: cartGoogleMap,
                draggable: true,
                animation: google.maps.Animation.DROP,
                title: 'Tu ubicación de entrega'
            });

            cartGoogleMarker.addListener('dragend', function(e) {
                setCartPin(e.latLng.lat(), e.latLng.lng(), false, true);
            });
        } else {
            cartGoogleMarker.setPosition(pos);
        }

        if (centerMap) {
            cartGoogleMap.panTo(pos);
            cartGoogleMap.setZoom(17);
        }
    } else if (cartLeafletMap && typeof L !== 'undefined') {
        // MODO LEAFLET
        if (!cartLeafletMarker) {
            const redIcon = L.divIcon({
                className: 'custom-leaflet-pin',
                html: `<div style="position: relative; width: 34px; height: 44px; transform: translate(-17px, -44px); cursor: grab;">
                    <svg viewBox="0 0 384 512" width="34" height="44" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.45));">
                        <path fill="#dc2626" stroke="#991b1b" stroke-width="8" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0z"/>
                        <circle cx="192" cy="192" r="68" fill="#ffffff"/>
                        <circle cx="192" cy="192" r="38" fill="#dc2626"/>
                    </svg>
                </div>`,
                iconSize: [0, 0],
                iconAnchor: [0, 0]
            });

            cartLeafletMarker = L.marker([lat, lng], {
                draggable: true,
                icon: redIcon
            }).addTo(cartLeafletMap);

            cartLeafletMarker.on('dragend', function(e) {
                const newPos = e.target.getLatLng();
                setCartPin(newPos.lat, newPos.lng, false, true);
                updatePinStatusUi(true);
            });
        } else {
            cartLeafletMarker.setLatLng([lat, lng]);
        }

        if (centerMap) {
            cartLeafletMap.setView([lat, lng], 17);
        }
    }

    const badge = document.getElementById('cart-map-status-badge');
    const coordsLabel = document.getElementById('cart-map-coords-label');
    const extLink = document.getElementById('cart-map-external-link');

    if (badge) {
        badge.classList.remove('hidden');
        badge.classList.add('flex');
    }
    if (coordsLabel) {
        coordsLabel.innerText = `Pin fijado (${Number(lat).toFixed(4)}, ${Number(lng).toFixed(4)})`;
    }
    if (extLink) {
        extLink.href = googleMapsUrl;
    }

    if (doReverseGeocode) {
        reverseGeocode(lat, lng);
    }

    handleAddressOrPinChanged();
}

function reverseGeocode(lat, lng) {
    // Si Google Maps Geocoder está disponible:
    if (window.google && window.google.maps) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, function(results, status) {
            if (status === 'OK' && results && results[0]) {
                const cleanAddr = results[0].formatted_address
                    .replace(', Corrientes, Argentina', '')
                    .replace(', Argentina', '');

                const addressInput = document.getElementById('order-customer-address');
                if (addressInput && (!addressInput.value.trim() || addressInput.dataset.autoFilled === 'true')) {
                    addressInput.value = cleanAddr;
                    addressInput.dataset.autoFilled = 'true';
                    handleAddressOrPinChanged();
                }
            }
        });
        return;
    }

    // Fallback con Nominatim
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
        headers: { 'Accept-Language': 'es' }
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.address) {
            const road = data.address.road || data.address.pedestrian || data.address.footway || '';
            const houseNumber = data.address.house_number || '';
            const neighbourhood = data.address.neighbourhood || data.address.suburb || '';

            if (road) {
                let formatted = road;
                if (houseNumber) formatted += ' ' + houseNumber;
                if (neighbourhood && !formatted.includes(neighbourhood)) formatted += ', B° ' + neighbourhood;

                const addressInput = document.getElementById('order-customer-address');
                if (addressInput && (!addressInput.value.trim() || addressInput.dataset.autoFilled === 'true')) {
                    addressInput.value = formatted;
                    addressInput.dataset.autoFilled = 'true';
                    handleAddressOrPinChanged();
                }
            }
        }
    })
    .catch(() => {});
}

// Extractor y detector de enlaces de Google Maps y coordenadas directas
function parseGoogleMapsUrlOrCoords(text) {
    if (!text || typeof text !== 'string') return null;
    text = text.trim();

    // 1. Coordenadas directas: -27.4942, -58.8417
    const rawMatch = text.match(/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/);
    if (rawMatch) {
        return { lat: parseFloat(rawMatch[1]), lng: parseFloat(rawMatch[2]) };
    }

    // 2. Parámetro ?q=lat,lng
    const qMatch = text.match(/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (qMatch) {
        return { lat: parseFloat(qMatch[1]), lng: parseFloat(qMatch[2]) };
    }

    // 3. Patrón @lat,lng,zoom
    const atMatch = text.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (atMatch) {
        return { lat: parseFloat(atMatch[1]), lng: parseFloat(atMatch[2]) };
    }

    // 4. Patrón /place/lat,lng o /search/lat,lng
    const placeMatch = text.match(/(?:place|search)\/(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (placeMatch) {
        return { lat: parseFloat(placeMatch[1]), lng: parseFloat(placeMatch[2]) };
    }

    // 5. geo:lat,lng
    const geoMatch = text.match(/geo:(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (geoMatch) {
        return { lat: parseFloat(geoMatch[1]), lng: parseFloat(geoMatch[2]) };
    }

    return null;
}

// Modal interactivo para pegar enlace de Google Maps
function promptPasteGoogleMapsLink() {
    Swal.fire({
        title: 'Pegar Enlace de Google Maps',
        text: 'Copia el enlace de tu ubicación desde Google Maps o WhatsApp y pégalo aquí:',
        input: 'text',
        inputPlaceholder: 'https://maps.app.goo.gl/... o https://maps.google.com/?q=...',
        showCancelButton: true,
        confirmButtonText: '📍 Fijar Ubicación',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Por favor pega un enlace o coordenadas válidas.';
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            handleGoogleMapsUrlInput(result.value.trim());
        }
    });
}

function handleGoogleMapsUrlInput(url) {
    const directCoords = parseGoogleMapsUrlOrCoords(url);
    if (directCoords) {
        const wrapper = document.getElementById('cart-map-wrapper');
        if (wrapper && wrapper.classList.contains('hidden')) {
            toggleCartMap();
        }
        initCartMap(directCoords.lat, directCoords.lng);
        setCartPin(directCoords.lat, directCoords.lng, true, true);
        updatePinStatusUi(true);
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '📍 ¡Ubicación fijada desde Google Maps!',
            showConfirmButton: false,
            timer: 2500
        });
        return;
    }

    // Si es un enlace acortado o complejo, resolver en el servidor
    const resolveEndpoint = window.APP_CONFIG?.resolveMapsUrl || '/resolver-mapa';
    Swal.fire({
        title: 'Procesando enlace...',
        text: 'Obteniendo coordenadas exactas desde Google Maps',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch(resolveEndpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.APP_CONFIG?.csrfToken || ''
        },
        body: JSON.stringify({ url: url })
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (data.success && data.lat && data.lng) {
            const wrapper = document.getElementById('cart-map-wrapper');
            if (wrapper && wrapper.classList.contains('hidden')) {
                toggleCartMap();
            }
            initCartMap(data.lat, data.lng);
            setCartPin(data.lat, data.lng, true, true);
            updatePinStatusUi(true);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '📍 ¡Ubicación fijada desde Google Maps!',
                showConfirmButton: false,
                timer: 2500
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No se pudo leer la ubicación',
                text: data.message || 'Intenta buscar el nombre de la calle o toca en el mapa.',
                confirmButtonColor: '#dc2626'
            });
        }
    })
    .catch(() => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No pudimos verificar el enlace. Puedes buscar tu calle o tocar directamente en el mapa.',
            confirmButtonColor: '#dc2626'
        });
    });
}

// Bounding Box estricto para Corrientes Capital, Argentina
const CORRIENTES_VIEWBOX = '-58.89,-27.42,-58.72,-27.56';
let addressAutocompleteTimeout = null;

// Autocompletado profesional en tiempo real para Corrientes Capital (Photon / OSM)
function setupAddressAutocomplete() {
    const input = document.getElementById('order-customer-address');
    const dropdown = document.getElementById('address-autocomplete-dropdown');
    if (!input || !dropdown) return;

    if (input.dataset.autocompleteReady === 'true') return;
    input.dataset.autocompleteReady = 'true';

    input.addEventListener('input', () => {
        clearTimeout(addressAutocompleteTimeout);
        const query = input.value.trim();

        if (query.length < 3) {
            dropdown.innerHTML = '';
            dropdown.classList.add('hidden');
            return;
        }

        addressAutocompleteTimeout = setTimeout(async () => {
            try {
                // Photon API con bias a Corrientes Capital (-27.4692, -58.8306)
                const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&lat=-27.4692&lon=-58.8306&limit=8`;
                const res = await fetch(url);
                const data = await res.json();

                if (!data || !data.features || data.features.length === 0) {
                    dropdown.innerHTML = '';
                    dropdown.classList.add('hidden');
                    return;
                }

                // Filtrar para dar máxima prioridad a Corrientes Capital y Gran Corrientes
                const corrientesResults = data.features.filter(f => {
                    const coords = f.geometry?.coordinates;
                    if (!coords) return false;
                    const lon = coords[0];
                    const lat = coords[1];
                    const inBBox = (lat >= -27.58 && lat <= -27.42 && lon >= -58.90 && lon <= -58.72);
                    const isCity = f.properties?.city === 'Corrientes' || f.properties?.county === 'Departamento Capital';
                    return inBBox || isCity;
                });

                const listToRender = corrientesResults;

                if (listToRender.length === 0) {
                    dropdown.innerHTML = '';
                    dropdown.classList.add('hidden');
                    return;
                }

                dropdown.innerHTML = '';
                listToRender.forEach(f => {
                    const props = f.properties || {};
                    const street = props.name || props.street || '';
                    const locality = props.locality || props.district || props.city || 'Corrientes Capital';
                    const houseNumber = props.housenumber || '';
                    const coords = f.geometry.coordinates; // [lon, lat]

                    if (!street) return;

                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'w-full text-left px-3.5 py-2.5 hover:bg-red-50/70 transition flex items-center space-x-2.5 cursor-pointer text-xs border-b border-gray-100 last:border-0';
                    item.innerHTML = `
                        <div class="w-6 h-6 rounded-full bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0 text-[11px]">
                            <i class="fas fa-map-pin"></i>
                        </div>
                        <div class="truncate">
                            <span class="font-bold text-slate-800 block truncate text-xs">${street} ${houseNumber}</span>
                            <span class="text-[10px] text-slate-500 block truncate">${locality}, Corrientes</span>
                        </div>
                    `;

                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();

                        // Si el usuario ya escribió un número de puerta (ej: 1043), conservarlo
                        const userNumberMatch = input.value.match(/\d+/);
                        const userNumber = userNumberMatch ? userNumberMatch[0] : '';
                        let finalAddress = street;
                        if (userNumber && !street.includes(userNumber)) {
                            finalAddress += ' ' + userNumber;
                        } else if (houseNumber && !street.includes(houseNumber)) {
                            finalAddress += ' ' + houseNumber;
                        }

                        input.value = finalAddress;
                        input.dataset.lastGeocoded = finalAddress;
                        dropdown.innerHTML = '';
                        dropdown.classList.add('hidden');

                        const lat = coords[1];
                        const lng = coords[0];

                        initCartMap(lat, lng);
                        setCartPin(lat, lng, true, false);
                        updatePinStatusUi(false);
                    });

                    dropdown.appendChild(item);
                });

                dropdown.classList.remove('hidden');
            } catch (err) {
                console.error('Error in address autocomplete:', err);
            }
        }, 220);
    });

    // Cerrar sugerencias al hacer clic afuera
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

function handleAddressInputBlur() {
    const addressInput = document.getElementById('order-customer-address');
    const dropdown = document.getElementById('address-autocomplete-dropdown');

    // Cerrar menú de sugerencias tras breve timeout para permitir click
    setTimeout(() => {
        if (dropdown) dropdown.classList.add('hidden');
    }, 250);

    if (!addressInput) return;
    const query = addressInput.value.trim();

    if (query.length < 3) return;

    // Si el usuario ya confirmó su pin manualmente o ya buscamos esta dirección, no repetir
    if (isPinConfirmedByUser) return;
    if (addressInput.dataset.lastGeocoded === query) return;

    addressInput.dataset.lastGeocoded = query;
    searchAddressOnMap(query, true);
}

// Búsqueda robusta y profesional en Corrientes Capital
async function searchAddressOnMap(customQuery = null, isSilent = false) {
    const addressInput = document.getElementById('order-customer-address');
    const query = (customQuery !== null ? customQuery : (addressInput ? addressInput.value : '')).trim();

    if (!query) return;

    // Extraer nombre de calle y numeración
    const streetOnly = query.replace(/\d+/g, '').replace(/b°|barrio/gi, '').trim();

    const statusText = document.getElementById('cart-map-status-text');
    if (statusText) {
        statusText.innerHTML = `<span class="text-blue-600 font-semibold flex items-center gap-1"><i class="fas fa-spinner fa-spin"></i> Localizando en Corrientes...</span>`;
    }

    // PASO 1: Probar con Photon API (Soporta errores ortográficos y es ultra rápida)
    try {
        const photonUrl = `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&lat=-27.4692&lon=-58.8306&limit=5`;
        const res = await fetch(photonUrl);
        const data = await res.json();

        if (data && data.features && data.features.length > 0) {
            // Filtrar las que pertenezcan a Corrientes Capital
            const match = data.features.find(f => {
                const coords = f.geometry?.coordinates;
                if (!coords) return false;
                const lon = coords[0];
                const lat = coords[1];
                return (lat >= -27.58 && lat <= -27.42 && lon >= -58.90 && lon <= -58.72) ||
                       (f.properties?.city === 'Corrientes' || f.properties?.county === 'Departamento Capital');
            });

            if (match) {
                const lat = match.geometry.coordinates[1];
                const lng = match.geometry.coordinates[0];
                applyFoundLocation(lat, lng, isSilent);
                return;
            }
        }
    } catch (err) {
        console.warn('Photon lookup fallback:', err);
    }

    // PASO 2: Si el número no estaba registrado, buscar solo el nombre de la calle en Photon
    if (streetOnly.length >= 3 && streetOnly !== query) {
        try {
            const photonStreetUrl = `https://photon.komoot.io/api/?q=${encodeURIComponent(streetOnly)}&lat=-27.4692&lon=-58.8306&limit=5`;
            const res = await fetch(photonStreetUrl);
            const data = await res.json();

            if (data && data.features && data.features.length > 0) {
                const match = data.features.find(f => {
                    const coords = f.geometry?.coordinates;
                    if (!coords) return false;
                    const lon = coords[0];
                    const lat = coords[1];
                    return (lat >= -27.58 && lat <= -27.42 && lon >= -58.90 && lon <= -58.72) ||
                           (f.properties?.city === 'Corrientes' || f.properties?.county === 'Departamento Capital');
                });

                if (match) {
                    const lat = match.geometry.coordinates[1];
                    const lng = match.geometry.coordinates[0];
                    applyFoundLocation(lat, lng, isSilent, true);
                    return;
                }
            }
        } catch (err) {
            console.warn('Photon street lookup fallback:', err);
        }
    }

    // PASO 3: Fallback a Nominatim delimitado estrictamente a Corrientes Capital
    const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Corrientes, Argentina')}&limit=1&countrycodes=ar&viewbox=${CORRIENTES_VIEWBOX}&bounded=1`;

    try {
        const res = await fetch(nominatimUrl, {
            headers: { 'Accept-Language': 'es', 'User-Agent': 'RotiseriaLaAbuelaApp/1.0' }
        });
        const data = await res.json();

        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            applyFoundLocation(lat, lng, isSilent);
            return;
        }

        // Reintento Nominatim solo calle
        if (streetOnly.length >= 3 && streetOnly !== query) {
            const nomStreetUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(streetOnly + ', Corrientes, Argentina')}&limit=1&countrycodes=ar&viewbox=${CORRIENTES_VIEWBOX}&bounded=1`;
            const rStreet = await fetch(nomStreetUrl, {
                headers: { 'Accept-Language': 'es', 'User-Agent': 'RotiseriaLaAbuelaApp/1.0' }
            });
            const dStreet = await rStreet.json();
            if (dStreet && dStreet.length > 0) {
                const lat = parseFloat(dStreet[0].lat);
                const lng = parseFloat(dStreet[0].lon);
                applyFoundLocation(lat, lng, isSilent, true);
                return;
            }
        }
    } catch (err) {
        console.error('Nominatim search error:', err);
    }

    if (statusText) {
        statusText.innerHTML = `<span class="text-amber-600 font-medium">Calle aproximada • Toca en el mapa</span>`;
    }
}

function applyFoundLocation(lat, lng, isSilent = false, isApproxStreet = false) {
    initCartMap(lat, lng);
    setCartPin(lat, lng, true, false);
    updatePinStatusUi(false);

    if (cartLeafletMap) {
        cartLeafletMap.flyTo([lat, lng], 17, { duration: 1 });
        setTimeout(() => cartLeafletMap.invalidateSize(), 200);
    }

    if (!isSilent) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: isApproxStreet ? '📍 Calle ubicada' : '📍 Zona localizada',
            text: '👉 Toca el mapa para confirmar la puerta exacta de tu casa.',
            showConfirmButton: false,
            timer: 3500
        });
    }
}

function clearCartMapPin() {
    if (cartGoogleMarker) {
        cartGoogleMarker.setMap(null);
        cartGoogleMarker = null;
    }
    if (cartLeafletMarker && cartLeafletMap) {
        cartLeafletMap.removeLayer(cartLeafletMarker);
        cartLeafletMarker = null;
    }

    const latInput = document.getElementById('order-delivery-lat');
    const lngInput = document.getElementById('order-delivery-lng');
    const mapUrlInput = document.getElementById('order-delivery-map-url');

    if (latInput) latInput.value = '';
    if (lngInput) lngInput.value = '';
    if (mapUrlInput) mapUrlInput.value = '';

    const badge = document.getElementById('cart-map-status-badge');
    if (badge) {
        badge.classList.add('hidden');
        badge.classList.remove('flex');
    }

    updatePinStatusUi(false);
    isGpsLocatedByUser = false;
    updateGpsNoticeUi(false);
    const btn = document.getElementById('btn-cart-gps-locate');
    if (btn) {
        btn.innerHTML = `
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-90"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
            </span>
            <i class="fas fa-crosshairs text-sm"></i>
            <span class="text-xs font-bold tracking-tight">Ubicarme</span>
        `;
        btn.classList.remove('from-emerald-600', 'to-teal-600', 'ring-emerald-400');
        btn.classList.add('from-red-600', 'to-rose-600', 'ring-red-400/70', 'animate-pulse');
    }
    handleAddressOrPinChanged();
}

function locateUserGPS() {
    if (!navigator.geolocation) {
        Swal.fire({
            icon: 'warning',
            title: 'GPS no disponible',
            text: 'Tu navegador o dispositivo no soporta geolocalización.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const btn = document.getElementById('btn-cart-gps-locate');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i><span class="text-xs font-bold">Ubicando...</span>';
        btn.classList.add('opacity-75', 'pointer-events-none');
    }

    const wrapper = document.getElementById('cart-map-wrapper');
    if (wrapper && wrapper.classList.contains('hidden')) {
        toggleCartMap();
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            isGpsLocatedByUser = true;
            isPinConfirmedByUser = true;

            if (btn) {
                btn.innerHTML = '<i class="fas fa-check text-xs"></i><span class="text-xs font-bold">Ubicado</span>';
                btn.classList.remove('opacity-75', 'pointer-events-none', 'from-red-600', 'to-rose-600', 'ring-red-400/70', 'animate-pulse');
                btn.classList.add('from-emerald-600', 'to-teal-600', 'ring-emerald-400');
            }

            updateGpsNoticeUi(true);
            isGpsLocatedByUser = true;
            isPinConfirmedByUser = true;

            if (btn) {
                btn.innerHTML = '<i class="fas fa-check text-xs"></i><span class="text-xs font-bold">Ubicado</span>';
                btn.classList.remove('opacity-75', 'pointer-events-none', 'from-red-600', 'to-rose-600', 'ring-red-400/70', 'animate-pulse');
                btn.classList.add('from-emerald-600', 'to-teal-600', 'ring-emerald-400');
            }

            updateGpsNoticeUi(true);
            initCartMap(lat, lng);
            setCartPin(lat, lng, true, true);
            updatePinStatusUi(true);

            if (cartGoogleMap) {
                cartGoogleMap.panTo({ lat, lng });
                cartGoogleMap.setZoom(18);
            } else if (cartLeafletMap) {
                cartLeafletMap.setView([lat, lng], 17);
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '📍 ¡Ubicación GPS fijada con éxito!',
                showConfirmButton: false,
                timer: 2200
            });
        },
        function(error) {
            if (btn) {
                btn.innerHTML = `
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-90"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                    </span>
                    <i class="fas fa-crosshairs text-sm"></i>
                    <span class="text-xs font-bold tracking-tight">Ubicarme</span>
                `;
                btn.classList.remove('opacity-75', 'pointer-events-none');
            }

            const addressInput = document.getElementById('order-customer-address');
            const hasAddress = addressInput && addressInput.value.trim().length > 0;
            const isHttp = window.location.protocol === 'http:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';

            if (isHttp && error.code === error.PERMISSION_DENIED) {
                Swal.fire({
                    icon: 'info',
                    title: 'Geolocalización por GPS',
                    text: 'En conexiones HTTP de prueba (como http://192.168.1.2), los navegadores restringen el GPS satelital por seguridad. Al usar HTTPS en producción funcionará de forma automática. Mientras tanto, puedes usar el botón de buscar dirección o tocar en el mapa.',
                    showCancelButton: hasAddress,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#2563eb',
                    confirmButtonText: 'Entendido',
                    cancelButtonText: hasAddress ? `Buscar "${addressInput.value.trim()}"` : 'Buscar dirección'
                }).then((res) => {
                    if (res.isDismissed && res.dismiss === Swal.DismissReason.cancel && hasAddress) {
                        searchAddressOnMap();
                    }
                });
            } else {
                let errorMsg = 'No pudimos obtener la señal de GPS. Puedes buscar tu calle con la lupa o tocar en el mapa.';
                if (error.code === error.PERMISSION_DENIED) {
                    errorMsg = 'Permiso de ubicación denegado en tu navegador. Puedes buscar tu calle con la lupa o tocar directamente en el mapa.';
                }
                Swal.fire({
                    icon: 'info',
                    title: 'Ubicación GPS',
                    text: errorMsg,
                    showCancelButton: hasAddress,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#2563eb',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: hasAddress ? `Buscar "${addressInput.value.trim()}"` : 'Buscar dirección'
                }).then((res) => {
                    if (res.isDismissed && res.dismiss === Swal.DismissReason.cancel && hasAddress) {
                        searchAddressOnMap();
                    }
                });
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 12000,
            maximumAge: 0
        }
    );
}

function handleAddressOrPinChanged() {
    const addressInput = document.getElementById('order-customer-address');
    const mapUrlInput = document.getElementById('order-delivery-map-url');
    const asterisk = document.getElementById('address-required-asterisk');

    // Auto-detección si el usuario pegó un enlace o coordenadas directamente en el campo de texto
    if (addressInput && addressInput.value) {
        const val = addressInput.value.trim();
        const coords = parseGoogleMapsUrlOrCoords(val);
        if (coords && (!mapUrlInput || !mapUrlInput.value)) {
            const wrapper = document.getElementById('cart-map-wrapper');
            if (wrapper && wrapper.classList.contains('hidden')) toggleCartMap();
            initCartMap(coords.lat, coords.lng);
            setCartPin(coords.lat, coords.lng, true, true);
        }
    }

    const hasAddress = addressInput && addressInput.value.trim().length > 0;
    const hasPin = mapUrlInput && mapUrlInput.value.trim().length > 0;

    if (asterisk) {
        if (hasPin) {
            asterisk.innerText = ' (opcional con pin)';
            asterisk.className = 'text-[10px] font-normal text-emerald-600 lowercase';
        } else {
            asterisk.innerText = '*';
            asterisk.className = 'text-red-500';
        }
    }
}

function toggleAddressField(isDelivery) {
    const container = document.getElementById('delivery-address-container');
    const addressInput = document.getElementById('order-customer-address');
    if (container) {
        if (isDelivery) {
            container.classList.remove('hidden');
            if (cartGoogleMap && window.google && window.google.maps) {
                setTimeout(() => google.maps.event.trigger(cartGoogleMap, 'resize'), 100);
            } else if (cartLeafletMap) {
                setTimeout(() => cartLeafletMap.invalidateSize(), 100);
            }
        } else {
            container.classList.add('hidden');
        }
    }
}

// Modal de opciones / variantes, cocción y guarniciones
function handleAddToCartClick(product) {
    const hasVariants = product.variants && product.variants.length > 0;
    const hasCooking = !!product.has_cooking_options;
    const hasGarnishes = !!(product.garnishes && product.garnishes.length > 0);

    if (hasVariants || hasCooking || hasGarnishes) {
        currentSelectedProductForModal = product;
        document.getElementById('variant-modal-title').innerText = product.name;
        
        let descParts = [];
        if (hasVariants) descParts.push('tamaño/variante');
        if (hasCooking) descParts.push('tipo de cocción');
        if (hasGarnishes) descParts.push('guarnición');
        document.getElementById('variant-modal-desc').innerText = product.description || `Elige tu ${descParts.join(' y ')}:`;

        // Contenedor de variantes de tamaño
        const container = document.getElementById('variant-options-container');
        if (hasVariants) {
            container.classList.remove('hidden');
            let html = '';
            product.variants.forEach((v, index) => {
                const formattedPrice = '$' + parseFloat(v.price).toLocaleString('es-AR');
                html += `
                    <label class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-gray-200 cursor-pointer transition hover:bg-purple-50 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/70">
                        <div class="flex items-center space-x-3">
                            <input type="radio" name="selected_variant_option" value="${index}" ${index === 0 ? 'checked' : ''} onchange="updateModalTotalPrice()" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <span class="font-bold text-gray-800 text-sm sm:text-base">${v.name}</span>
                        </div>
                        <span class="font-black text-purple-700 text-base sm:text-lg">${formattedPrice}</span>
                    </label>
                `;
            });
            container.innerHTML = html;
        } else {
            container.classList.add('hidden');
            container.innerHTML = '';
        }

        // Contenedor de opciones de cocción (Horno / Freír)
        const cookingContainer = document.getElementById('variant-cooking-container');
        const cookingOptionsEl = document.getElementById('variant-cooking-options');
        if (hasCooking && cookingContainer && cookingOptionsEl) {
            cookingContainer.classList.remove('hidden');
            let availableCooking = ['Horno', 'Frita'];
            if (product.cooking_options) {
                if (Array.isArray(product.cooking_options)) {
                    availableCooking = product.cooking_options;
                } else if (typeof product.cooking_options === 'string') {
                    try {
                        availableCooking = JSON.parse(product.cooking_options);
                    } catch (e) {
                        availableCooking = ['Horno', 'Frita'];
                    }
                }
            }

            const cleanAvailableCooking = availableCooking.map(opt => (opt === 'Al Horno' || opt === 'Horno') ? 'Horno' : opt);
            const uniqueCooking = [...new Set(cleanAvailableCooking)];

            let cookingHtml = '';
            uniqueCooking.forEach((opt, idx) => {
                const label = formatCookingLabel(opt);
                cookingHtml += `
                    <label class="flex items-center justify-between p-3 rounded-2xl border-2 border-gray-200 cursor-pointer transition hover:bg-amber-50 has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50">
                        <div class="flex items-center space-x-2.5">
                            <input type="radio" name="selected_cooking_option" value="${opt}" ${idx === 0 ? 'checked' : ''} class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                            <span class="font-bold text-gray-800 text-xs sm:text-sm">${label}</span>
                        </div>
                    </label>
                `;
            });
            cookingOptionsEl.innerHTML = cookingHtml;
        } else if (cookingContainer) {
            cookingContainer.classList.add('hidden');
            if (cookingOptionsEl) cookingOptionsEl.innerHTML = '';
        }

        // Contenedor de opciones de Guarnición
        const garnishContainer = document.getElementById('variant-garnish-container');
        const garnishOptionsEl = document.getElementById('variant-garnish-options');
        if (hasGarnishes && garnishContainer && garnishOptionsEl) {
            garnishContainer.classList.remove('hidden');
            let garnishHtml = '';
            product.garnishes.forEach((g, idx) => {
                const priceExtra = parseFloat(g.price || 0);
                const priceLabel = priceExtra > 0 ? `+ $${priceExtra.toLocaleString('es-AR')}` : 'Incluida ($0)';
                const imgSrc = g.image_path ? (g.image_path.startsWith('http') || g.image_path.startsWith('/') ? g.image_path : '/' + g.image_path) : null;

                garnishHtml += `
                    <label class="flex items-center justify-between p-3 rounded-2xl border-2 border-gray-200 cursor-pointer transition hover:bg-emerald-50 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/70">
                        <div class="flex items-center space-x-3 pr-2">
                            <input type="radio" name="selected_garnish_option" value="${idx}" ${idx === 0 ? 'checked' : ''} onchange="updateModalTotalPrice()" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            ${imgSrc ? `
                                <img src="${imgSrc}" alt="${g.name}" class="w-12 h-12 rounded-full object-cover border-2 border-emerald-400 shadow-xs flex-shrink-0">
                            ` : `
                                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold flex-shrink-0">
                                    <i class="fas fa-bowl-food text-base"></i>
                                </div>
                            `}
                            <div>
                                <span class="font-bold text-gray-800 text-sm block leading-tight">${g.name}</span>
                                ${g.description ? `<span class="text-xs text-gray-500 block leading-tight mt-0.5">${g.description}</span>` : ''}
                            </div>
                        </div>
                        <span class="font-bold text-xs sm:text-sm ${priceExtra > 0 ? 'text-emerald-800 bg-emerald-100/90 border border-emerald-200' : 'text-gray-500 bg-gray-100'} px-2.5 py-1 rounded-xl flex-shrink-0">
                            ${priceLabel}
                        </span>
                    </label>
                `;
            });
            garnishOptionsEl.innerHTML = garnishHtml;
        } else if (garnishContainer) {
            garnishContainer.classList.add('hidden');
            if (garnishOptionsEl) garnishOptionsEl.innerHTML = '';
        }

        // Función para actualizar en vivo el botón con el precio total calculado
        window.updateModalTotalPrice = function() {
            let basePrice = 0;
            if (hasVariants) {
                const selectedRadio = document.querySelector('input[name="selected_variant_option"]:checked');
                if (selectedRadio && product.variants[parseInt(selectedRadio.value)]) {
                    basePrice = parseFloat(product.variants[parseInt(selectedRadio.value)].price);
                }
            } else {
                basePrice = parseFloat(product.price || 0);
            }

            let extraGarnish = 0;
            if (hasGarnishes) {
                const selectedGarnishRadio = document.querySelector('input[name="selected_garnish_option"]:checked');
                if (selectedGarnishRadio && product.garnishes[parseInt(selectedGarnishRadio.value)]) {
                    extraGarnish = parseFloat(product.garnishes[parseInt(selectedGarnishRadio.value)].price || 0);
                }
            }

            const currentTotal = basePrice + extraGarnish;
            const btnTextEl = document.getElementById('variant-confirm-btn-text');
            if (btnTextEl) {
                btnTextEl.innerText = `Añadir al Pedido — $${currentTotal.toLocaleString('es-AR')}`;
            }
        };

        // Actualizar total inicial del botón
        updateModalTotalPrice();

        document.getElementById('variant-item-notes').value = '';

        const modal = document.getElementById('variant-modal');
        const card = document.getElementById('variant-modal-card');
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
        }, 10);

        document.getElementById('variant-add-confirm-btn').onclick = () => {
            let variant = null;
            if (hasVariants) {
                const selectedRadio = document.querySelector('input[name="selected_variant_option"]:checked');
                if (selectedRadio) {
                    variant = product.variants[parseInt(selectedRadio.value)];
                }
            }

            let cookingMethod = null;
            if (hasCooking) {
                const selectedCookingRadio = document.querySelector('input[name="selected_cooking_option"]:checked');
                if (selectedCookingRadio) {
                    cookingMethod = selectedCookingRadio.value;
                    if (cookingMethod === 'Al Horno') cookingMethod = 'Horno';
                } else {
                    cookingMethod = 'Horno';
                }
            }

            let selectedGarnish = null;
            if (hasGarnishes) {
                const selectedGarnishRadio = document.querySelector('input[name="selected_garnish_option"]:checked');
                if (selectedGarnishRadio && product.garnishes[parseInt(selectedGarnishRadio.value)]) {
                    selectedGarnish = product.garnishes[parseInt(selectedGarnishRadio.value)];
                }
            }

            const notes = document.getElementById('variant-item-notes').value;
            cartManager.addItem(product, variant, notes, 1, cookingMethod, selectedGarnish);
            closeVariantModal();
        };
    } else {
        cartManager.addItem(product, null, '', 1, null, null);
    }
}

function closeVariantModal() {
    const modal = document.getElementById('variant-modal');
    const card = document.getElementById('variant-modal-card');
    if (card) card.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
    }, 200);
}

// Armado y envío del pedido a WhatsApp
async function submitOrderToWhatsApp() {
    if (cartManager.items.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tu carrito está vacío',
            text: 'Agrega al menos un plato para realizar el pedido.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const nameInput = document.getElementById('order-customer-name');
    const phoneInput = document.getElementById('order-customer-phone');
    const emailInput = document.getElementById('order-customer-email');
    const deliveryRadio = document.querySelector('input[name="order_delivery_type"]:checked');
    const addressInput = document.getElementById('order-customer-address');
    const paymentSelect = document.getElementById('order-payment-method');
    const notesTextarea = document.getElementById('order-general-notes');

    const customerName = nameInput ? nameInput.value.trim() : '';
    const customerPhone = phoneInput ? phoneInput.value.trim() : '';
    const customerEmail = emailInput ? emailInput.value.trim() : '';
    const deliveryType = deliveryRadio ? deliveryRadio.value : 'delivery';
    const address = addressInput ? addressInput.value.trim() : '';
    const paymentMethod = paymentSelect ? paymentSelect.value : 'Efectivo';
    const generalNotes = notesTextarea ? notesTextarea.value.trim() : '';

    if (!customerName) {
        Swal.fire({
            icon: 'info',
            title: 'Falta tu nombre',
            text: 'Por favor ingresa tu nombre para identificar el pedido.',
            confirmButtonColor: '#dc2626'
        });
        nameInput.focus();
        return;
    }

    if (!customerPhone) {
        Swal.fire({
            icon: 'info',
            title: 'Falta tu teléfono',
            text: 'Por favor ingresa un teléfono o WhatsApp de contacto.',
            confirmButtonColor: '#dc2626'
        });
        phoneInput.focus();
        return;
    }

    const mapLat = document.getElementById('order-delivery-lat')?.value || '';
    const mapLng = document.getElementById('order-delivery-lng')?.value || '';
    const mapUrl = document.getElementById('order-delivery-map-url')?.value || '';

    if (deliveryType === 'delivery') {
        if (!address) {
            Swal.fire({
                icon: 'info',
                title: 'Falta tu dirección',
                text: 'Por favor ingresa tu dirección de envío.',
                confirmButtonColor: '#dc2626'
            });
            if (addressInput) addressInput.focus();
            return;
        }
        if (!isGpsLocatedByUser || !mapLat || !mapLng) {
            const gpsBtn = document.getElementById('btn-cart-gps-locate');
            if (gpsBtn) {
                gpsBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                gpsBtn.classList.add('scale-110', 'ring-4', 'ring-red-500');
                setTimeout(() => gpsBtn.classList.remove('scale-110', 'ring-4', 'ring-red-500'), 1500);
            }

            Swal.fire({
                icon: 'warning',
                title: '¡Paso obligatorio!',
                html: 'Debes presionar el botón rojo <b class="text-red-600"><i class="fas fa-crosshairs"></i> Ubicarme</b> al lado de tu dirección para fijar tu ubicación satelital antes de continuar.',
                confirmButtonColor: '#dc2626',
                confirmButtonText: '📍 Presionar Ubicarme ahora'
            }).then((res) => {
                if (res.isConfirmed) {
                    locateUserGPS();
                }
            });
            return;
        }
    }

    // Registrar pedido en la base de datos de Laravel (opcional y transparente)
    try {
        const orderPayload = {
            customer_name: customerName,
            customer_phone: customerPhone,
            customer_email: customerEmail || null,
            delivery_type: deliveryType,
            delivery_address: address || (mapUrl ? 'Ubicación fijada en mapa' : null),
            delivery_map_url: mapUrl || null,
            delivery_latitude: mapLat ? parseFloat(mapLat) : null,
            delivery_longitude: mapLng ? parseFloat(mapLng) : null,
            payment_method: paymentMethod,
            notes: generalNotes,
            total_amount: cartManager.getTotalAmount(),
            items: cartManager.items.map(item => ({
                product_id: item.productId,
                category_name: item.categoryName || null,
                product_name: item.productName,
                variant_name: item.variantName,
                cooking_method: item.cookingMethod || null,
                garnish_name: item.garnishName || null,
                garnish_price: item.garnishPrice || 0,
                unit_price: item.unitPrice,
                quantity: item.quantity,
                subtotal: item.subtotal,
                notes: item.notes
            }))
        };

        if (window.APP_CONFIG && window.APP_CONFIG.orderSaveUrl) {
            fetch(window.APP_CONFIG.orderSaveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.APP_CONFIG.csrfToken || ''
                },
                body: JSON.stringify(orderPayload),
                keepalive: true
            }).catch(err => console.log('Registro local de pedido completado.', err));
        }
    } catch (e) {
        console.error('Error al registrar pedido:', e);
    }

    // Armado estructurado del mensaje para WhatsApp con emojis
    let msg = `¡Hola *${window.APP_CONFIG?.restaurantName || 'Rotisería La Abuela'}*! 👵🍕\n`;
    msg += `Quiero realizar el siguiente pedido:\n\n`;
    msg += `📋 *DETALLE DEL PEDIDO:*\n`;

    cartManager.items.forEach(item => {
        let details = [];
        if (item.variantName) details.push(item.variantName);
        if (item.cookingMethod) details.push(formatCookingLabel(item.cookingMethod));
        const detailsText = details.length > 0 ? ` (${details.join(' - ')})` : '';
        const categoryPrefix = item.categoryName ? `[${item.categoryName}] ` : '';
        const itemSubtotal = '$' + item.subtotal.toLocaleString('es-AR');

        const baseUnitPrice = item.basePrice !== undefined ? item.basePrice : (item.unitPrice - (item.garnishPrice || 0));
        const baseSubtotal = '$' + (baseUnitPrice * item.quantity).toLocaleString('es-AR');

        if (item.garnishName) {
            msg += `• *${item.quantity}x* ${categoryPrefix}${item.productName}${detailsText} — ${baseSubtotal}\n`;
            const extraText = item.garnishPrice > 0 ? ` (+ $${item.garnishPrice.toLocaleString('es-AR')})` : '';
            msg += `   └ 🥗 _Guarnición: ${item.garnishName}${extraText}_\n`;
            if (item.notes) {
                msg += `   └ 📝 _Nota: ${item.notes}_\n`;
            }
            msg += `   subtotal: ${itemSubtotal}\n`;
        } else {
            msg += `• *${item.quantity}x* ${categoryPrefix}${item.productName}${detailsText} — ${itemSubtotal}\n`;
            if (item.notes) {
                msg += `   └ 📝 _Nota: ${item.notes}_\n`;
            }
        }
    });

    const totalFormatted = '$' + cartManager.getTotalAmount().toLocaleString('es-AR');
    msg += `\n💰 *TOTAL A PAGAR:* *${totalFormatted}*\n\n`;

    msg += `━━━━━━━━━━━━━━━━━━━━━\n`;
    msg += `🛵 *Tipo de entrega:* ${deliveryType === 'delivery' ? 'Envío a Domicilio' : 'Retiro en el Local'}\n`;
    if (deliveryType === 'delivery') {
        if (address) {
            msg += `📍 *Dirección de Envío:* ${address}\n`;
        }
        if (mapUrl) {
            msg += `🗺️ *Ubicación exacta (Google Maps):* ${mapUrl}\n`;
        }
        if (!address && mapUrl) {
            msg += `📍 *Dirección:* Ubicación exacta fijada en el mapa (ver link)\n`;
        }
    }
    msg += `👤 *Cliente:* ${customerName}\n`;
    msg += `📞 *Teléfono:* ${customerPhone}\n`;
    msg += `💳 *Forma de pago:* ${paymentMethod}\n`;
    if (generalNotes) {
        msg += `📝 *Aclaraciones generales:* ${generalNotes}\n`;
    }
    msg += `━━━━━━━━━━━━━━━━━━━━━\n`;
    msg += `_¡Muchas gracias!_ ✨`;

    const rawNumber = window.APP_CONFIG?.whatsappPhone || '5493794565528';
    const whatsappNumber = rawNumber.replace(/\D/g, '');
    const whatsappUrl = `https://api.whatsapp.com/send?phone=${whatsappNumber}&text=${encodeURIComponent(msg)}`;

    // Feedback visual y apertura de WhatsApp
    closeCartModal();
    cartManager.clearCart();

    // Detección de dispositivos móviles (iOS / Android)
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent || navigator.vendor || window.opera) ||
                     (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    // En iOS (Safari) y Android, window.open() dentro de promesas/timers es bloqueado como popup por el navegador.
    // Usar window.location.href directamente en el flujo del usuario abre WhatsApp de forma nativa e inmediata.
    if (isMobile) {
        window.location.href = whatsappUrl;
    } else {
        window.open(whatsappUrl, '_blank');
    }

    // Modal de confirmación con botón de respaldo directo por si el navegador o modo privado bloquea la redirección
    Swal.fire({
        icon: 'success',
        title: '¡Pedido armado con éxito!',
        html: `
            <p class="text-sm text-slate-600 mb-3">Te estamos redirigiendo a WhatsApp para enviar tu pedido...</p>
            <div class="py-1">
                <a href="${whatsappUrl}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/30 transition">
                    <i class="fab fa-whatsapp text-lg"></i>
                    <span>Abrir WhatsApp</span>
                </a>
            </div>
            <p class="text-[11px] text-slate-400 mt-3">Si WhatsApp no se abrió automáticamente, toca el botón de arriba.</p>
        `,
        showConfirmButton: false,
        timer: 6000,
        timerProgressBar: true
    });
}

// Inicialización global al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    // Si la API Key de Google Maps está presente, precargar SDK
    if (window.APP_CONFIG?.googleMapsApiKey) {
        loadGoogleMapsScript();
    }

    // Escuchar pegado directo de enlaces en el campo de dirección
    const addressInput = document.getElementById('order-customer-address');
    if (addressInput) {
        addressInput.addEventListener('blur', handleAddressInputBlur);
        addressInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addressInput.blur();
            }
        });
        addressInput.addEventListener('paste', (e) => {
            setTimeout(() => {
                const pastedText = addressInput.value.trim();
                const coords = parseGoogleMapsUrlOrCoords(pastedText);
                if (coords) {
                    const wrapper = document.getElementById('cart-map-wrapper');
                    if (wrapper && wrapper.classList.contains('hidden')) toggleCartMap();
                    initCartMap(coords.lat, coords.lng);
                    setCartPin(coords.lat, coords.lng, true, true);
                } else if (pastedText.startsWith('http://') || pastedText.startsWith('https://')) {
                    handleGoogleMapsUrlInput(pastedText);
                }
            }, 50);
        });
    }

    // Inicializar autocompletado inteligente de calles
    setupAddressAutocomplete();
});

