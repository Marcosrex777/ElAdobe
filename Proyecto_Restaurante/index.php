<?php
require 'conectar_bd.php';
session_start();
$cliente_logueado = isset($_SESSION['cliente_id']);
$nombre_cliente = $cliente_logueado ? $_SESSION['cliente_nombre'] : null;
?>






<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Sabor de la Adobe - Restaurante</title>
    <link rel="stylesheet" href="style.css">
    
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <div class="header-content">

        <!-- Login de empleados (ícono discreto a la izquierda) -->
        <div class="header-empleado">
            <a href="./loginempleados.php" title="Iniciar sesión como empleado">
                <img src="Recursos/perfil.png" alt="Login Empleado" style="width:32px; height:32px;">
            </a>
        </div>

        <!-- Logo centrado -->
        <div class="header-logo">
            <img src="Recursos/logo.png" alt="El Adobe Logo">
        </div>

        <!-- Menú de navegación -->
        <nav>
            <ul>
                <li><button class="tab-link active" data-tab="inicio">Inicio</button></li>
                <li><button class="tab-link" data-tab="salones">Salones</button></li>
                <li><button class="tab-link" data-tab="ubicaciones">Ubicaciones</button></li>
                <li><button class="tab-link" data-tab="menu">Menú</button></li>
                <li><button class="tab-link" data-tab="contacto">Contacto</button></li>
            </ul>
        </nav>

        <!-- Área de login del cliente (visible y textual a la derecha) -->
        <div class="header-login">
            <?php if ($cliente_logueado): ?>
                <div class="cliente-info">
                    <h4>Bienvenido, <?php echo htmlspecialchars($nombre_cliente); ?></h4>
                    <a href="logout.php" class="cerrarSesion">(Cerrar sesión)</a>
                </div>
            <?php else: ?>
                <a href="./loginClientes.php" class="iniciosecion">
                    <h4>Iniciar Sesión</h4>
                </a>
            <?php endif; ?>
        </div>

        <!-- Íconos sociales -->
        <div class="social-icons">
            <a href="https://www.facebook.com/restauranteeladobe" target="_blank">
                <img src="Recursos/facebook.png" alt="Facebook">
            </a>
            <a href="https://www.instagram.com/restauranteeladobe" target="_blank">
                <img src="Recursos/instagram.png" alt="Instagram">
            </a>
            <a href="https://tripadvisor.com/tuPagina" target="_blank">
                <img src="Recursos/tripadviser.png" alt="TripAdvisor">
            </a>
        </div>

    </div>
</header>


    <main>
        <section id="inicio" class="tab-content active">
            
<div class="hero-carousel">
                <div class="carousel-item active">
                    <img src="Recursos/imagen1.png" alt="Imagen de Antigua Guatemala">
                    <div class="carousel-text">
                        <h2>Un homenaje a nuestra riqueza ancestral</h2>
                        <p>Un sueño familiar que nació en el altiplano de Guatemala, específicamente en San Juan Ostuncalco, Quetzaltenango.</p>
                    </div>
                </div>
                
<div class="carousel-item">
                    <img src="Recursos/imagen2.png" alt="Imagen de Ruinas Mayas">
                    <div class="carousel-text">
                        <h2>Tradición y Sabor</h2>
                        <p>Platillos auténticos preparados con ingredientes frescos y locales, siguiendo recetas ancestrales.</p>
                    </div>
                </div>
                
<div class="carousel-item">
                    <img src="Recursos/imagen3.png" alt="Plato de comida tradicional">
                    <div class="carousel-text">
                        <h2>El Sabor de la Adobe</h2>
                        <p>Nuestra esencia en cada plato, una experiencia que te transporta a nuestras raíces.</p>
                    </div>
                </div>
                
<button class="prev-btn"><</button>
                <button class="next-btn">></button>

                <div class="carousel-nav">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>

            
