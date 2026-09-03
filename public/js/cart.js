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

    addItem(product, variant = null, notes = '', quantity = 1, cookingMethod = null) {
        const variantId = variant ? variant.id : null;
        const variantName = variant ? variant.name : null;
        const unitPrice = variant ? parseFloat(variant.price) : parseFloat(product.price || 0);

        const hasCooking = !!product.has_cooking_options;
        let finalCookingMethod = cookingMethod;
        if (hasCooking) {
            if (!finalCookingMethod || finalCookingMethod === 'Al Horno') {
                finalCookingMethod = 'Horno';
            }
        }

        // Clave única para agrupar ítems idénticos considerando método de cocción
        const itemKey = `${product.id}_${variantId || 'base'}_${finalCookingMethod || 'none'}_${notes.trim().toLowerCase()}`;

        const existingIndex = this.items.findIndex(item => item.key === itemKey);

        if (existingIndex > -1) {
            this.items[existingIndex].quantity += quantity;
            this.items[existingIndex].subtotal = this.items[existingIndex].quantity * unitPrice;
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

            this.items.push({
                key: itemKey,
                productId: product.id,
                productName: product.name,
                variantId: variantId,
                variantName: variantName,
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
        const newKey = `${item.productId}_${item.variantId || 'base'}_${normalizedMethod || 'none'}_${(item.notes || '').trim().toLowerCase()}`;
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
                            <h4 class="font-bold text-gray-800 text-base leading-tight">${item.productName}</h4>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                ${item.variantName ? `<span class="inline-block bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-md">${item.variantName}</span>` : ''}
                            </div>
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

function toggleAddressField(isDelivery) {
    const container = document.getElementById('delivery-address-container');
    const addressInput = document.getElementById('order-customer-address');
    if (container && addressInput) {
        if (isDelivery) {
            container.classList.remove('hidden');
            addressInput.setAttribute('required', 'required');
        } else {
            container.classList.add('hidden');
            addressInput.removeAttribute('required');
        }
    }
}

// Modal de opciones / variantes y cocción
function handleAddToCartClick(product) {
    const hasVariants = product.variants && product.variants.length > 0;
    const hasCooking = !!product.has_cooking_options;

    if (hasVariants || hasCooking) {
        currentSelectedProductForModal = product;
        document.getElementById('variant-modal-title').innerText = product.name;
        document.getElementById('variant-modal-desc').innerText = product.description || (hasVariants ? 'Elige la opción que prefieras:' : 'Elige el tipo de cocción:');

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
                            <input type="radio" name="selected_variant_option" value="${index}" ${index === 0 ? 'checked' : ''} class="w-4 h-4 text-purple-600 focus:ring-purple-500">
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

            const notes = document.getElementById('variant-item-notes').value;
            cartManager.addItem(product, variant, notes, 1, cookingMethod);
            closeVariantModal();
        };
    } else {
        cartManager.addItem(product, null, '', 1, null);
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

    if (deliveryType === 'delivery' && !address) {
        Swal.fire({
            icon: 'info',
            title: 'Falta la dirección',
            text: 'Por favor indica la dirección exacta para el envío a domicilio.',
            confirmButtonColor: '#dc2626'
        });
        addressInput.focus();
        return;
    }

    // Registrar pedido en la base de datos de Laravel (opcional y transparente)
    try {
        const orderPayload = {
            customer_name: customerName,
            customer_phone: customerPhone,
            customer_email: customerEmail || null,
            delivery_type: deliveryType,
            delivery_address: address,
            payment_method: paymentMethod,
            notes: generalNotes,
            total_amount: cartManager.getTotalAmount(),
            items: cartManager.items.map(item => ({
                product_id: item.productId,
                product_name: item.productName,
                variant_name: item.variantName,
                cooking_method: item.cookingMethod || null,
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
                body: JSON.stringify(orderPayload)
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
        const itemSubtotal = '$' + item.subtotal.toLocaleString('es-AR');
        msg += `• *${item.quantity}x* ${item.productName}${detailsText} — ${itemSubtotal}\n`;
        if (item.notes) {
            msg += `   └ _Nota: ${item.notes}_\n`;
        }
    });

    const totalFormatted = '$' + cartManager.getTotalAmount().toLocaleString('es-AR');
    msg += `\n💰 *TOTAL A PAGAR:* *${totalFormatted}*\n\n`;

    msg += `━━━━━━━━━━━━━━━━━━━━━\n`;
    msg += `🛵 *Tipo de entrega:* ${deliveryType === 'delivery' ? 'Envío a Domicilio' : 'Retiro en el Local'}\n`;
    if (deliveryType === 'delivery') {
        msg += `📍 *Dirección de Envío:* ${address}\n`;
    }
    msg += `👤 *Cliente:* ${customerName}\n`;
    msg += `📞 *Teléfono:* ${customerPhone}\n`;
    msg += `💳 *Forma de pago:* ${paymentMethod}\n`;
    if (generalNotes) {
        msg += `📝 *Aclaraciones generales:* ${generalNotes}\n`;
    }
    msg += `━━━━━━━━━━━━━━━━━━━━━\n`;
    msg += `_¡Muchas gracias!_ ✨`;

    const whatsappNumber = window.APP_CONFIG?.whatsappPhone || '5493794565528';
    const whatsappUrl = `https://api.whatsapp.com/send?phone=${whatsappNumber}&text=${encodeURIComponent(msg)}`;

    // Feedback visual y apertura de WhatsApp
    closeCartModal();

    Swal.fire({
        icon: 'success',
        title: '¡Pedido armado con éxito!',
        text: 'Te estamos redirigiendo a WhatsApp para enviar tu pedido...',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    }).then(() => {
        cartManager.clearCart();
        window.open(whatsappUrl, '_blank');
    });
}
