/**
 * Control del Menú Dinámico, Animaciones GSAP, Swiper y SweetAlert2
 * Rotisería La Abuela
 */

let activeCategorySlug = null;
let isAnimatingTransition = false;

// Carga dinámica de categoría con animación GSAP Curve Swipe
function loadCategoryDynamic(slug, forceWithoutAnimation = false, isUserClick = false) {
    if (activeCategorySlug === slug && !forceWithoutAnimation) return;
    if (isAnimatingTransition) return;

    // Actualizar clase activa en los botones de categoría
    document.querySelectorAll('.category-btn').forEach(btn => {
        if (btn.getAttribute('data-slug') === slug) {
            btn.classList.add('active');
            if (isUserClick) {
                btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        } else {
            btn.classList.remove('active');
        }
    });

    const overlay = document.getElementById('transition-overlay');
    const path = document.getElementById('curve-path');
    const container = document.getElementById('menu-sections-container');

    if (forceWithoutAnimation || !overlay || !path) {
        fetchCategoryContent(slug, (html) => {
            container.innerHTML = html;
            initSwipers();
            activeCategorySlug = slug;
            if (isUserClick) {
                scrollToMenuCategory();
            }
        });
        return;
    }

    isAnimatingTransition = true;
    overlay.classList.remove('hidden');

    // Reset curva GSAP
    gsap.set(path, { attr: { d: "M 0 0 L 100 0 L 100 0 Q 50 0 0 0 Z" } });

    const tl = gsap.timeline();

    // 1. Cubrir pantalla hacia abajo con curva
    tl.to(path, {
        duration: 0.35,
        attr: { d: "M 0 0 L 100 0 L 100 100 Q 50 120 0 100 Z" },
        ease: "power2.in"
    })
    // 2. Aplanar a pantalla completa
    .to(path, {
        duration: 0.1,
        attr: { d: "M 0 0 L 100 0 L 100 100 Q 50 100 0 100 Z" },
        ease: "power1.out",
        onComplete: () => {
            fetchCategoryContent(slug, (html) => {
                container.innerHTML = html;
                initSwipers();
                activeCategorySlug = slug;

                // Desplazar con precisión solo si fue clic del usuario (para que el icono y título queden 100% visibles)
                if (isUserClick) {
                    scrollToMenuCategory();
                }

                // 3. Revelar nuevo contenido hacia abajo
                gsap.to(path, {
                    duration: 0.35,
                    attr: { d: "M 0 100 L 100 100 L 100 100 Q 50 100 0 100 Z" },
                    ease: "power2.inOut",
                    onComplete: () => {
                        overlay.classList.add('hidden');
                        gsap.set(path, { attr: { d: "M 0 0 L 100 0 L 100 0 Q 50 0 0 0 Z" } });
                        isAnimatingTransition = false;
                    }
                });
            });
        }
    });
}

// Función para desplazar la vista dejando el icono y título de la categoría 100% visibles
function scrollToMenuCategory() {
    const navEl = document.querySelector('.menu-categories-wrapper');
    const menuEl = document.getElementById('menu-content');
    if (!menuEl) return;

    const isMobile = window.innerWidth < 768;
    const rect = menuEl.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    // En mobile la barra es estática en el flujo normal (no flotante),
    // por lo que nos desplazamos directamente al inicio del menú sin restar la barra.
    const navHeight = (!isMobile && navEl) ? navEl.offsetHeight : 0;
    const targetY = rect.top + scrollTop - navHeight - 12;

    window.scrollTo({
        top: Math.max(0, targetY),
        behavior: 'smooth'
    });
}

function fetchCategoryContent(slug, callback) {
    const url = `${window.APP_CONFIG?.categoryRouteUrl || '/categoria'}/${slug}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html, application/xhtml+xml'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Error al cargar categoría');
        return res.text();
    })
    .then(html => {
        if (callback) callback(html);
    })
    .catch(err => {
        console.error('Error cargando categoría:', err);
        const container = document.getElementById('menu-sections-container');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-16 text-red-500">
                    <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                    <p class="text-xl font-bold">No pudimos cargar esta categoría en este momento.</p>
                </div>
            `;
        }
        isAnimatingTransition = false;
        const overlay = document.getElementById('transition-overlay');
        if (overlay) overlay.classList.add('hidden');
    });
}

// Inicializar Swiper.js en carruseles de fotos
function initSwipers() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.category-photos-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 16,
            centeredSlides: false,
            freeMode: true,
            grabCursor: true,
            observer: true,
            observeParents: true
        });
    }
}