<div class="intro-section">
                <h3>SOMOS DE GUATEMALA, BIENVENIDOS</h3>
                <p>Nuestras cuatro casas están llenas de espacios y actividades para que vivas una experiencia que además del sabor, te llena de tradición.</p>
                <div class="icon-grid">
                    <div class="icon-item">
                        <img src="https://via.placeholder.com/80x80?text=Bebida" alt="Icono Bebida">
                    </div>
                    <div class="icon-item">
                        <img src="https://via.placeholder.com/80x80?text=Artesania" alt="Icono Artesanía">
                    </div>
                    <div class="icon-item">
                        <img src="https://via.placeholder.com/80x80?text=Comida" alt="Icono Comida">
                    </div>
                    <div class="icon-item">
                        <img src="https://via.placeholder.com/80x80?text=Cultura" alt="Icono Cultura">
                    </div>
                    <div class="icon-item">
                        <img src="https://via.placeholder.com/80x80?text=Maya" alt="Icono Maya">
                    </div>
                </div>
            </div>

            
<div class="restaurants-section">
                <h3>NUESTROS RESTAURANTES</h3>
                <div class="restaurant-gallery">
                    <img src="Recursos/ch.png" alt="Restaurante Interior 1">
                    <img src="Recursos/zonaviva.png" alt="Restaurante Interior 2">
                    <img src="Recursos/naranjo.png" alt="Restaurante Exterior 1">
                    <img src="Recursos/antigua.png" alt="Restaurante Exterior 2">
                </div>
            </div>
        </section>

        <section id="salones" class="tab-content">
            <div class="salones-hero">
                <img src="Recursos/salones1.png" alt="Celebraciones en Salones">
                <div class="salones-overlay">
                    <h2>¡CELEBRA CON TRADICIÓN!</h2>
                    <button class="btn-primary">CONOCE NUESTROS SALONES</button>
                </div>
            </div>
            <div class="testimonial-section">
                <div class="testimonial-content">
                    <img src="Recursos/celebrando2.png" alt="Gente celebrando">
                    <div class="testimonial-text">
                        <p class="quote">"Excellent service of Guatemalan food. "Delicious Guatemalan food! Easy access and good prices! Kind waiters and excellent service. Beautiful decorations with Guatemalan theme. Family friendly."</p>
                        <p class="author">lidiajimezsosa</p>
                    </div>
                </div>
            </div>
        </section>

 <?php
// Datos de sucursales
$sucursales = [
  [
    "id" => "zona1",
    "nombre" => "Zona 1 – Centro Histórico",
    "direccion" => "6ª Avenida 9-10, Zona 1, Ciudad de Guatemala.",
    "descripcion" => "En el corazón del Centro Histórico, un espacio colonial lleno de historia y sabor tradicional. Ideal para compartir con familia o celebrar ocasiones especiales.",
    "salones" => ["Salón Colonial","Salón Jardín","Salón Bóveda","Salón Adobe Viejo","Salón Abuelas"],
    "mensaje" => "¡Gracias por visitarnos! Que tus días estén llenos de buenos momentos y bendiciones.",
    "imagen" => "https://www.guatemala.com/fotos/201803/EL-ADOBE1-885x500.jpg"
  ],
  [
    "id" => "zona10",
    "nombre" => "Zona 10 – Zona Viva",
    "direccion" => "12 Calle 5-59, Zona 10, Ciudad de Guatemala.",
    "descripcion" => "Ubicado en una de las zonas más modernas de la ciudad, combina elegancia y calidez para reuniones, eventos privados o cenas memorables.",
    "salones" => ["Salón Ejecutivo","Salón Moderno","Salón Terraza","Salón Eventos"],
    "mensaje" => "¡Que cada encuentro aquí sea motivo de alegría y prosperidad!",
    "imagen" => "https://bde04f90fcbcc7b99af9-a4cf3e88ec567f5b6c6819f1d482f77f.ssl.cf1.rackcdn.com/16_101606_r_0.jpg?v=245.jpg"
  ],
  [
    "id" => "naranjo",
    "nombre" => "Mixco – Pasaje Naranjo",
    "direccion" => "Centro Comercial Pasaje Naranjo, Calzada San Juan, Zona 4 de Mixco.",
    "descripcion" => "Un ambiente relajado y familiar, ideal para disfrutar en compañía. No cuenta con salones adicionales para eventos.",
    "salones" => ["No aplica"],
    "mensaje" => "¡Bienvenido siempre! Que cada visita te llene de buenos sabores y bendiciones.",
    "imagen" => "https://eladobe.gt/wp-content/uploads/2023/07/ubicacion-naranjo.jpg"
  ],
  [
    "id" => "antigua",
    "nombre" => "Antigua Guatemala",
    "direccion" => "5ª Avenida Norte #10, Antigua Guatemala, Sacatepéquez.",
    "descripcion" => "En una casona colonial con encanto histórico, donde cada rincón respira tradición y cultura. No cuenta con salones adicionales.",
    "salones" => ["No aplica"],
    "mensaje" => "¡Gracias por elegirnos! Que la magia de Antigua acompañe tus momentos y tus bendiciones.",
    "imagen" => "https://www.vidaantigua.com/wp-content/uploads/2024/02/El-Adobe-Antigua-Courtyard.jpg"
  ]
];
?>

