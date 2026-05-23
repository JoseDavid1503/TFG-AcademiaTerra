<?php session_start(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Oferta Educativa — ACADEMIA TERRA</title>
  <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container">
    <section class="card">
      <div style="text-align:center; margin-bottom: 30px;">
        <h2>Nuestros Ciclos Formativos</h2>
        <p class="muted">Elige tu itinerario profesional. Al matricularte en un ciclo podrás seleccionar las asignaturas sueltas o el curso completo.</p>
      </div>
      
      <?php
      // Array de Ciclos Formativos
      $ciclos = [
          [
              'siglas' => 'SMR',
              'nombre' => 'Sistemas Microinformáticos y Redes',
              'nivel'  => 'Grado Medio',
              'tipo'   => 'medio', // Para el color de la etiqueta (CSS)
              'desc'   => 'Aprende a instalar y configurar software, montar equipos y asegurar redes locales pequeñas.'
          ],
          [
              'siglas' => 'ASIR',
              'nombre' => 'Ad. de Sistemas Informáticos en Red',
              'nivel'  => 'Grado Superior',
              'tipo'   => 'superior',
              'desc'   => 'Especialízate en servidores, ciberseguridad, bases de datos y administración cloud.'
          ],
          [
              'siglas' => 'DAW',
              'nombre' => 'Desarrollo de Aplicaciones Web',
              'nivel'  => 'Grado Superior',
              'tipo'   => 'superior',
              'desc'   => 'Domina el diseño web, programación backend, bases de datos y despliegue de aplicaciones.'
          ]
      ];
      ?>

      <div class="class-list">
        <?php foreach ($ciclos as $ciclo): ?>
            <article class="class-item ciclo-card">
              <span class="badge <?php echo $ciclo['tipo']; ?>">
                <?php echo $ciclo['nivel']; ?>
              </span>
              
              <h3 class="ciclo-title">
                <span class="text-blue"><?php echo $ciclo['siglas']; ?></span> 
                <br> 
                <span style="font-size:0.7em; color:#444"><?php echo $ciclo['nombre']; ?></span>
              </h3>
              
              <p class="muted"><?php echo $ciclo['desc']; ?></p>
              
              <a href="<?php echo strtolower($ciclo['siglas']); ?>.php" class="btn-outline">
                Ver asignaturas
              </a>
            </article>
        <?php endforeach; ?>
      </div>

    </section>
  </main>

  <?php include 'includes/footer.php'; ?> 
</body>
</html>