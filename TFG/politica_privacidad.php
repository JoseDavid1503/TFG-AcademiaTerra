<?php session_start(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Política de Privacidad — ACADEMIA TERRA</title>
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
        <h1 style="font-size: 2.8rem; margin-bottom: 10px; text-align: center;">Política de Privacidad</h1>
        <p class="muted" style="text-align: center; margin-bottom: 40px;">Última actualización: Abril 2026</p>

        <h3>1. Responsable del Tratamiento</h3>
        <p>El responsable del tratamiento de los datos personales recogidos en esta web es <b>Academia Terra</b>, localizada en Calle Dr. Fernando Vivar, 2, 29700 Vélez-Málaga. Puedes contactar con nosotros a través del correo info@academiaterra.es.</p>

        <h3>2. Finalidad del Tratamiento</h3>
        <p>En Academia Terra tratamos la información que nos facilitan las personas interesadas con el fin de:</p>
        <ul>
            <li>Gestionar el registro y acceso de los alumnos a nuestra plataforma y aula virtual.</li>
            <li>Procesar las matriculaciones y gestionar el cobro de las tarifas académicas.</li>
            <li>Enviar comunicaciones informativas sobre cambios en el temario, horarios o novedades del centro.</li>
            <li>Responder a las consultas realizadas a través del formulario de contacto.</li>
        </ul>

        <h3>3. Legitimación</h3>
        <p>La base legal para el tratamiento de tus datos es el consentimiento que otorgas al registrarte en nuestra plataforma o al marcar las casillas de aceptación en nuestros formularios, así como la ejecución de un contrato en el caso de matriculaciones (artículo 6.1.b del RGPD).</p>

        <h3>4. Conservación de los Datos</h3>
        <p>Los datos personales proporcionados se conservarán mientras se mantenga la relación académica o mercantil, y posteriormente durante los plazos legales exigidos para el cumplimiento de obligaciones fiscales y legales (generalmente 5 años).</p>

        <h3>5. Derechos de los Usuarios</h3>
        <p>Cualquier persona tiene derecho a obtener confirmación sobre si en Academia Terra estamos tratando datos personales que les conciernan. Tienes derecho a acceder a tus datos, solicitar la rectificación de los datos inexactos o, en su caso, solicitar su supresión. Para ejercer estos derechos, puedes enviar un correo a info@academiaterra.es adjuntando una copia de tu DNI.</p>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>