<section id="ubicaciones" class="tab-content">
  <div class="locations-section">
    <div class="location-banner">
      <h2>NUESTRAS UBICACIONES</h2>
      <img src="Recursos/hojas.png" alt="Fondo de hojas decorativas">
    </div>
  </div>

  <div id="carousel-ubicaciones" class="carousel">
    <button class="carousel-prev" aria-label="Anterior">‹</button>
    <button class="carousel-next" aria-label="Siguiente">›</button>

    <div class="carousel-track">
      <?php foreach($sucursales as $sucursal): ?>
        <article class="slide" data-sucursal="<?= $sucursal['id'] ?>">
          <div class="card-sucursal">
            <div class="card-media">
              <img src="<?= $sucursal['imagen'] ?>" alt="<?= $sucursal['nombre'] ?>">
              <div class="media-actions">
                <a href="reservaciones.php?sucursal=<?= $sucursal['id'] ?>" class="btn btn-primary">Reservar</a>
                <button class="btn btn-secondary btn-ver-mas" data-target="<?= $sucursal['id'] ?>">Ver más</button>
              </div>
            </div>
            <div class="card-body">
              <h3>Sucursal: <?= $sucursal['nombre'] ?></h3>
              <p><strong>Dirección:</strong> <?= $sucursal['direccion'] ?></p>
              <p><strong>Descripción:</strong> <?= $sucursal['descripcion'] ?></p>
              <div class="card-more" id="more-<?= $sucursal['id'] ?>" hidden>
                <h4>Salones disponibles:</h4>
                <ul>
                  <?php foreach($sucursal['salones'] as $salon): ?>
                    <li><?= $salon ?></li>
                  <?php endforeach; ?>
                </ul>
                <p class="mensaje"><?= $sucursal['mensaje'] ?></p>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
