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
  fetch(`${filename}`) // ¡Importante: la ruta ahora incluye 'opciones/'!
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
      })
      .catch(error => {
          console.error('Error al cargar la categoría:', error);
          document.getElementById('menu-sections-container').innerHTML = `<p class="text-purple-500 text-center text-xl">Menú sin disponibilidad por el momento.</p>`;
      });
}

// Lógica para el menú fijo en scroll
let lastScrollTop = 0;
const menuFixed = document.querySelector('.menu-fixed');
const heroHeight = document.querySelector('.hero-bg').offsetHeight; // Altura del header hero

window.addEventListener('scroll', () => {
  let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

  // Si el scroll supera la altura del hero, se considera para mostrar/ocultar el menú fijo
  if (scrollTop > heroHeight) {
      if (scrollTop > lastScrollTop) {
          // Scrolling down
          menuFixed.classList.add('hidden-menu');
      } else {
          // Scrolling up
          menuFixed.classList.remove('hidden-menu');
      }
      menuFixed.classList.add('scrolled'); // Asegura que el menú fijo esté visible y con estilos de scroll
  } else {
      menuFixed.classList.remove('scrolled', 'hidden-menu'); // Elimina los estilos de scroll si volvemos al hero
  }
  lastScrollTop = scrollTop;
});

// Carga la primera categoría por defecto al cargar la página
document.addEventListener('DOMContentLoaded', () => {
  showCategory('opciones/pizzas.html');
});