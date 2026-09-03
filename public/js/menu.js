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

// Modal SweetAlert2 para ampliar fotos
function showImageSweetAlert(imageUrl, title) {
    Swal.fire({
        imageUrl: imageUrl,
        imageAlt: title,
        imageWidth: 'auto',
        imageHeight: 'auto',
        title: `<span class="text-xl font-bold text-gray-800">${title}</span>`,
        showConfirmButton: false,
        showCloseButton: true,
        background: 'rgba(255, 255, 255, 0.95)',
        backdrop: 'rgba(0, 0, 0, 0.65)',
        customClass: {
            image: 'rounded-2xl max-h-[70vh] object-contain shadow-xl mx-auto',
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

// Modal interactivo de Promociones / Eventos con Confeti personalizable y Emotes
function showEventAlertDynamic(eventData) {
    const title = eventData?.title || 'Promoción Especial';
    const subtitle = eventData?.subtitle || '';
    const imagePath = eventData?.image_path ? `/${eventData.image_path}` : '/imagenes/eventos/mundial/oferta_mundial.jpeg';
    const rawEmojis = eventData?.confetti_emojis || '⚽,🇦🇷,🏆,🎉';
    const emojis = parseConfettiEmojis(rawEmojis);
    const colors = (eventData?.confetti_colors || '#75AADB,#FFFFFF,#F6B40E').split(',').map(c => c.trim()).filter(c => c.length > 0);
    const customWhatsAppText = eventData?.whatsapp_custom_text || `Hola! Quiero consultar por la promo: ${title}`;

    Swal.fire({
        showConfirmButton: true,
        confirmButtonText: '<i class="fab fa-whatsapp mr-1.5"></i> Consultar Promo',
        confirmButtonColor: '#16a34a',
        showCloseButton: true,
        background: '#ffffff',
        width: 'auto',
        customClass: {
            popup: 'rounded-3xl p-4 sm:p-6 shadow-2xl max-w-xl',
            confirmButton: 'rounded-xl font-bold px-6 py-3 shadow-lg'
        },
        html: `
            <div class="text-center space-y-3">
                <h3 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">${title}</h3>
                ${subtitle ? `<p class="text-sm sm:text-base text-gray-600 font-medium">${subtitle}</p>` : ''}
                <div class="relative overflow-hidden rounded-2xl shadow-xl my-3">
                    <img src="${imagePath}" alt="${title}" class="w-full max-h-[60vh] object-contain rounded-2xl mx-auto">
                </div>
            </div>
        `,
        didOpen: () => {
            if (typeof confetti !== 'function') return;

            // Generar formas para los emoticones
            let emojiShapes = [];
            if (typeof confetti.shapeFromText === 'function' && emojis.length > 0) {
                const scalar = 3;
                emojis.forEach(emoji => {
                    try {
                        const shape = confetti.shapeFromText({ text: emoji, scalar });
                        if (shape) emojiShapes.push(shape);
                    } catch (e) {
                        console.warn('Error al generar forma de confeti para:', emoji, e);
                    }
                });
            }

            // 1. Ráfaga inicial de bienvenida (Colores + Emotes en el centro)
            confetti({
                particleCount: 50,
                spread: 80,
                origin: { x: 0.5, y: 0.6 },
                colors: colors.length > 0 ? colors : ['#75AADB', '#FFFFFF', '#F6B40E']
            });

            if (emojiShapes.length > 0) {
                confetti({
                    particleCount: 25,
                    spread: 90,
                    origin: { x: 0.5, y: 0.6 },
                    shapes: emojiShapes,
                    scalar: 3,
                    flat: true
                });
            }

            // 2. Efecto de Confeti festivo continuo por 3.5 segundos desde ambos laterales
            const end = Date.now() + 3500;
            (function frame() {
                // Confeti de colores
                confetti({
                    particleCount: 3,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0, y: 0.7 },
                    colors: colors
                });
                confetti({
                    particleCount: 3,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1, y: 0.7 },
                    colors: colors
                });

                // Confeti de emoticones
                if (emojiShapes.length > 0) {
                    confetti({
                        particleCount: 1,
                        angle: 60,
                        spread: 50,
                        origin: { x: 0, y: 0.7 },
                        shapes: emojiShapes,
                        scalar: 2.8,
                        flat: true
                    });
                    confetti({
                        particleCount: 1,
                        angle: 120,
                        spread: 50,
                        origin: { x: 1, y: 0.7 },
                        shapes: emojiShapes,
                        scalar: 2.8,
                        flat: true
                    });
                }

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            }());
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const phone = window.APP_CONFIG?.whatsappPhone || '5493794565528';
            window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(customWhatsAppText)}`, '_blank');
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
});