(function(){
  const principal = document.getElementById('carousel-ubicaciones');
  const slides = Array.from(principal.querySelectorAll('.slide'));
  let idx = 0;
  let auto = null;
  const INTERVAL = 5000;

  function showSlide(i){
    slides.forEach((s,n)=> {
      s.classList.remove('active');
      if(n===i) s.classList.add('active');
    });
    idx = i;
  }

  function next(){ showSlide((idx+1)%slides.length); }
  function prev(){ showSlide((idx-1+slides.length)%slides.length); }

  function startAuto(){
    if(auto) return;
    auto = setInterval(next, INTERVAL);
  }
  function stopAuto(){
    clearInterval(auto);
    auto = null;
  }

  // Botones
  principal.querySelector('.carousel-next').addEventListener('click', next);
  principal.querySelector('.carousel-prev').addEventListener('click', prev);

  // Hover pausa
  principal.addEventListener('mouseenter', stopAuto);
  principal.addEventListener('mouseleave', startAuto);

  // Botón "Ver más"
  document.querySelectorAll('.btn-ver-mas').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const target = document.getElementById('more-'+btn.dataset.target);
      if(target.hasAttribute('hidden')) {
        target.removeAttribute('hidden');
        btn.textContent = "Ver menos";
      } else {
        target.setAttribute('hidden','');
        btn.textContent = "Ver más";
      }
    });
  });

  // Init
  document.addEventListener('DOMContentLoaded', ()=>{
    showSlide(0);
    startAuto();
  });
})();
</script>

        <section id="menu" class="tab-content">
            <h2>Nuestro Menú</h2>
            <div class="menu-category">
                <h3>Platos Fuertes</h3>
                <div class="menu-item">
                    <h4>Mole Poblano</h4>
                    <p>El clásico mole poblano, con un toque casero y exquisito. Servido con arroz rojo.<span>$15.00</span></p>
                </div>
                <div class="menu-item">
                    <h4>Pepián de Pollo</h4>
                    <p>Estofado tradicional guatemalteco de pollo con especias y vegetales.<span>$14.50</span></p>
                </div>
            </div>
            <div class="menu-category">
                <h3>Entradas</h3>
                <div class="menu-item">
                    <h4>Quesadillas de Flor de Calabaza</h4>
                    <p>Quesadillas fritas de maíz rellenas de flor de calabaza y queso Oaxaca.<span>$8.50</span></p>
                </div>
                <div class="menu-item">
                    <h4>Tamales Colorados</h4>
                    <p>Tamales de masa de maíz rellenos de cerdo o pollo, envueltos en hoja de plátano.<span>$7.00</span></p>
                </div>
            </div>
            <div class="menu-category">
                <h3>Postres</h3>
                <div class="menu-item">
                    <h4>Pastel de Tres Leches</h4>
                    <p>Esponjoso pastel bañado en tres tipos de leche, decorado con merengue.<span>$6.00</span></p>
                </div>
                <div class="menu-item">
                    <h4>Buñuelos con Miel</h4>
                    <p>Buñuelos fritos bañados en miel de piloncillo.<span>$5.50</span></p>
                </div>
            </div>
            <div class="menu-category">
                <h3>Bebidas</h3>
                <div class="menu-item">
                    <h4>Agua de Jamaica</h4>
                    <p>Refrescante agua fresca preparada con flor de jamaica.<span>$3.00</span></p>
                </div>
                <div class="menu-item">
                    <h4>Atol de Elote</h4>
                    <p>Bebida caliente y dulce a base de maíz tierno.<span>$4.00</span></p>
                </div>
            </div>
        </section>

        <section id="contacto" class="tab-content">
            <h2>Contáctanos</h2>
            <p>Estamos ansiosos por escucharte y atender todas tus preguntas.</p>

            <form action="#" method="post" class="contact-form">
                <h3>Envíanos un mensaje</h3>
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="mensaje">Mensaje:</label>
                <textarea id="mensaje" name="mensaje" rows="6" required></textarea>
                <button type="submit" class="btn-primary">Enviar Mensaje</button>
            </form>

            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3862.0000000000004!2d-90.51860000000001!3d14.600000000000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8589a1c1d014389d%3A0xc3f1f7e346b0a64d!2sEl%20Adobe%20Restaurante!5e0!3m2!1sen!2sgt!4v1628100000000!5m2!1sen!2sgt" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="Recursos/logo.png" alt="El Adobe Logo Footer">
                <span class="logo-text">EL ADOBE</span>
            </div>
            <nav class="footer-nav">
                <ul>
                    <li><a href="#" class="tab-link-footer" data-tab="inicio">Espíritu</a></li>
                    <li><a href="#" class="tab-link-footer" data-tab="salones">Salones</a></li>
                    <li><a href="#" class="tab-link-footer" data-tab="ubicaciones">Ubicaciones</a></li>
                    <li><a href="#" class="tab-link-footer" data-tab="menu">Menú</a></li>
                    <li><a href="#" class="tab-link-footer" data-tab="contacto">Contacto</a></li>
                </ul>
            </nav>
            <div class="social-icons footer-social">
                <a href="https://www.facebook.com/restauranteeladobe" target="_blank">
					<img src="Recursos/facebook.png" alt="Facebook">
				</a>
				<a href="https://www.instagram.com/restauranteeladobe" target="_blank">
					<img src="Recursos/instagram.png" alt="Instagram">
				</a>
				<a href="https://tripadvisor.com/tuPagina" target="_blank">
					<img src="Recursos/tripadviser.png" alt="TripAdvisor">
				</a>
            </div>
        </div>
        <p class="copy-right">© <?php echo date("Y"); ?> El Sabor de la Adobe. Todos los derechos reservados.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
