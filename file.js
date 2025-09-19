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

// --- Optimización de Confeti: Object Pooling ---
const confettiPool = [];
const MAX_CONFETTI_PARTICLES = 200; // Un poco más de los 150 que se usan

function initializeConfettiPool() {
    for (let i = 0; i < MAX_CONFETTI_PARTICLES; i++) {
        const particle = document.createElement('div');
        particle.classList.add('explosion-particle');
        confettiPool.push(particle);
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
        confettiOrigin.x = logoRect.left + logoRect.width / 2 + window.scrollX;
        confettiOrigin.y = logoRect.top + logoRect.height / 2 + window.scrollY;
    }

    // Pre-crea los divs del confeti para reutilizarlos
    initializeConfettiPool();

    // Iniciar las animaciones de aniversario DESPUÉS de calcular la posición del logo.
    // ¡Explosión de confeti para el aniversario!
    createConfettiExplosion();
    setInterval(createConfettiExplosion, 4000);

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

function createConfettiExplosion() {
    const originX = confettiOrigin.x;
    const originY = confettiOrigin.y;
    const particleCount = 150;
    const colors = ['#facc15', '#ef4444', '#3b82f6', '#22c55e', '#ec4899', '#f97316', '#8b5cf6'];

    for (let i = 0; i < particleCount; i++) {
        // Reutiliza una partícula del pool en lugar de crear una nueva
        const particle = confettiPool.pop();
        if (!particle) continue; // Si el pool está vacío, no hace nada

        document.body.appendChild(particle);

        const color = colors[Math.floor(Math.random() * colors.length)];
        particle.style.backgroundColor = color;
        particle.style.left = `${originX}px`;
        particle.style.top = `${originY}px`;
        particle.style.opacity = '1';
        particle.style.display = 'block';

        const angle = Math.random() * Math.PI * 2;
        const velocity = Math.random() * 8 + 4; // Velocidad inicial
        let vx = Math.cos(angle) * velocity;
        let vy = Math.sin(angle) * velocity;
        const gravity = 0.1;
        const friction = 0.98;
        let rotation = Math.random() * 360;
        const rotationSpeed = Math.random() * 20 - 10;

        let currentX = originX;
        let currentY = originY;
        let life = 120; // Frames de vida

        function animateParticle() {
            currentX += vx;
            currentY += vy;
            vy += gravity;
            vx *= friction;
            vy *= friction;
            rotation += rotationSpeed;
            life--;

            particle.style.transform = `translate(${currentX - originX}px, ${currentY - originY}px) rotate(${rotation}deg)`;
            particle.style.opacity = life / 120;

            if (life > 0) {
                requestAnimationFrame(animateParticle);
            } else {
                // En lugar de destruir el div, lo devolvemos al pool para reutilizarlo
                if (particle.parentElement) particle.remove();
                confettiPool.push(particle);
            }
        }
        requestAnimationFrame(animateParticle);
    }
}

function createRisingBalloon() {
    const colors = ['#fde047', '#38bdf8', '#4ade80', '#a78bfa', '#ffffff', '#fb923c', '#22d3ee'];
    const balloon = document.createElement('div');
    balloon.className = 'rising-balloon';

    const size = Math.random() * 30 + 40; // Tamaño entre 40px y 70px
    const color = colors[Math.floor(Math.random() * colors.length)];
    const duration = Math.random() * 5 + 6; // Duración entre 6s y 11s

    // Asignar valores a las variables CSS para un renderizado más eficiente
    balloon.style.setProperty('--balloon-size', `${size}px`);
    balloon.style.setProperty('--balloon-color', color);
    balloon.style.setProperty('--balloon-duration', `${duration}s`);
    balloon.style.setProperty('--balloon-left', `${Math.random() * 95}%`);
    
    document.body.appendChild(balloon);

    // Limpiar el globo del DOM después de que termine la animación
    balloon.addEventListener('animationend', () => {
        balloon.remove();
    });
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
