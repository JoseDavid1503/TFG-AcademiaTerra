<?php session_start(); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Contacto — ACADEMIA TERRA</title>
  
  <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />
  
  <style>
      /* Estilo específico para adaptar el Textarea al Dark Theme */
      textarea {
          width: 100%;
          background: rgba(10, 15, 28, 0.6);
          color: white;
          padding: 12px 15px;
          border: 1px solid var(--border-light);
          border-radius: 8px;
          margin-bottom: 20px;
          box-sizing: border-box;
          transition: all 0.3s ease;
          font-family: inherit;
          resize: vertical; /* Permite estirar solo hacia abajo */
      }
      textarea:focus {
          outline: none;
          border-color: var(--primary);
          box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.15);
          background: rgba(10, 15, 28, 0.8);
      }
      textarea::placeholder { color: #5a668a; }
      
      label { display: block; margin-bottom: 8px; color: #e0e7ff; font-weight: 500; }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container" style="display: flex; justify-content: center; align-items: center; min-height: 75vh; padding: 40px 20px;">
    
    <section class="card" style="max-width: 600px; width: 100%; padding: 40px;">
      
      <div style="text-align:center; margin-bottom:30px;">
        <h2 style="font-size: 2.8rem; margin-bottom: 10px;">Contacto</h2>
        <p class="muted" style="font-size: 1.1rem;">Rellena el formulario para solicitar información o llámanos al <strong>+34 951 123 456</strong>.</p>
      </div>
      
      <form onsubmit="event.preventDefault(); alert('Formulario simulado. En el TFG podrías enviar esto a un servidor o guardarlo en BD.');">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0 20px;">
            <div>
                <label>Nombre completo</label>
                <input type="text" name="nombre" required placeholder="Tu nombre">
            </div>
            <div>
                <label>Correo electrónico</label>
                <input type="email" name="email" required placeholder="tu@ejemplo.com">
            </div>
        </div>
        
        <div>
            <label>Asunto</label>
            <input type="text" name="asunto" placeholder="Información sobre cursos">
        </div>
        
        <div>
            <label>Mensaje</label>
            <textarea name="mensaje" rows="6" placeholder="Escribe aquí tu consulta..."></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1.1rem; margin-top: 10px;">Enviar mensaje</button>
      </form>

    </section>

  </main>

  <?php include 'includes/footer.php'; ?> 
</body>
</html>