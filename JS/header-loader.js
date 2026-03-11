fetch('header.php')
  .then(response => response.text())
  .then(data => {
    document.getElementById('main-header').innerHTML = data;

    // Detectar la página actual
    const links = document.querySelectorAll('#main-header .nav-list a');
    const currentPage = window.location.pathname.split("/").pop(); // obtiene 'detalles.html'

    links.forEach(link => {
      if (link.getAttribute('href') === currentPage) {
        link.classList.add('active'); // añadir clase 'active'
      }
    });
  })
  .catch(err => console.error('Error cargando header:', err)); 
