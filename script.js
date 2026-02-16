// Función para mostrar categorías dinámicamente
// Función para mostrar categorías dinámicamente con transición GSAP
function showCategory(filename) {
    // Quita la clase activa de todos los botones
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Agrega la clase activa al botón correspondiente
    const btn = Array.from(document.querySelectorAll('.category-btn')).find(b => b.getAttribute('onclick') && b.getAttribute('onclick').includes(filename));
    if (btn) {
        btn.classList.add('active');
    }

    const overlay = document.getElementById('transition-overlay');
    const path = document.getElementById('curve-path');
    
    // Asegurar que el overlay sea visible
    overlay.classList.remove('hidden');
    
    // Implementación de Curve Swipe estilo demo GSAP
    
    // Reset path: Start flat at top (Height 0)
    gsap.set(path, { attr: { d: "M 0 0 L 100 0 L 100 0 Q 50 0 0 0 Z" } });

    const tl = gsap.timeline();

    // 1. Fill Screen (Curve leads down)
    // The curve (Q 50 120) goes lower than the sides (100) to create the convex shape
    tl.to(path, { 
        duration: 0.4,
        attr: { d: "M 0 0 L 100 0 L 100 100 Q 50 120 0 100 Z" }, 
        ease: "power2.in"
    })
    // 2. Flatten (Snap to full screen)
    .to(path, {
        duration: 0.1,
        attr: { d: "M 0 0 L 100 0 L 100 100 Q 50 100 0 100 Z" },
        ease: "power1.out",
        onComplete: () => {
            loadContent(filename, () => {
                // 3. Prepare for Exit (Curve at top hidden)
                // We want to reveal from top to bottom.
                // We set the path to be full, but we will animate the TOP edge and the CURVE down.
                // M 0 0 ... -> M 0 100 ...
                
                // Let's animate from "Full Screen" to "Content Revealed" (everything moving down)
                
                gsap.to(path, {
                    duration: 0.4,
                    attr: { d: "M 0 100 L 100 100 L 100 100 Q 50 100 0 100 Z" }, // Effectively moving the top edge down to the bottom
                    ease: "power2.inOut",
                    onComplete: () => {
                        overlay.classList.add('hidden');
                        gsap.set(path, { attr: { d: "M 0 0 L 100 0 L 100 0 Q 50 0 0 0 Z" } });
                    }
                });
            });
        }
    });

}

function loadContent(filename, callback) {
    // ... (existing loadContent code)
    fetch(`opciones/${filename}`)
        .then(response => {
            if (!response.ok) {
                if (response.status === 404) {
                    return Promise.reject('Archivo no encontrado');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('menu-sections-container').innerHTML = html;
            document.getElementById('menu-content').scrollIntoView({ behavior: 'auto' });
            if (callback) callback();
        })
        .catch(error => {
            console.error('Error al cargar la categoría:', error);
            document.getElementById('menu-sections-container').innerHTML = `<p class="text-red-500 text-center text-xl">No contamos con ese menú actualmente.</p>`;
            if (callback) callback();
        });
}

// ... (Rest of code) ...

// Corazones
// ...



// Lógica para el menú fijo y ocultar/mostrar en scroll
let lastScrollY = window.scrollY;
const menuFixed = document.querySelector('.menu-fixed');
const heroHeight = document.querySelector('.hero-bg').offsetHeight;

window.addEventListener('scroll', () => {
    let currentScrollY = window.scrollY;

    // Determina si estamos en una vista móvil (menos de 768px de ancho)
    const isMobileView = window.innerWidth < 768; // Coincide con el breakpoint 'md' de Tailwind

    if (currentScrollY > heroHeight) {
        // Cuando se ha pasado el header
        menuFixed.classList.add('scrolled'); // Añade la clase para estilos de menú fijo al hacer scroll

        if (isMobileView) {
            // Lógica para ocultar/mostrar en móvil
            if (currentScrollY > lastScrollY) {
                // Bajando: oculta el menú
                menuFixed.classList.add('hidden-on-scroll');
            } else {
                // Subiendo: muestra el menú
                menuFixed.classList.remove('hidden-on-scroll');
            }
        } else {
            // Si no es vista móvil, asegúrate de que el menú no esté oculto por el scroll
            menuFixed.classList.remove('hidden-on-scroll');
        }
    } else {
        // Cuando estamos dentro del área del header hero, no se oculta y se quitan estilos de scroll
        menuFixed.classList.remove('scrolled', 'hidden-on-scroll');
    }
    lastScrollY = currentScrollY;
});

// Asegurarse de que el menú se muestre si se cambia el tamaño de la ventana a escritorio
window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) { // Si es vista de escritorio
        menuFixed.classList.remove('hidden-on-scroll');
    }
});

// Carga la primera categoría por defecto al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    // No se carga ninguna categoría por defecto para que el usuario elija.

    // Scroll a la sección de categorías después de una breve demora para que se vean las animaciones del header
    const categoriesSection = document.querySelector('.menu-categories-wrapper');

});

// Función para mostrar SweetAlert2 al hacer clic en una imagen de pizza
function showPizzaSweetAlert(imageElement) {
    Swal.fire({
        imageUrl: imageElement.src,
        imageAlt: imageElement.alt,
        imageWidth: '80%', // Ajusta el ancho de la imagen en el modal
        imageHeight: 'auto',
        title: imageElement.alt,
        showConfirmButton: false,
        showCloseButton: true,
        background: 'rgba(255, 255, 255, 0.9)',
        backdrop: `
          rgba(0,0,0,0.4)
          url("/path/to/your/custom-loader.gif") // Si tienes un loader personalizado
          left top
          no-repeat
      `
    });
}

// Función para mostrar el evento en SweetAlert
function showEventAlert() {
    Swal.fire({
        imageUrl: '/imagenes/eventos/SanValentin/sanvalentin.jpg',
        imageAlt: 'San Valentin',
        showConfirmButton: false,
        showCloseButton: true,
        background: 'transparent',
        customClass: {
            popup: 'bg-transparent',
            image: 'rounded-2xl shadow-2xl'
        },
        backdrop: `
            rgba(0,0,0,0.6)
            left top
            no-repeat
        `
    });
}



