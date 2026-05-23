<?php session_start(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Aviso Legal — ACADEMIA TERRA</title>
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
        <h1 style="font-size: 2.8rem; margin-bottom: 10px; text-align: center;">Aviso Legal</h1>
        <p class="muted" style="text-align: center; margin-bottom: 40px;">Última actualización: Abril 2026</p>

        <h3>1. Información General</h3>
        <p>En cumplimiento con el deber de información recogido en artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y del Comercio Electrónico (LSSICE), a continuación se reflejan los siguientes datos: la empresa titular de dominio web es <b>Academia Terra</b> (en adelante, La Academia), con domicilio a estos efectos en Calle Dr. Fernando Vivar, 2, 29700 Vélez-Málaga, Málaga. Correo electrónico de contacto: info@academiaterra.es.</p>
        <p><i>*Nota: Este sitio web forma parte de un Trabajo de Fin de Grado (TFG) del ciclo formativo de ASIR. Los datos comerciales aquí reflejados son simulados con fines académicos.</i></p>

        <h3>2. Usuarios</h3>
        <p>El acceso y/o uso de este portal de La Academia atribuye la condición de USUARIO, que acepta, desde dicho acceso y/o uso, las Condiciones Generales de Uso aquí reflejadas.</p>

        <h3>3. Uso del Portal</h3>
        <p>El sitio web proporciona el acceso a multitud de informaciones, servicios, programas o datos (en adelante, "los contenidos") en Internet pertenecientes a La Academia a los que el USUARIO pueda tener acceso. El USUARIO asume la responsabilidad del uso del portal.</p>

        <h3>4. Propiedad Intelectual e Industrial</h3>
        <p>La Academia por sí o como cesionaria, es titular de todos los derechos de propiedad intelectual e industrial de su página web, así como de los elementos contenidos en la misma (a título enunciativo, imágenes, sonido, audio, vídeo, software o textos; marcas o logotipos, combinaciones de colores, estructura y diseño, etc.). Todos los derechos reservados.</p>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>