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
let confettiOrigin = { x: 0, y: 0 };

const balloonPool = [];
const MAX_BALLOON_PARTICLES = 20; // Un número seguro para los globos en pantalla

function initializeBalloonPool() {
    for (let i = 0; i < MAX_BALLOON_PARTICLES; i++) {
        const balloon = document.createElement('div');
        balloon.className = 'rising-balloon';
        balloonPool.push(balloon);
    }
}

window.addEventListener('load', () => {
    let lastScrollY = window.scrollY;
    const menuWrapper = document.querySelector('.menu-categories-wrapper');
    const heroBg = document.querySelector('.hero-bg');
    if (!menuWrapper || !heroBg) return; // Salir si los elementos no existen
    const heroHeight = heroBg.offsetHeight;
    
    // Calcula la posición del logo una sola vez, cuando todo ha cargado.
    const logo = document.getElementById('logo');
    if (logo) {
        const logoRect = logo.getBoundingClientRect();
        // Normaliza las coordenadas para canvas-confetti (0 a 1)
        confettiOrigin.x = (logoRect.left + logoRect.width / 2) / window.innerWidth;
        confettiOrigin.y = (logoRect.top + logoRect.height / 2) / window.innerHeight;
    }

    initializeBalloonPool();

    // Iniciar las animaciones de aniversario DESPUÉS de calcular la posición del logo.
    // ¡Explosión de confeti para el aniversario!
    function fireConfetti() {
        confetti({
            origin: confettiOrigin,
            particleCount: 150,
            spread: 70,
            startVelocity: 30,
            colors: ['#fde047', '#38bdf8', '#4ade80', '#a78bfa', '#ffffff', '#fb923c', '#22d3ee']
        });
    }
    fireConfetti();
    setInterval(fireConfetti, 4000);

    // ¡Globos flotando para el aniversario!
    setInterval(createRisingBalloon, 800);

    window.addEventListener('scroll', () => {
        let currentScrollY = window.scrollY;
    
        // Determina si estamos en una vista móvil (menos de 768px de ancho)
        const isMobileView = window.innerWidth < 768; // Coincide con el breakpoint 'md' de Tailwind
    
        if (currentScrollY > heroHeight) {
            // Cuando se ha pasado el header
            menuWrapper.classList.add('menu-fixed'); // Hace el menú pegajoso
    
            if (isMobileView) {
                // Lógica para ocultar/mostrar en móvil
                if (currentScrollY > lastScrollY) {
                    // Bajando: oculta el menú
                    menuWrapper.classList.add('hidden-on-scroll');
                } else {
                    // Subiendo: muestra el menú
                    menuWrapper.classList.remove('hidden-on-scroll');
                }
            } else {
                // Si no es vista móvil, asegúrate de que el menú no esté oculto por el scroll
                menuWrapper.classList.remove('hidden-on-scroll');
            }
        } else {
            // Cuando estamos dentro del área del header hero, no se oculta y se quitan estilos de scroll
            menuWrapper.classList.remove('menu-fixed', 'hidden-on-scroll');
        }
        lastScrollY = currentScrollY;
    });
    
    // Asegurarse de que el menú se muestre si se cambia el tamaño de la ventana a escritorio
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) { // Si es vista de escritorio
            menuWrapper.classList.remove('hidden-on-scroll');
        }
    });
});

function resetBalloon(balloon) {
    if (balloon.parentElement) {
        balloon.remove();
    }
    // Resetea los estilos en línea y la clase para el próximo uso
    balloon.style.cssText = '';
    balloon.className = 'rising-balloon';
    balloonPool.push(balloon);
}

function createRisingBalloon() {
    const balloon = balloonPool.pop();
    if (!balloon) return;

    const colors = ['#fde047', '#38bdf8', '#4ade80', '#a78bfa', '#ffffff', '#fb923c', '#22d3ee'];
    const size = Math.random() * 30 + 40; // Tamaño entre 40px y 70px
    const color = colors[Math.floor(Math.random() * colors.length)];
    const duration = Math.random() * 5 + 6; // Duración entre 6s y 11s

    balloon.style.setProperty('--balloon-size', `${size}px`);
    balloon.style.setProperty('--balloon-color', color);
    balloon.style.setProperty('--balloon-duration', `${duration}s`);
    balloon.style.setProperty('--balloon-left', `${Math.random() * 95}%`);

    document.body.appendChild(balloon);

    const popHandler = () => {
        // Limpia el otro listener para evitar conflictos
        balloon.removeEventListener('animationend', endHandler);

        // --- Confeti al reventar el globo ---
        const rect = balloon.getBoundingClientRect();
        const origin = {
            x: (rect.left + rect.width / 2) / window.innerWidth,
            y: (rect.top + rect.height / 2) / window.innerHeight
        };
        confetti({
            origin: origin,
            particleCount: 50, // Una explosión más pequeña
            spread: 40,
            startVelocity: 25,
            gravity: 0.8,
            colors: ['#fde047', '#38bdf8', '#4ade80', '#a78bfa', '#ffffff']
        });

        // Captura el estado actual del globo
        const currentTransform = getComputedStyle(balloon).transform;

        // Detiene la animación CSS y congela el globo en su posición
        balloon.style.animation = 'none';
        balloon.style.transform = currentTransform;

        // Aplica la animación de "reventar"
        requestAnimationFrame(() => {
            balloon.style.transition = 'transform 0.2s ease-out, opacity 0.2s ease-out';
            balloon.style.transform = `${currentTransform} scale(1.5)`;
            balloon.style.opacity = '0';
        });

        // Después de la animación, resetea y devuelve el globo al pool
        setTimeout(() => resetBalloon(balloon), 250);
    };

    const endHandler = () => {
        balloon.removeEventListener('click', popHandler);
        resetBalloon(balloon);
    };

    balloon.addEventListener('click', popHandler, { once: true });
    balloon.addEventListener('animationend', endHandler, { once: true });
}

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