// Escapa texto cargado desde el panel antes de insertarlo como HTML en SweetAlert
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Modal SweetAlert2 para ampliar fotos (muestra el título propio de cada foto)
// options.round = true → la foto se muestra en círculo (fotos de platos)
function showImageSweetAlert(imageUrl, title, options = {}) {
    const safeTitle = escapeHtml(title || '');
    const round = !!options.round;
    const roundSize = 'min(78vw, 380px)';
    Swal.fire({
        imageUrl: imageUrl,
        imageAlt: title || '',
        imageWidth: round ? roundSize : 'auto',
        imageHeight: round ? roundSize : 'auto',
        title: safeTitle ? `<span class="text-xl font-bold text-gray-800">${safeTitle}</span>` : '',
        showConfirmButton: false,
        showCloseButton: true,
        background: 'rgba(255, 255, 255, 0.95)',
        backdrop: 'rgba(0, 0, 0, 0.65)',
        customClass: {
            image: round
                ? 'rounded-full object-cover shadow-xl mx-auto border-[6px] border-purple-200'
                : 'rounded-2xl max-h-[70vh] object-contain shadow-xl mx-auto',
            popup: 'rounded-3xl p-4'
        }
    });
}

// Función auxiliar para separar emoticones con soporte para comas y secuencias de emojis
function parseConfettiEmojis(rawEmojis) {
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

// Las formas de emoji se rasterizan una sola vez (shapeFromText es costoso y bloquea el hilo principal)
const emojiShapeCache = new Map();
function getEmojiShapes(emojis, scalar) {
    if (typeof confetti.shapeFromText !== 'function') return [];
    const shapes = [];
    emojis.forEach(emoji => {
        const key = `${emoji}|${scalar}`;
        if (!emojiShapeCache.has(key)) {
            try {
                emojiShapeCache.set(key, confetti.shapeFromText({ text: emoji, scalar }));
            } catch (e) {
                console.warn('Error al generar forma de confeti para:', emoji, e);
                emojiShapeCache.set(key, null);
            }
        }
        const shape = emojiShapeCache.get(key);
        if (shape) shapes.push(shape);
    });
    return shapes;
}

// Lluvia continua de confeti desde ambos laterales.
// La emisión va por tiempo y no por cuadro: la cantidad total es la misma que a 60 FPS,
// pero se agrupa en menos llamadas y no se duplica en pantallas de 90/120 Hz.
function runConfettiStream(colors, emojiShapes, durationMs) {
    const FRAME_MS = 1000 / 60;
    const EMIT_EVERY_MS = FRAME_MS * 2;
    const end = performance.now() + durationMs;
    let last = performance.now();
    let pending = 0;

    function emit(frames) {
        confetti({ particleCount: 3 * frames, angle: 60, spread: 55, origin: { x: 0, y: 0.7 }, colors, disableForReducedMotion: true });
        confetti({ particleCount: 3 * frames, angle: 120, spread: 55, origin: { x: 1, y: 0.7 }, colors, disableForReducedMotion: true });

        if (emojiShapes.length > 0) {
            confetti({ particleCount: frames, angle: 60, spread: 50, origin: { x: 0, y: 0.7 }, shapes: emojiShapes, scalar: 2.8, flat: true, disableForReducedMotion: true });
            confetti({ particleCount: frames, angle: 120, spread: 50, origin: { x: 1, y: 0.7 }, shapes: emojiShapes, scalar: 2.8, flat: true, disableForReducedMotion: true });
        }
    }

    (function frame(now) {
        pending += now - last;
        last = now;
        if (pending >= EMIT_EVERY_MS) {
            // Tope de 4 cuadros para no soltar una ráfaga enorme si el teléfono se trabó
            emit(Math.min(Math.round(pending / FRAME_MS), 4));
            pending = 0;
        }
        if (now < end) requestAnimationFrame(frame);
    }(last));
}

// Modal interactivo de Promociones / Eventos con Confeti personalizable y Emotes
function showEventAlertDynamic(eventData) {
    if (!eventData) return;
    const title = eventData?.title || 'Promoción Especial';
    const subtitle = eventData?.subtitle || '';
    const safeTitle = escapeHtml(title);
    const safeSubtitle = escapeHtml(subtitle);
    const imagePath = eventData?.image_url
        || (eventData?.image_path ? `/${eventData.image_path}` : '/imagenes/eventos/mundial/oferta_mundial.jpeg');
    const rawEmojis = eventData?.confetti_emojis || '⚽,🇦🇷,🏆,🎉';
    const emojis = parseConfettiEmojis(rawEmojis);
    const colors = (eventData?.confetti_colors || '#75AADB,#FFFFFF,#F6B40E').split(',').map(c => c.trim()).filter(c => c.length > 0);
    const customWhatsAppText = eventData?.whatsapp_custom_text || `Hola! Quiero consultar por la promo: ${title}`;

    return Swal.fire({
        showConfirmButton: true,
        confirmButtonText: '<i class="fab fa-whatsapp mr-1.5"></i> Consultar Promo',
        confirmButtonColor: '#16a34a',
        showCloseButton: true,
        background: '#ffffff',
        width: 'auto',
        customClass: {
            popup: 'rounded-3xl p-3 sm:p-5 shadow-2xl max-w-xl',
            htmlContainer: '!m-0 !px-1 !pt-2',
            confirmButton: 'rounded-xl font-bold px-6 py-3 shadow-lg'
        },
        html: `
            <div class="text-center space-y-2">
                <h3 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight px-8">${safeTitle}</h3>
                ${safeSubtitle ? `<p class="text-sm sm:text-base text-gray-600 font-medium px-2">${safeSubtitle}</p>` : ''}
                <div class="relative inline-block max-w-full overflow-hidden rounded-2xl shadow-xl mt-3 align-top">
                    <img src="${escapeHtml(imagePath)}" alt="${safeTitle}" class="block max-w-full w-auto h-auto max-h-[62vh] object-contain mx-auto">
                </div>
            </div>
        `,
        didOpen: async (popup) => {
            if (typeof confetti !== 'function') return;

            // Esperar a que el flyer esté decodificado para no competir con el confeti por el hilo principal
            const flyer = popup.querySelector('img');
            if (flyer && typeof flyer.decode === 'function') {
                await flyer.decode().catch(() => {});
            }

            const confettiColors = colors.length > 0 ? colors : ['#75AADB', '#FFFFFF', '#F6B40E'];
            const emojiShapes = getEmojiShapes(emojis, 3);

            // 1. Ráfaga inicial de bienvenida (Colores + Emotes en el centro)
            confetti({
                particleCount: 50,
                spread: 80,
                origin: { x: 0.5, y: 0.6 },
                colors: confettiColors,
                disableForReducedMotion: true
            });

            if (emojiShapes.length > 0) {
                confetti({
                    particleCount: 25,
                    spread: 90,
                    origin: { x: 0.5, y: 0.6 },
                    shapes: emojiShapes,
                    scalar: 3,
                    flat: true,
                    disableForReducedMotion: true
                });
            }

            // 2. Efecto de Confeti festivo continuo por 3.5 segundos desde ambos laterales
            runConfettiStream(confettiColors, emojiShapes, 3500);
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const rawPhone = window.APP_CONFIG?.whatsappPhone || '5493794565528';
            const phone = rawPhone.replace(/\D/g, '');
            const targetUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(customWhatsAppText)}`;
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent || navigator.vendor || window.opera) ||
                             (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            if (isMobile) {
                window.location.href = targetUrl;
            } else {
                window.open(targetUrl, '_blank');
            }
        }
    });
}

// Carga de la categoría inicial al iniciar la página (SIN scroll, manteniendo el logo 100% visible)
document.addEventListener('DOMContentLoaded', () => {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);

    const initialSlug = window.APP_CONFIG?.initialCategorySlug || 'pizzas';
    loadCategoryDynamic(initialSlug, true, false);

    maybeAutoOpenEventPopup();
});

// ===== Modal automático del evento / flyer al entrar a la carta =====
function eventPopupStorageKey(eventData) {
    return `la-abuela:event-popup:${eventData.id}:${eventData.version || 0}`;
}

function wasEventPopupAlreadyShown(eventData) {
    const frequency = eventData.popup_frequency || 'always';
    if (frequency === 'always') return false;

    try {
        const key = eventPopupStorageKey(eventData);
        if (frequency === 'session') {
            return sessionStorage.getItem(key) === '1';
        }
        if (frequency === 'daily') {
            const today = new Date().toLocaleDateString('en-CA'); // AAAA-MM-DD local
            return localStorage.getItem(key) === today;
        }
    } catch (e) {
        // Navegación privada o almacenamiento bloqueado: se muestra igual
    }
    return false;
}

function markEventPopupAsShown(eventData) {
    try {
        const key = eventPopupStorageKey(eventData);
        if (eventData.popup_frequency === 'session') {
            sessionStorage.setItem(key, '1');
        } else if (eventData.popup_frequency === 'daily') {
            localStorage.setItem(key, new Date().toLocaleDateString('en-CA'));
        }
    } catch (e) { /* sin almacenamiento disponible */ }
}

function maybeAutoOpenEventPopup() {
    const eventData = window.EVENT_DATA;
    if (!eventData || !eventData.auto_popup || typeof Swal === 'undefined') return;
    if (wasEventPopupAlreadyShown(eventData)) return;

    let opened = false;
    const open = () => {
        if (opened) return;
        opened = true;
        // Si el cliente ya abrió otro modal (ej: el carrito), no lo pisamos
        if (Swal.isVisible()) return;
        markEventPopupAsShown(eventData);
        showEventAlertDynamic(eventData);
    };

    // Precargar el flyer para que el modal aparezca con la imagen ya lista (sin saltos)
    const preload = new Image();
    preload.onload = () => setTimeout(open, 700);   // deja ver la entrada animada del logo
    preload.onerror = () => setTimeout(open, 700);
    preload.src = eventData.image_url || `/${eventData.image_path}`;

    // Respaldo por si la imagen tarda demasiado en conexiones lentas
    setTimeout(open, 3500);
}
