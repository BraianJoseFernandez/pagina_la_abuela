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
