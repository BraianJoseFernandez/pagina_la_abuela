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
window.addEventListener('load', () => {
    let lastScrollY = window.scrollY;
    const menuFixed = document.querySelector('.menu-fixed');
    const heroBg = document.querySelector('.hero-bg');
    if (!menuFixed || !heroBg) return; // Salir si los elementos no existen
    const heroHeight = heroBg.offsetHeight;
    
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
});

// Carga la primera categoría por defecto al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    // No se carga ninguna categoría por defecto para que el usuario elija.

    // ¡Explosión de confeti para el aniversario! Se ejecuta una vez y luego cada 4 segundos.
    createConfettiExplosion();
    setInterval(createConfettiExplosion, 4000);
    // ¡Globos flotando para el aniversario!
    // Crea un globo ascendente cada 800 milisegundos para más frecuencia
    setInterval(createRisingBalloon, 800);
});

function createConfettiExplosion() {
    const logo = document.getElementById('logo');
    if (!logo) return;

    const logoRect = logo.getBoundingClientRect();
    const originX = logoRect.left + logoRect.width / 2;
    const originY = logoRect.top + logoRect.height / 2;
    const particleCount = 150;
    const colors = ['#facc15', '#ef4444', '#3b82f6', '#22c55e', '#ec4899', '#f97316', '#8b5cf6'];

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('explosion-particle');
        document.body.appendChild(particle);

        const color = colors[Math.floor(Math.random() * colors.length)];
        particle.style.backgroundColor = color;
        particle.style.left = `${originX}px`;
        particle.style.top = `${originY}px`;

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
                particle.remove();
            }
        }
        requestAnimationFrame(animateParticle);
    }
}

function createRisingBalloon() {
    // Paleta de colores de alto contraste para que resalten sobre el fondo rojo
    const colors = ['#fde047', '#38bdf8', '#4ade80', '#a78bfa', '#ffffff', '#fb923c', '#22d3ee'];
    const balloon = document.createElement('div');
    balloon.className = 'rising-balloon';

    const size = Math.random() * 30 + 40; // Tamaño entre 40px y 70px
    const color = colors[Math.floor(Math.random() * colors.length)];
    const duration = Math.random() * 4 + 5; // Duración entre 5s y 9s

    balloon.style.left = `${Math.random() * 90}%`; // Posición horizontal aleatoria
    balloon.style.animationDuration = `${duration}s`;

    // Crear el cuerpo del globo con CSS
    const body = document.createElement('div');
    body.className = 'balloon-body'; // Clase para el reflejo
    body.style.width = `${size}px`;
    body.style.height = `${size * 1.2}px`;
    body.style.backgroundColor = color;
    body.style.borderRadius = '50% 50% 50% 50% / 60% 60% 40% 40%';

    // Crear el nudo del globo con CSS
    const knot = document.createElement('div');
    knot.style.width = '0';
    knot.style.height = '0';
    knot.style.borderStyle = 'solid';
    knot.style.borderWidth = `0 ${size / 6}px ${size / 5}px ${size / 6}px`;
    knot.style.borderColor = `transparent transparent ${color} transparent`;
    knot.style.position = 'absolute';
    knot.style.bottom = `-${size / 5}px`;
    knot.style.left = '50%';
    knot.style.transform = 'translateX(-50%)';

    balloon.appendChild(body);
    balloon.appendChild(knot); // El nudo es hijo del cuerpo, pero el cuerpo es hijo del globo. Corregimos.
    document.body.appendChild(balloon);

    // Limpiar el globo del DOM después de que termine la animación
    setTimeout(() => {
        balloon.remove();
    }, duration * 1000);
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
