// Aquí puedes agregar tus scripts personalizados. Ejemplo de función para mostrar categorías:
function showCategory(category) {
  // Quita la clase activa de todos los botones
  document.querySelectorAll('.category-btn').forEach(btn => {
    btn.classList.remove('active');
  });

  // Agrega la clase activa al botón correspondiente
  const btn = Array.from(document.querySelectorAll('.category-btn')).find(b => b.getAttribute('onclick') && b.getAttribute('onclick').includes(category));
  if (btn) {
    btn.classList.add('active');
  }

  // Carga el contenido de la categoría desde su archivo HTML
  // Actualizado para buscar en la carpeta 'opciones'
  fetch(`opciones/${category}.html`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.text();
    })
    .then(html => {
      document.getElementById('menu-content').innerHTML = html;
      // Scroll to the top of the loaded content, or adjust as needed
      document.getElementById('menu-content').scrollIntoView({ behavior: 'smooth' });
    })
    .catch(error => {
      console.error('Error loading category content:', error);
      document.getElementById('menu-content').innerHTML = `<p class="text-center text-red-500 text-xl">Error al cargar la categoría "${category}".</p>`;
    });
}

// Mostrar la primera categoría por defecto al cargar
window.addEventListener('DOMContentLoaded', () => {
  showCategory('pizzas');
});
// Nuevo código para esconder el menú al hacer scroll en móvil
let lastScrollY = window.scrollY;
const menuFixed = document.querySelector('.menu-fixed');
const header = document.querySelector('header.hero-bg'); // Para conocer la altura del header

// Función para determinar si estamos en un dispositivo móvil (o pantalla pequeña)
function isMobileView() {
    return window.innerWidth < 768; // Tailwind's 'md' breakpoint
}

window.addEventListener('scroll', () => {
    // Solo aplica el efecto si estamos en vista móvil
    if (isMobileView()) {
        if (window.scrollY > lastScrollY && window.scrollY > header.offsetHeight) {
            // Scrolling down and past the header, hide the menu
            menuFixed.classList.add('hidden-on-scroll');
        } else if (window.scrollY < lastScrollY) {
            // Scrolling up, show the menu
            menuFixed.classList.remove('hidden-on-scroll');
        }
        lastScrollY = window.scrollY;
    } else {
        // Si no es vista móvil, asegúrate de que el menú esté visible
        menuFixed.classList.remove('hidden-on-scroll');
    }
});

// Asegurarse de que el menú se muestre si se cambia el tamaño de la ventana a escritorio
window.addEventListener('resize', () => {
    if (!isMobileView()) {
        menuFixed.classList.remove('hidden-on-scroll');
    }
});