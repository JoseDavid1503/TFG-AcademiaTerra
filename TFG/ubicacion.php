<?php
session_start();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ubicación — ACADEMIA TERRA</title>

    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />

    <style>
        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-light);
            margin-bottom: 30px;
        }

        .info-ubicacion {
            margin-top: 40px;
            text-align: center;
            padding: 30px;
            background: rgba(0, 212, 255, 0.05);
            border-radius: 15px;
            border: 1px dashed var(--border-light);
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="card" style="margin: 40px auto; max-width: 1000px; padding: 40px;">
            <h1 style="color: white; text-align: center; margin-bottom: 10px; font-size: 2.5rem;">Nuestra Ubicación</h1>
            <p style="text-align: center; margin-bottom: 40px; font-size: 1.1rem;" class="muted">Ven a visitarnos y descubre nuestras instalaciones en Vélez-Málaga.</p>

            <div class="map-container">
                <iframe
                    src="https://maps.google.com/maps?q=Calle+Dr.+Fernando+Vivar,+2,+29700+V%C3%A9lez-M%C3%A1laga,+M%C3%A1laga&t=&z=17&ie=UTF8&iwloc=&output=embed"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>

            <div class="info-ubicacion">
                <h3 style="color: var(--primary); margin-bottom: 15px; font-size: 1.6rem;">Academia Terra</h3>
                <p style="margin-bottom: 10px; font-size: 1.1rem;">📍 Calle Dr. Fernando Vivar, 2, 29700 Vélez-Málaga, Málaga</p>
                <p style="margin-bottom: 10px; font-size: 1.1rem;">📞 Teléfono: +34 951 123 456</p>
                <p style="font-size: 1.1rem;"><strong>🕒 Horario:</strong> Lunes a Viernes de 09:00 a 20:00</p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>

</html>