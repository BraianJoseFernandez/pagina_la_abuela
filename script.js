// Función para mostrar categorías dinámicamente
function showCategory(filename) {
    // Quita la clase activa de todos los botones
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Agrega la clase activa al botón correspondiente
    // Asegúrate de que el 'filename' coincida con lo que se pasa en onclick
    const btn = Array.from(document.querySelectorAll('.category-btn')).find(b => b.getAttribute('onclick') && b.getAttribute('onclick').includes(filename));
    if (btn) {
        btn.classList.add('active');
    }

    // Carga el contenido de la categoría desde su archivo HTML
    // Actualizado para buscar en la carpeta 'opciones'
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
            // Scroll al principio del contenido cargado
            document.getElementById('menu-content').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error al cargar la categoría:', error);
            document.getElementById('menu-sections-container').innerHTML = `<p class="text-red-500 text-center text-xl">No contamos con ese menú actualmente.</p>`;
        });
}

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

// Función para mostrar el evento 'Noche de Blanco' en SweetAlert
function showEventAlert() {
    Swal.fire({
        imageUrl: '/imagenes/eventos/noche_de_blanco/Imagen de WhatsApp 2025-12-04 a las 11.19.47_8fc62c34.jpg',
        imageAlt: 'Noche de Blanco',
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

// --- Snow Effect using Canvas Confetti ---
(function () {
    // Create a dedicated canvas for the confetti to ensure full coverage and z-index control
    var snowCanvas = document.createElement('canvas');
    snowCanvas.style.position = 'fixed';
    snowCanvas.style.top = '0';
    snowCanvas.style.left = '0';
    snowCanvas.style.width = '100vw';
    snowCanvas.style.height = '100vh';
    snowCanvas.style.pointerEvents = 'none';
    snowCanvas.style.zIndex = '800'; // User requested 800
    document.body.appendChild(snowCanvas);

    // Initialize confetti on this canvas
    var myConfetti = confetti.create(snowCanvas, {
        resize: true,
        useWorker: true
    });

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    // Shapes for confetti effects
    var snowflakeObj = confetti.shapeFromText({ text: '*', scalar: 2, color: 'white' });
    

    // Continuous falling snow animation
    (function frame() {
        var shapes = [snowflakeObj];
        var colors = ['#ffffff'];

        // 15% chance to spawn a gold star
        if (Math.random() < 0.15) {
            colors = ['#FFD700', '#FDB931'];
        }

        myConfetti({
            particleCount: 1,
            startVelocity: 0,
            ticks: 800,
            origin: { x: Math.random(), y: -0.05 },
            colors: colors,
            shapes: shapes,
            gravity: randomInRange(0.4, 0.6),
            scalar: randomInRange(0.8, 1.2),
            drift: randomInRange(-0.4, 0.4),
            disableForReducedMotion: true
        });

        requestAnimationFrame(frame);
    }());

    // Click explosion effect
    window.explodeDecoration = function (event) {
        event.stopPropagation();

        var rect = event.target.getBoundingClientRect();
        var x = (rect.left + rect.width / 2) / window.innerWidth;
        var y = (rect.top + rect.height / 2) / window.innerHeight;

        myConfetti({
            particleCount: 40,
            spread: 80,
            origin: { x: x, y: y },
            shapes: [snowflakeObj],
            colors: ['#ffffff', '#f0f0f0', '#e8e8e8'],
            scalar: 1.2,
            gravity: 0.6,
            ticks: 100,
            disableForReducedMotion: true
        });

        if (event.target.classList.contains('page-wide-icon')) {
            event.target.remove();
        }
    };

    // Create page-wide floating icons
    var iconsList = ['⭐', '🥂', '🎄', '❄️'];
    var iconsContainer = document.createElement('div');
    iconsContainer.style.position = 'fixed';
    iconsContainer.style.top = '0';
    iconsContainer.style.left = '0';
    iconsContainer.style.width = '100vw';
    iconsContainer.style.height = '100vh';
    iconsContainer.style.pointerEvents = 'none';
    iconsContainer.style.zIndex = '1000';
    iconsContainer.style.overflow = 'hidden';
    document.body.appendChild(iconsContainer);

    function createRandomIcon() {
        var icon = document.createElement('span');
        icon.className = 'floating-icon page-wide-icon';
        icon.textContent = iconsList[Math.floor(Math.random() * iconsList.length)];
        icon.style.left = Math.random() * 100 + '%';
        icon.style.top = Math.random() * 100 + '%';
        icon.style.animationDelay = Math.random() * 5 + 's';
        icon.style.fontSize = (Math.random() * 2 + 2) + 'em';
        icon.onclick = explodeDecoration;
        iconsContainer.appendChild(icon);

        // Remove icon after animation cycle completes
        setTimeout(function () {
            if (icon.parentNode) {
                icon.remove();
            }
        }, 10000 + (Math.random() * 5000));
    }

    // Create initial icons
    for (var i = 0; i < 10; i++) {
        setTimeout(createRandomIcon, i * 500);
    }

    // Continue spawning icons periodically
    setInterval(function () {
        if (iconsContainer.children.length < 15) {
            createRandomIcon();
        }
    }, 2000);

})();