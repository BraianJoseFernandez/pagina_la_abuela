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

    // Click explosion effect - Firework style
    window.explodeDecoration = function (event) {
        event.stopPropagation();

        var rect = event.target.getBoundingClientRect();
        var x = (rect.left + rect.width / 2) / window.innerWidth;
        var y = (rect.top + rect.height / 2) / window.innerHeight;

        // Colores vibrantes para el fuego artificial
        var fireworkColors = [
            ['#FFD700', '#FFA500', '#FF6347'], // Dorado, naranja, rojo
            ['#FF1493', '#FF69B4', '#FFB6C1'], // Rosa fuerte, rosa, rosa claro
            ['#00FFFF', '#1E90FF', '#4169E1'], // Cian, azul dodger, azul real
            ['#9370DB', '#BA55D3', '#DA70D6'], // Púrpura medio, orquídea, orquídea medio
            ['#32CD32', '#00FF00', '#ADFF2F'], // Verde lima, verde, verde amarillo
            ['#FFD700', '#FFFF00', '#FAFAD2']  // Dorado, amarillo, amarillo claro
        ];

        var selectedColors = fireworkColors[Math.floor(Math.random() * fireworkColors.length)];

        // Primera ráfaga - explosión inicial
        myConfetti({
            particleCount: 60,
            spread: 100,
            startVelocity: 45,
            origin: { x: x, y: y },
            colors: selectedColors,
            scalar: 0.7, // Reducido de 1.5 a 0.7
            gravity: 1,
            ticks: 150,
            disableForReducedMotion: true
        });

        // Segunda ráfaga - efecto de chispas
        setTimeout(function () {
            myConfetti({
                particleCount: 40,
                spread: 120,
                startVelocity: 35,
                origin: { x: x, y: y },
                colors: selectedColors,
                scalar: 0.6, // Reducido de 1.2 a 0.6
                gravity: 0.8,
                ticks: 120,
                disableForReducedMotion: true
            });
        }, 100);

        // Tercera ráfaga - estrellas brillantes
        setTimeout(function () {
            myConfetti({
                particleCount: 30,
                spread: 80,
                startVelocity: 25,
                origin: { x: x, y: y },
                shapes: ['star'],
                colors: ['#FFD700', '#FFFFFF', '#FFA500'],
                scalar: 0.9, // Reducido de 1.8 a 0.9
                gravity: 0.5,
                ticks: 180,
                disableForReducedMotion: true
            });
        }, 200);

        // Eliminar el icono después de la explosión
        if (event.target.classList.contains('floating-icon')) {
            event.target.remove();
        }
    };

    // Create page-wide floating icons
    var iconsList = [
        { class: 'fas fa-star', color: 'text-yellow-400', shadow: '0 0 10px rgba(250, 204, 21, 0.5)' },
        { class: 'fas fa-snowflake', color: 'text-gray-200', shadow: '0 0 8px rgba(255, 255, 255, 0.8)' },
        { class: 'fas fa-tree', color: 'text-green-600', shadow: '0 0 8px rgba(34, 197, 94, 0.5)' },
        { class: 'fas fa-champagne-glasses', color: 'text-yellow-300', shadow: '0 0 8px rgba(253, 224, 71, 0.5)' }
    ];
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
        var icon = document.createElement('i');
        var selectedIcon = iconsList[Math.floor(Math.random() * iconsList.length)];

        icon.className = 'floating-icon page-wide-icon ' + selectedIcon.class + ' ' + selectedIcon.color + ' cursor-pointer';
        icon.style.left = Math.random() * 100 + '%';
        icon.style.top = Math.random() * 100 + '%';
        icon.style.animationDelay = Math.random() * 3 + 's';
        icon.style.fontSize = (Math.random() * 1.5 + 1.5) + 'rem';
        icon.style.textShadow = selectedIcon.shadow;
        icon.onclick = explodeDecoration;
        iconsContainer.appendChild(icon);

        // Remove icon after animation cycle completes
        setTimeout(function () {
            if (icon.parentNode) {
                icon.remove();
            }
        }, 10000 + (Math.random() * 5000));
    }

    // Create initial icons - reducido para menos frecuencia
    for (var i = 0; i < 3; i++) {
        setTimeout(createRandomIcon, i * 1500);
    }

    // Continue spawning icons periodically - menos frecuente
    setInterval(function () {
        if (iconsContainer.children.length < 8) {
            createRandomIcon();
        }
    }, 4000); // Cambiado de 2000ms a 6000ms (cada 6 segundos)

})();