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

// Función para crear calabazas
function createPumpkin() {
    const pumpkin = document.createElement('img');
    pumpkin.src = '/imagenes/halloween/calabaza.png'; // Usa tu SVG
    pumpkin.alt = 'Calabaza';
    pumpkin.className = 'pumpkin-svg pumpkin';
    pumpkin.style.position = 'absolute';
    pumpkin.style.left = Math.random() * 80 + 10 + '%';
    pumpkin.style.top = Math.random() * 60 + 20 + '%';
    pumpkin.style.width = '180px';
    pumpkin.style.height = '120px';
    pumpkin.style.zIndex = 100;

    document.body.appendChild(pumpkin);

    pumpkin.addEventListener('click', () => {
        createCandies(pumpkin);
        pumpkin.remove();
    });

    setTimeout(() => {
        if (pumpkin.parentElement) {
            pumpkin.classList.add('pumpkin-fadeout');
            setTimeout(() => pumpkin.remove(), 800);
        }
    }, 5000);
}

function createCandies(pumpkin) {
    const rect = pumpkin.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    
    const candyColors = [
        ['#ff0000', '#ff6666'],
        ['#ff6600', '#ffcc00'],
        ['#ff3399', '#ff99cc'],
        ['#6600cc', '#9933ff'],
        ['#00ff00', '#66ff66']
    ];

    for (let i = 0; i < 12; i++) {
        const candy = document.createElement('div');
        candy.className = 'candy';
        const colorPair = candyColors[Math.floor(Math.random() * candyColors.length)];
        candy.style.setProperty('--candy-color-1', colorPair[0]);
        candy.style.setProperty('--candy-color-2', colorPair[1]);
        
        document.body.appendChild(candy);
        
        const angle = (Math.PI * 2 * i) / 12;
        const velocity = 8;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity;
        
        candy.style.left = centerX + 'px';
        candy.style.top = centerY + 'px';
        
        animateCandy(candy, centerX, centerY, vx, vy);
    }
}

function animateCandy(candy, startX, startY, vx, vy) {
    let posX = startX;
    let posY = startY;
    let time = 0;
    
    function animate() {
        time += 1/60;
        posX += vx;
        posY += vy + (9.81 * time * 15); // Gravedad aumentada
        
        candy.style.transform = `translate(${posX - startX}px, ${posY - startY}px)`;
        
        if (posY < window.innerHeight) {
            requestAnimationFrame(animate);
        } else {
            candy.remove();
        }
    }
    
    requestAnimationFrame(animate);
}

// Crear calabazas periódicamente
window.addEventListener('load', () => {
    setInterval(createPumpkin, 3000); // Crear una nueva calabaza cada 3 segundos
});

// --- INICIO DE CAMBIOS EN LA ARAÑA ---

window.addEventListener('DOMContentLoaded', () => {
    const spider = document.getElementById('spider-svg');
    const thread = document.getElementById('spider-thread');
    let direction = 1;
    let pos = 400; // Posición inicial
    let falling = false;

    function animateSpider() {
        if (!falling) {
            pos += direction * 0.5; 
            
            if (pos > 430) direction = -1; // Rango de 30px (400 a 430)
            if (pos < 400) direction = 1;

            spider.style.top = pos + 'px';
            thread.style.height = (pos + 100) + 'px'; 
            
            requestAnimationFrame(animateSpider);
        }
    }
    
    if (spider && thread) {
        // Asegurarse de que el hilo tenga la altura correcta al cargar
        thread.style.height = (pos + 100) + 'px';
        animateSpider();
    }

    // CAMBIO: Lógica de caída y reaparición
    spider.addEventListener('click', () => {
        // 1. Detener la animación de balanceo
        falling = true;
        
        // 2. Ocultar el hilo inmediatamente
        thread.style.display = 'none';
        
        let fallPos = parseInt(spider.style.top) || 400; 
        
        function fall() {
            fallPos += 8; // Velocidad de caída
            spider.style.top = fallPos + 'px';
            
            // 3. Comprobar si sigue en pantalla (window.innerHeight es la altura total de la ventana)
            if (fallPos < window.innerHeight) { 
                requestAnimationFrame(fall);
            } else {
                // 4. Si choca o sale de pantalla, ocultar la araña
                spider.style.display = 'none';
                
                // 5. Esperar 5 segundos para reaparecer
                setTimeout(() => {
                    // Restablecer todo a la posición inicial
                    pos = 400; // Posición 'top' inicial
                    spider.style.top = pos + 'px';
                    spider.style.display = 'block'; // Mostrar araña
                    
                    thread.style.height = (pos + 100) + 'px'; // Altura inicial del hilo
                    thread.style.display = 'block'; // Mostrar hilo
                    
                    falling = false; // Permitir que la animación de balanceo se reanude
                    
                    // Reiniciar la animación de balanceo
                    animateSpider(); 
                }, 5000); // 5000 ms = 5 segundos
            }
        }
        fall();
    });
});
// --- FIN DE CAMBIOS EN LA ARAÑA ---

