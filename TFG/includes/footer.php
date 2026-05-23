<style>
  /* Ajuste específico para el nuevo grid del footer */
  .footer-content {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 3rem;
    align-items: start !important;
  }
  .footer-content div { display: block !important; }
  .footer-col h3 { color: var(--primary); margin-bottom: 1.2rem; font-size: 1.2rem; }
  .footer-col ul { list-style: none; padding: 0; margin: 0; }
  .footer-col ul li { margin-bottom: 0.8rem; }
  .footer-col a { color: var(--text-muted); text-decoration: none; transition: 0.3s; font-size: 0.95rem; }
  .footer-col a:hover { color: var(--primary); }
  .footer-bottom {
    max-width: 1200px;
    margin: 3rem auto 0;
    padding: 2rem 40px 0;
    border-top: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    color: var(--text-muted);
    font-size: 0.9rem;
  }

  /* ESTILOS DEL AVISO DE COOKIES */
  #cookie-banner {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    max-width: 800px;
    background: rgba(10, 15, 28, 0.95);
    backdrop-filter: blur(12px);
    border: 1px solid var(--primary);
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6), 0 0 15px rgba(0, 212, 255, 0.1);
    z-index: 9999;
    display: none; /* Oculto por defecto hasta que JS compruebe */
    flex-direction: column;
    gap: 15px;
  }
  .cookie-text {
    font-size: 0.95rem;
    color: var(--text-main);
    line-height: 1.5;
    margin: 0;
  }
  .cookie-text a {
    color: var(--primary);
    text-decoration: underline;
    font-weight: 500;
  }
  .cookie-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
  }
  @media (min-width: 768px) {
    #cookie-banner {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    .cookie-text { padding-right: 20px; }
  }
</style>

<footer>
  <div class="footer-content">
    <div class="footer-col">
      <h3>ACADEMIA TERRA</h3>
      <p style="font-size: 0.95rem;">Formación tecnológica de calidad con enfoque práctico y orientación laboral.</p>
    </div>
    <div class="footer-col">
      <h3>Enlaces rápidos</h3>
      <ul>
        <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>clases.php">Cursos</a></li>
        <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>login.php">Aula Virtual</a></li>
        <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>ubicacion.php">Ubicación</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3>Legal</h3>
      <ul>
        <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>aviso_legal.php">Aviso Legal</a></li>
        <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>politica_privacidad.php">Política de Privacidad</a></li>
        <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>politica_cookies.php">Política de Cookies</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3>Contacto</h3>
      <ul>
        <li><a href="mailto:info@academiaterra.es">info@academiaterra.es</a></li>
        <li><a href="tel:+34951123456">+34 951 123 456</a></li>
        <li>Calle Dr. Fernando Vivar, 2<br>29700 Vélez-Málaga</li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© 2026 Academia Terra. Todos los derechos reservados.</p>
    <p>TFG — ASIR</p>
  </div>
</footer>

<div id="cookie-banner">
    <p class="cookie-text">
        Utilizamos cookies propias y de terceros para mejorar tu experiencia de usuario y analizar el tráfico web. Al pulsar "Aceptar", consientes el uso de todas las cookies. Para más información, lee nuestra <a href="<?php echo isset($base_url) ? $base_url : ''; ?>politica_cookies.php">Política de Cookies</a>.
    </p>
    <div class="cookie-buttons">
        <button id="btn-rechazar" class="btn-outline" style="padding: 10px 20px; font-size: 0.9rem; margin: 0; width: auto;">Rechazar</button>
        <button id="btn-aceptar" class="btn btn-primary" style="padding: 10px 20px; font-size: 0.9rem; margin: 0; width: auto;">Aceptar</button>
    </div>
</div>

<script type="text/javascript">
// Lógica de Tawk.to
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/69b5a96879984a1c3cd83cbb/1jjmprftp';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();

// Lógica del Banner de Cookies
document.addEventListener("DOMContentLoaded", function() {
    var banner = document.getElementById("cookie-banner");
    var btnAceptar = document.getElementById("btn-aceptar");
    var btnRechazar = document.getElementById("btn-rechazar");

    // Comprobamos si el usuario ya ha tomado una decisión previamente
    if (!localStorage.getItem("cookieConsentido")) {
        // Si no hay registro, mostramos el banner cambiando su display a flex
        banner.style.display = "flex";
    }

    // Acción al aceptar
    btnAceptar.addEventListener("click", function() {
        localStorage.setItem("cookieConsentido", "aceptado");
        banner.style.display = "none";
    });

    // Acción al rechazar
    btnRechazar.addEventListener("click", function() {
        localStorage.setItem("cookieConsentido", "rechazado");
        banner.style.display = "none";
    });
});
</script>