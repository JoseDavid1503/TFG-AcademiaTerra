<?php session_start(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Política de Cookies — ACADEMIA TERRA</title>
  <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />
  <style>
      .legal-content h3 { color: var(--primary); margin-top: 30px; margin-bottom: 15px; font-size: 1.5rem; }
      .legal-content p { margin-bottom: 15px; color: var(--text-main); line-height: 1.8; }
      .legal-content ul { margin-bottom: 15px; margin-left: 20px; color: var(--text-main); line-height: 1.8; }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container">
    <section class="card legal-content" style="max-width: 900px; margin: 40px auto; padding: 50px;">
        <h1 style="font-size: 2.8rem; margin-bottom: 10px; text-align: center;">Política de Cookies</h1>
        <p class="muted" style="text-align: center; margin-bottom: 40px;">Última actualización: Abril 2026</p>

        <h3>1. ¿Qué son las cookies?</h3>
        <p>Una cookie es un fichero que se descarga en su ordenador al acceder a determinadas páginas web. Las cookies permiten a una página web, entre otras cosas, almacenar y recuperar información sobre los hábitos de navegación de un usuario o de su equipo y, dependiendo de la información que contengan y de la forma en que utilice su equipo, pueden utilizarse para reconocer al usuario.</p>

        <h3>2. ¿Qué tipos de cookies utiliza esta página web?</h3>
        <p>En Academia Terra utilizamos los siguientes tipos de cookies:</p>
        <ul>
            <li><b>Cookies técnicas y de sesión:</b> Son aquellas que permiten al usuario la navegación a través de la página web y la utilización de las diferentes opciones o servicios que en ella existen, como, por ejemplo, mantener la sesión iniciada en el Aula Virtual, controlar el fraude vinculado a la seguridad del servicio o utilizar elementos de seguridad durante la navegación. Estas cookies son gestionadas internamente mediante PHP Sessions.</li>
            <li><b>Cookies de análisis:</b> Son aquellas que, bien tratadas por nosotros o por terceros, nos permiten cuantificar el número de usuarios y así realizar la medición y análisis estadístico de la utilización que hacen los usuarios del servicio ofertado (por ejemplo, Google Analytics).</li>
            <li><b>Cookies de terceros:</b> Integraciones como el mapa de ubicación (Leaflet/OpenStreetMap) o el chat de soporte (Tawk.to) pueden instalar cookies en tu navegador para funcionar correctamente.</li>
        </ul>

        <h3>3. ¿Cómo gestionar o desactivar las cookies?</h3>
        <p>Puedes permitir, bloquear o eliminar las cookies instaladas en tu equipo mediante la configuración de las opciones del navegador instalado en tu ordenador:</p>
        <ul>
            <li><b>Google Chrome:</b> Configuración > Privacidad y seguridad > Cookies y otros datos de sitios.</li>
            <li><b>Mozilla Firefox:</b> Opciones > Privacidad y seguridad > Cookies y datos del sitio.</li>
            <li><b>Safari:</b> Preferencias > Privacidad > Bloquear todas las cookies.</li>
        </ul>
        <p>Si desactivas las cookies técnicas, es posible que no puedas iniciar sesión en tu panel de alumno ni acceder al aula virtual correctamente.</p>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>