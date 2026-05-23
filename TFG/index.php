<?php session_start(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ACADEMIA TERRA — TFG ASIR</title>
  
  <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <style>
    /* Estilos específicos para la cabecera (Hero) */
    .hero {
      min-height: 80vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      text-align: center;
      padding: 0 20px;
      background: linear-gradient(rgba(10,15,28,0.7), rgba(10,15,28,0.95)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2071') center/cover no-repeat;
      border-bottom: 1px solid var(--border-light);
    }
    .hero-content {
      max-width: 1000px;
      z-index: 2;
    }
    .hero h1 {
      font-size: clamp(2.5rem, 5vw, 4.1rem);
      line-height: 1.1;
      margin-bottom: 25px;
      font-weight: 800;
    }
    .hero p {
      font-size: 1.3rem;
      max-width: 780px;
      margin: 0 auto 35px;
      color: #b0b8d0;
    }
    /* Estilos para el mapa de Google incrustado */
    .map-container {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      border: 1px solid var(--border-light);
      height: 400px; 
      width: 100%;
    }

    /* Personalización de los puntos del carrusel */
    .swiper-pagination-bullet { background: var(--text-muted); opacity: 0.5; }
    .swiper-pagination-bullet-active { background: var(--primary); opacity: 1; box-shadow: 0 0 10px rgba(0,212,255,0.5); }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <section class="hero">
    <div class="hero-content">
      <h1>Formación práctica en Tecnología.<br>Presencial y Online.</h1>
      <p>Aprende con docentes que trabajan en la industria. Accede a tu aula virtual 24/7 con clases, proyectos y certificaciones oficiales.</p>
      
      <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="clases.php" class="btn btn-primary" style="margin: 0;">Explorar cursos</a>
        <a href="login.php" class="btn-outline" style="margin: 0; padding: 10px 36px; width: auto;">Ver aula virtual</a>
      </div>
    </div>
  </section>

  <main class="container">
    
    <section id="sobre" class="card" style="margin-bottom: 60px; text-align: center; padding: 60px 40px;">
      <h2 style="font-size: 2.8rem; margin-bottom: 25px;">Sobre Nosotros</h2>
      <p class="muted" style="font-size: 1.25rem; max-width: 900px; margin: 0 auto; line-height: 1.8;">
        Academia Terra es un centro especializado en formación tecnológica de calidad, enfocado en la práctica y en la inserción laboral. Contamos con un equipo de profesionales activos en el sector que combinan experiencia real con una metodología pedagógica moderna. Ofrecemos ciclos formativos de Grado Medio y Superior, cursos especializados y preparación para certificaciones oficiales.
      </p>
    </section>

    <section style="margin-bottom: 60px;">
      <h2 style="font-size: 2.8rem; text-align: center; margin-bottom: 30px;">Cursos Destacados</h2>
      <div class="news-grid">
        <div class="class-item">
          <div class="badge medio">GRADO MEDIO</div>
          <h3 class="ciclo-title">SMR</h3>
          <p><strong>Sistemas Microinformáticos y Redes</strong></p>
          <p>Aprende a instalar y configurar software, montar equipos y asegurar redes locales pequeñas.</p>
          <a href="clases.php" class="btn-outline" style="margin-top: auto; padding: 8px 25px; width: auto;">Ver asignaturas</a>
        </div>

        <div class="class-item">
          <div class="badge superior">GRADO SUPERIOR</div>
          <h3 class="ciclo-title">ASIR</h3>
          <p><strong>Administración de Sistemas Informáticos en Red</strong></p>
          <p>Especialízate en servidores, ciberseguridad, bases de datos y administración cloud.</p>
          <a href="clases.php" class="btn-outline" style="margin-top: auto; padding: 8px 25px; width: auto;">Ver asignaturas</a>
        </div>

        <div class="class-item">
          <div class="badge superior">GRADO SUPERIOR</div>
          <h3 class="ciclo-title">DAW</h3>
          <p><strong>Desarrollo de Aplicaciones Web</strong></p>
          <p>Domina el diseño web, programación backend, bases de datos y despliegue de aplicaciones.</p>
          <a href="clases.php" class="btn-outline" style="margin-top: auto; padding: 8px 25px; width: auto;">Ver asignaturas</a>
        </div>
      </div>
    </section>

    <section class="card" style="margin-bottom: 60px; text-align: center; background: linear-gradient(135deg, rgba(0,212,255,0.05), rgba(26,35,56,0.5)); border-color: rgba(0,212,255,0.3);">
      <h2 style="font-size: 2.8rem; margin-bottom: 20px;">Tu Aula Virtual 24/7</h2>
      <p class="muted" style="font-size: 1.25rem; max-width: 820px; margin: 0 auto 30px;">Una vez registrado, tendrás acceso a nuestra plataforma online donde podrás:</p>
      <ul style="list-style: none; font-size: 1.2rem; line-height: 2.2; max-width: 680px; margin: 0 auto 40px; color: #e0e7ff; text-align: left; display: inline-block;">
        <li><i class="fas fa-check" style="color: var(--primary); margin-right: 10px;"></i> Ver clases grabadas en cualquier momento</li>
        <li><i class="fas fa-check" style="color: var(--primary); margin-right: 10px;"></i> Descargar todo el material didáctico</li>
        <li><i class="fas fa-check" style="color: var(--primary); margin-right: 10px;"></i> Entregar proyectos y prácticas</li>
        <li><i class="fas fa-check" style="color: var(--primary); margin-right: 10px;"></i> Chatear directamente con los profesores</li>
      </ul>
      <br>
      <a href="login.php" class="btn btn-primary" style="font-size: 1.15rem; padding: 16px 45px;">Acceder a mi aula virtual</a>
    </section>

    <section style="margin-bottom: 80px;">
      <h2 style="font-size: 2.8rem; text-align: center; margin-bottom: 30px;">¿Por qué elegir Academia Terra?</h2>
      <div class="news-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="card" style="text-align: center; padding: 35px 20px;">
          <i class="fas fa-chalkboard-teacher" style="font-size: 3.5rem; color: var(--primary); margin-bottom: 20px;"></i>
          <h3 style="font-size: 1.3rem; margin: 0;">Docentes activos en la industria</h3>
        </div>
        <div class="card" style="text-align: center; padding: 35px 20px;">
          <i class="fas fa-laptop-code" style="font-size: 3.5rem; color: var(--primary); margin-bottom: 20px;"></i>
          <h3 style="font-size: 1.3rem; margin: 0;">Clases prácticas y proyectos reales</h3>
        </div>
        <div class="card" style="text-align: center; padding: 35px 20px;">
          <i class="fas fa-certificate" style="font-size: 3.5rem; color: var(--primary); margin-bottom: 20px;"></i>
          <h3 style="font-size: 1.3rem; margin: 0;">Preparación oficial de certificaciones</h3>
        </div>
        <div class="card" style="text-align: center; padding: 35px 20px;">
          <i class="fas fa-clock" style="font-size: 3.5rem; color: var(--primary); margin-bottom: 20px;"></i>
          <h3 style="font-size: 1.3rem; margin: 0;">Acceso 24/7 a tu aula virtual</h3>
        </div>
      </div>
    </section>

    <section id="noticias" style="margin-bottom: 80px;">
      <div style="text-align:center; margin-bottom: 45px;">
        <h2 style="font-size: 2.8rem; margin-bottom: 15px;">Últimas Noticias</h2>
        <p class="muted" style="font-size: 1.2rem;">Mantente al día con las novedades que definen el sector tecnológico.</p>
      </div>

      <?php
      $noticias = [
          [
              'cat'    => 'IA',
              'tipo'   => 'ia',
              'titulo' => 'IA Agéntica',
              'desc'   => 'Los nuevos modelos ya no solo responden, ahora toman decisiones autónomas y ejecutan tareas complejas.',
              'link'   => 'https://www.xataka.com/categoria/inteligencia-artificial'
          ],
          [
              'cat'    => 'CIBERSEGURIDAD',
              'tipo'   => 'ciber',
              'titulo' => 'Alerta: "Shadow AI"',
              'desc'   => 'El uso de IAs no autorizadas por empleados se convierte en la principal brecha de seguridad este año.',
              'link'   => 'https://www.incibe.es/ciudadania/tematicas/ciberseguridad'
          ],
          [
              'cat'    => 'CUÁNTICA',
              'tipo'   => 'cuantica',
              'titulo' => 'Supremacía Cuántica',
              'desc'   => 'Google presenta "Willow", un chip capaz de realizar en 5 minutos cálculos de 10.000 años.',
              'link'   => 'https://www.wired.com/tag/quantum-computing/'
          ]
      ];
      ?>

      <div class="news-grid">
        <?php foreach ($noticias as $noticia): ?>
          <article class="news-item">
            <span class="news-badge <?php echo $noticia['tipo']; ?>">
              <?php echo $noticia['cat']; ?>
            </span>
            <h3 class="news-title"><?php echo $noticia['titulo']; ?></h3>
            <p class="muted"><?php echo $noticia['desc']; ?></p>
            <a href="<?php echo $noticia['link']; ?>" target="_blank" class="btn-outline" style="width: auto; padding: 8px 25px; margin-top: auto;">
              Leer noticia
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section style="margin-bottom: 80px; overflow: hidden; padding: 0 10px;">
      <h2 style="font-size: 2.8rem; text-align: center; margin-bottom: 30px;">Lo que dicen nuestros alumnos</h2>
      
      <div class="swiper testimonios-swiper" style="padding-bottom: 50px;">
        <div class="swiper-wrapper">
          
          <div class="swiper-slide">
            <div class="card" style="padding: 40px; height: 100%; text-align: left; display: flex; flex-direction: column;">
              <div style="color: var(--primary); font-size: 2.2rem; margin-bottom: 15px;"><i class="fas fa-quote-left"></i></div>
              <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 20px; flex-grow: 1;">“Gracias a Academia Terra conseguí mi primer trabajo como Junior Developer en menos de 6 meses. La plataforma online es excelente y el temario de DAW está súper actualizado.”</p>
              <div>
                  <div style="color: var(--primary); font-size: 0.9rem; margin-bottom: 10px;">
                      <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                  </div>
                  <strong style="color: var(--primary);">— Carlos Mendoza, Alumno 2025</strong>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="card" style="padding: 40px; height: 100%; text-align: left; display: flex; flex-direction: column;">
              <div style="color: var(--primary); font-size: 2.2rem; margin-bottom: 15px;"><i class="fas fa-quote-left"></i></div>
              <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 20px; flex-grow: 1;">“El curso de ASIR me preparó perfectamente para la certificación de Cisco. Los profesores son increíbles, están activos en la industria y la atención es de 10.”</p>
              <div>
                  <div style="color: var(--primary); font-size: 0.9rem; margin-bottom: 10px;">
                      <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                  </div>
                  <strong style="color: var(--primary);">— Laura Vargas, Alumna 2024</strong>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="card" style="padding: 40px; height: 100%; text-align: left; display: flex; flex-direction: column;">
              <div style="color: var(--primary); font-size: 2.2rem; margin-bottom: 15px;"><i class="fas fa-quote-left"></i></div>
              <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 20px; flex-grow: 1;">“Venía de otro centro donde todo era pura teoría. Aquí en Terra el 80% es práctica. El ciclo de SMR fue la base perfecta para empezar mi carrera en IT.”</p>
              <div>
                  <div style="color: var(--primary); font-size: 0.9rem; margin-bottom: 10px;">
                      <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                  </div>
                  <strong style="color: var(--primary);">— David Jiménez, Alumno 2025</strong>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="card" style="padding: 40px; height: 100%; text-align: left; display: flex; flex-direction: column;">
              <div style="color: var(--primary); font-size: 2.2rem; margin-bottom: 15px;"><i class="fas fa-quote-left"></i></div>
              <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 20px; flex-grow: 1;">“Trabajo a jornada completa y el plan anual me ha permitido estudiar a mi ritmo. El aula virtual es súper intuitiva y el soporte técnico responde rapidísimo.”</p>
              <div>
                  <div style="color: var(--primary); font-size: 0.9rem; margin-bottom: 10px;">
                      <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                  </div>
                  <strong style="color: var(--primary);">— Elena Castro, Alumna 2024</strong>
              </div>
            </div>
          </div>

          <div class="swiper-slide">
            <div class="card" style="padding: 40px; height: 100%; text-align: left; display: flex; flex-direction: column;">
              <div style="color: var(--primary); font-size: 2.2rem; margin-bottom: 15px;"><i class="fas fa-quote-left"></i></div>
              <p style="font-size: 1.1rem; font-style: italic; margin-bottom: 20px; flex-grow: 1;">“Entré sin saber casi nada de código y ahora estoy diseñando mis propias bases de datos. La metodología basada en proyectos reales marca toda la diferencia.”</p>
              <div>
                  <div style="color: var(--primary); font-size: 0.9rem; margin-bottom: 10px;">
                      <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                  </div>
                  <strong style="color: var(--primary);">— Miguel Sánchez, Alumno 2025</strong>
              </div>
            </div>
          </div>

        </div>
        <div class="swiper-pagination"></div>
      </div>
    </section>

    <section id="ubicacion" class="card" style="padding: 50px 40px; text-align: center; margin-bottom: 60px;">
      <h2 style="font-size: 2.8rem; margin-bottom: 20px;">Ubicación</h2>
      <p class="muted" style="font-size: 1.2rem; margin-bottom: 35px;">Nos encontramos en Calle Dr. Fernando Vivar, 2, 29700 Vélez-Málaga, Málaga.</p>
      
      <div class="map-container">
          <iframe 
                src="https://maps.google.com/maps?q=Calle+Dr.+Fernando+Vivar,+2,+29700+V%C3%A9lez-M%C3%A1laga,+M%C3%A1laga&t=&z=17&ie=UTF8&iwloc=&output=embed" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
      </div>
    </section>

    <section class="card" style="text-align:center; padding: 60px 40px; margin-bottom: 20px;">
      <h2 style="font-size: 2.5rem; margin-bottom: 15px;">¿Listo para dar el siguiente paso en tu carrera?</h2>
      <p class="muted" style="font-size: 1.3rem; margin-bottom: 35px;">Únete a más de 450 alumnos que ya están formándose con nosotros.</p>
      <a href="registro.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 18px 50px;">Registrarse ahora</a>
    </section>

  </main>

  <?php include 'includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
      // 1. Lógica del Carrusel Swiper (Se mantiene intacta)
      var swiper = new Swiper(".testimonios-swiper", {
        slidesPerView: 1, 
        spaceBetween: 30, 
        loop: true,       
        autoplay: {
          delay: 4000,    
          disableOnInteraction: false,
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        breakpoints: {
          768: {
            slidesPerView: 2, 
          }
        }
      });
  </script>

</body>
</html>