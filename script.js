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

// Lista de palabras románticas para los corazones
const romanticWords = [
    'Amor', 'Pasión', 'Cariño', 'Ternura', 'Beso', 'Abrazo', 'Deseo', 'Dulzura',
    'Corazón', 'Alma', 'Vida', 'Cielo', 'Encanto', 'Felicidad', 'Devoción',
    'Admiración', 'Lealtad', 'Complicidad', 'Romance', 'Ilusión', 'Latido',
    'Suspiro', 'Mirada', 'Sonrisa', 'Calidez', 'Confianza', 'Magia', 'Fuego',
    'Eternidad', 'Destino', 'Unión', 'Plenitud', 'Entrega', 'Protección',
    'Inspiración', 'Belleza', 'Encanto', 'Armonía', 'Anhelo', 'Calma',
    'Fascinación', 'Serenidad', 'Amistad', 'Respeto', 'Cuidado', 'Esperanza',
    'Dulce', 'Amado/a', 'Querer', 'Enamoramiento'
];

// Colores para los corazones (rosa y rojo)
const heartColors = ['#ec4899', '#ef4444', '#f472b6', '#fb7185'];

class FloatingHeart {
    constructor(x, y) {
        this.x = x;
        this.y = y;
        this.isBroken = false;
        this.element = document.createElement('div');
        this.element.className = 'floating-heart';
        this.element.innerHTML = '<i class="fas fa-heart"></i>';
        this.element.style.left = x + 'px';
        this.element.style.top = y + 'px';
        this.element.style.color = heartColors[Math.floor(Math.random() * heartColors.length)];
        
        const duration = 7 + Math.random() * 3; // 7-10 segundos para ser más lento
        this.element.style.animationDuration = duration + 's';
        this.element.style.animationDelay = Math.random() * 0.5 + 's';
        
        this.element.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!this.isBroken) {
                this.breakHeart();
            }
        });
        
        document.getElementById('hearts-container').appendChild(this.element);
        
        // Eliminar el elemento después de que termine la animación
        this.animationTimeout = setTimeout(() => {
            if (!this.isBroken && this.element.parentElement) {
                this.element.remove();
                this.createNewHeart();
            }
        }, (duration + 1) * 1000);
    }

    breakHeart() {
        this.isBroken = true;
        clearTimeout(this.animationTimeout);
        
        // Agregar efecto de ruptura al corazón
        this.element.classList.add('heart-broken');
        
        const wordsContainer = document.getElementById('words-container');

        // Crear una sola palabra que sale hacia arriba
        const word = romanticWords[Math.floor(Math.random() * romanticWords.length)];
        const wordElement = document.createElement('div');
        // Usamos una animación simple hacia arriba (burst-1 modificada o una nueva clase si fuera necesario, 
        // pero burst-0 va hacia arriba-derecha, burst-3 hacia abajo... 
        // Vamos a usar una animación genérica de flotar hacia arriba para que sea más claro)
        
        // Pero para mantener el estilo, usaré word-border-0 pero ajustando estilo si hace falta, 
        // O mejor, defino un estilo inline o reutilizo la clase existing pero solo UNA.
        // El usuario quiere que "aparezca". 
        // Voy a usar animation 'word-float-up' que ya existe en CSS o similar?
        // En style.css vi: @keyframes word-float-up
        
        wordElement.className = 'romantic-word'; 
        // Sobreescribimos la animación para que sea consistente
        wordElement.style.animation = 'word-float-up 1.5s ease-out forwards';
        
        wordElement.textContent = word;
        
        // Centrar la palabra en el corazón
        wordElement.style.left = this.x + 'px';
        wordElement.style.top = this.y + 'px';
        wordElement.style.fontSize = '2rem'; // Un poco más grande
        wordElement.style.fontWeight = 'bold';
        
        wordsContainer.appendChild(wordElement);
        
        // Eliminar la palabra después de 1.5 segundos
        setTimeout(() => {
            if (wordElement.parentElement) {
                wordElement.remove();
            }
        }, 1500);
        
        // Eliminar el corazón después del efecto de ruptura
        setTimeout(() => {
            if (this.element.parentElement) {
                this.element.remove();
            }
            this.createNewHeart();
        }, 600);
    }

    createNewHeart() {
        const randomX = Math.random() * window.innerWidth;
        const randomY = window.innerHeight + 100;
        new FloatingHeart(randomX, randomY);
    }
}

// Inicializar corazones flotantes
function initFloatingHearts() {
    const heartsContainer = document.getElementById('hearts-container');
    
    // Crear corazones iniciales
    for (let i = 0; i < 5; i++) {
        const randomX = Math.random() * window.innerWidth;
        const randomY = window.innerHeight + Math.random() * 100;
        new FloatingHeart(randomX, randomY);
    }
}

// Iniciar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFloatingHearts);
} else {
    initFloatingHearts();
}
