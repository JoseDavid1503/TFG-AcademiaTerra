<?php
// Usamos una RUTA ABSOLUTA para que los enlaces no se rompan nunca.
$base_url = '/TFG/'; 

// LÓGICA DEL USUARIO PARA LA CABECERA
$usuario_conectado = isset($_SESSION['user_id']);
$nombre_usuario = '';
$foto_usuario = '';
$enlace_panel = $base_url . 'login.php';

if ($usuario_conectado) {
    $nombre_usuario = $_SESSION['nombre'];
    
    // Determinamos el panel principal según el rol
    if ($_SESSION['tipo_usuario'] === 'admin') {
        $enlace_panel = $base_url . 'plataforma/admin/index.php';
    } elseif ($_SESSION['tipo_usuario'] === 'profesor') {
        $enlace_panel = $base_url . 'plataforma/profesor/index.php';
    } else {
        $enlace_panel = $base_url . 'plataforma/alumno/index.php';
    }

    // Lógica de la Foto de Perfil (Prioridad: Foto subida > Avatar por defecto)
    if (!empty($_SESSION['foto'])) {
        if (strpos($_SESSION['foto'], 'http') === 0) {
            $foto_usuario = $_SESSION['foto']; 
        } else {
            $foto_limpia = ltrim($_SESSION['foto'], '/');
            $foto_usuario = $base_url . $foto_limpia;
        }
    } else {
        $foto_usuario = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['nombre']) . '&background=0a0f1c&color=00d4ff&size=100&length=1';
    }
}
?>

<header>
    <div class="container" style="padding: 0; width: 90%; max-width: 1200px;"> 
        <nav style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
            
            <a href="<?php echo $base_url; ?>index.php" class="brand" style="display: flex; align-items: center; font-size: 1.85rem; font-weight: 800; letter-spacing: -1px; color: white; text-decoration: none;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 12px;">
                    <rect x="2" y="3" width="20" height="18" rx="3" fill="rgba(0, 212, 255, 0.1)"/> 
                    <path d="M7 8h10M7 12h6" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                ACADEMIA <span style="color: var(--primary); margin-left: 6px;">TERRA</span>
            </a>

            <div class="mobile-controls">
                <button class="menu-toggle" onclick="toggleMenu()" style="background:none; border:none; cursor:pointer; padding:0;">
                   <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="nav-links" id="navLinks">
                <a href="<?php echo $base_url; ?>index.php">Inicio</a>
                <a href="<?php echo $base_url; ?>clases.php">Cursos</a>
                <a href="<?php echo $base_url; ?>ubicacion.php">Ubicación</a>
                <a href="<?php echo $base_url; ?>contacto.php">Contacto</a>
                
                <?php if ($usuario_conectado): ?>
                    
                    <?php if ($_SESSION['tipo_usuario'] !== 'admin'): ?>
                        <a href="<?php echo $base_url; ?>plataforma/calendario.php" style="display: flex; align-items: center;">
                            <i class="fa-regular fa-calendar-days" style="margin-right: 8px;"></i> Calendario
                        </a>
                    <?php endif; ?>

                    <div style="display: flex; align-items: center; gap: 12px; margin-left: 10px; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 20px;">
                        
                        <a href="<?php echo $base_url; ?>plataforma/perfil.php" title="Configuración de Perfil" style="display: flex; align-items: center; gap: 8px; color: #a1a1aa; font-size: 0.9rem; text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#a1a1aa'">
                            <i class="fa-solid fa-gear"></i>
                            <span class="hide-mobile">Perfil</span>
                        </a>

                        <a href="<?php echo $enlace_panel; ?>" title="Mi Panel de Control" style="display: flex; align-items: center; text-decoration: none;">
                            <img src="<?php echo $foto_usuario; ?>" alt="Perfil" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); box-shadow: 0 0 15px rgba(0,212,255,0.2); transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        </a>

                        <a href="<?php echo $base_url; ?>logout.php" title="Cerrar Sesión" style="display: flex; align-items: center; padding: 8px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); transition: 0.3s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </a>
                    </div>

                <?php else: ?>
                    <div class="auth-buttons" style="display: flex; align-items: center; gap: 10px; margin-left: 15px;">
                        <a href="<?php echo $base_url; ?>login.php" style="color: white; font-weight: 500; text-decoration: none; padding: 8px 15px; transition: color 0.3s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='white'">Entrar</a>
                        <a href="<?php echo $base_url; ?>registro.php" class="btn" style="padding: 10px 22px; font-size: 0.95rem;">Registrarse gratis</a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <script>
        function toggleMenu() {
            var x = document.getElementById("navLinks");
            if (x.style.display === "flex") {
                x.style.display = "none";
            } else {
                x.style.display = "flex";
                x.style.flexDirection = "column";
                x.style.position = "absolute";
                x.style.top = "75px";
                x.style.left = "0";
                x.style.width = "100%";
                x.style.background = "rgba(10, 15, 28, 0.98)";
                x.style.backdropFilter = "blur(16px)";
                x.style.padding = "25px 20px";
                x.style.borderBottom = "1px solid var(--border-light)";
                x.style.boxShadow = "0 10px 30px rgba(0,0,0,0.5)";
                x.style.zIndex = "1000";
            }
        }
    </script>
</header>