<?php 
session_start(); 

require_once 'config/config.php';
require_once 'config/db_pdo.php';
$db = DB::open();

// Comprobamos si es un alumno y si ya tiene un curso asignado
$ya_matriculado = false;
$curso_actual_alumno = "";

if (isset($_SESSION['user_id']) && $_SESSION['tipo_usuario'] === 'alumno') {
    $check_alumno = $db->query("SELECT curso_matriculado FROM Alumnos WHERE id = ?", [$_SESSION['user_id']]);
    if (!empty($check_alumno) && !empty($check_alumno[0]['curso_matriculado'])) {
        $ya_matriculado = true;
        $curso_actual_alumno = $check_alumno[0]['curso_matriculado'];
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ASIR - Asignaturas — ACADEMIA TERRA</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  
  <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />
  <style>
      .course-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 30px; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-light); box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
      .course-table th { background-color: rgba(0, 212, 255, 0.1); color: var(--primary); padding: 18px 15px; text-align: left; font-size: 1.1em; font-weight: 700; border-bottom: 1px solid var(--border-light); }
      .course-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--text-main); background: var(--glass); backdrop-filter: blur(8px); }
      .course-table tr:last-child td { border-bottom: none; }
      .course-table tr:hover td { background-color: rgba(255,255,255,0.02); }
      .year-title { margin-top: 50px; margin-bottom: 15px; border-left: 4px solid var(--primary); padding-left: 15px; font-size: 1.8rem; color: white; }
      
      /* Reseteo básico para que Tailwind no pise los estilos de tus botones antiguos */
      .btn-primary { cursor: pointer; border: none; font-family: inherit; }
  </style>
</head>
<body class="bg-zinc-950">
  <?php include 'includes/header.php'; ?>

  <main class="container mx-auto px-4">
    <div style="margin-bottom: 30px; margin-top: 20px;">
        <a href="clases.php" style="color: var(--primary); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
            <span style="margin-right: 5px;">←</span> Volver a Ciclos Formativos
        </a>
    </div>

    <section style="margin-bottom: 60px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div class="badge superior" style="margin-bottom: 15px;">GRADO SUPERIOR</div>
            <h1 style="font-size: 3rem; margin-bottom: 10px; color: white;">ASIR</h1>
            <p class="muted" style="font-size: 1.25rem;">Administración de Sistemas Informáticos en Red</p>
        </div>

        <h2 class="year-title">Primer Curso</h2>
        <table class="course-table">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Horas Semanales</th>
                    <th>Descripción Breve</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Implantación de Sistemas Operativos</td>
                    <td>8h</td>
                    <td>Instalación y gestión avanzada de Windows y distribuciones Linux.</td>
                </tr>
                <tr>
                    <td>Planificación y Administración de Redes</td>
                    <td>6h</td>
                    <td>Diseño físico y lógico de redes, switching, routing y VLANS.</td>
                </tr>
                <tr>
                    <td>Fundamentos de Hardware</td>
                    <td>3h</td>
                    <td>Arquitectura de ordenadores, componentes y centros de proceso de datos.</td>
                </tr>
                <tr>
                    <td>Gestión de Bases de Datos</td>
                    <td>6h</td>
                    <td>Diseño, consultas SQL y administración de SGBD como MySQL o Oracle.</td>
                </tr>
                <tr>
                    <td>Lenguajes de Marcas y Sist. Gestores de Info.</td>
                    <td>4h</td>
                    <td>HTML, CSS, XML y conceptos básicos de sindicación de contenidos.</td>
                </tr>
                <tr>
                    <td>Formación y Orientación Laboral (FOL)</td>
                    <td>3h</td>
                    <td>Legislación laboral, nóminas y prevención de riesgos laborales.</td>
                </tr>
            </tbody>
        </table>

        <h2 class="year-title">Segundo Curso</h2>
        <table class="course-table">
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Horas Semanales</th>
                    <th>Descripción Breve</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Administración de Sistemas Operativos</td>
                    <td>6h</td>
                    <td>Gestión avanzada de servidores (Active Directory, LDAP, políticas).</td>
                </tr>
                <tr>
                    <td>Servicios de Red e Internet</td>
                    <td>6h</td>
                    <td>Implantación de DNS, DHCP, servidores web (Apache/Nginx), correo.</td>
                </tr>
                <tr>
                    <td>Implantación de Aplicaciones Web</td>
                    <td>4h</td>
                    <td>Despliegue y administración de aplicaciones y arquitecturas cloud.</td>
                </tr>
                <tr>
                    <td>Administración de Bases de Datos</td>
                    <td>3h</td>
                    <td>Seguridad, rendimiento, copias de seguridad y alta disponibilidad.</td>
                </tr>
                <tr>
                    <td>Seguridad y Alta Disponibilidad</td>
                    <td>5h</td>
                    <td>Criptografía, firewalls, auditorías y planes de contingencia.</td>
                </tr>
                <tr>
                    <td>Empresa e Iniciativa Emprendedora (EIE)</td>
                    <td>4h</td>
                    <td>Plan de negocio y creación de una empresa tecnológica.</td>
                </tr>
            </tbody>
        </table>
        
        <div style="text-align: center; margin-top: 50px;">
             <h3 style="margin-bottom: 25px; color: white;">¿Preparado para administrar grandes sistemas?</h3>
             <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                
                <?php if ($ya_matriculado): ?>
                    <button type="button" onclick="abrirModalAvisoMatricula()" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.15rem;">
                        Matricularse en 1º ASIR
                    </button>
                    <button type="button" onclick="abrirModalAvisoMatricula()" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.15rem;">
                        Matricularse en 2º ASIR
                    </button>
                <?php else: ?>
                    <a href="procesar_matricula.php?curso=ASIR_1" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.15rem; display: inline-block; text-decoration: none;">
                        Matricularse en 1º ASIR
                    </a>
                    <a href="procesar_matricula.php?curso=ASIR_2" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.15rem; display: inline-block; text-decoration: none;">
                        Matricularse en 2º ASIR
                    </a>
                <?php endif; ?>

             </div>
             <p class="muted" style="margin-top: 20px; font-size: 0.9rem;">Haz clic para confirmar tu matrícula. Tendrás acceso inmediato al aula virtual.</p>
        </div>
    </section>
  </main>

  <div id="modalAvisoMatricula" class="fixed inset-0 bg-zinc-950/90 backdrop-blur-sm z-[9999] hidden flex-col justify-center items-center opacity-0 transition-opacity duration-300 px-4">
      <div class="bg-zinc-900 border border-zinc-700 w-full max-w-md rounded-3xl p-8 shadow-2xl transform scale-95 transition-transform duration-300 text-center" id="modalAvisoMatriculaCard">
          
          <div class="w-20 h-20 bg-amber-500/10 border border-amber-500/30 rounded-full flex items-center justify-center mx-auto mb-6">
              <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-500"></i>
          </div>
          
          <h2 class="text-2xl font-bold text-white mb-2">Acción denegada</h2>
          <p class="text-zinc-400 mb-6 leading-relaxed">
              Actualmente ya figuras matriculado en el curso:<br> 
              <span class="text-cyan-400 font-bold text-lg"><?php echo htmlspecialchars($curso_actual_alumno); ?></span>. 
              <br><br>
              Si deseas cambiar de ciclo, matricularte en uno nuevo, o crees que hay un error, debes ponerte en contacto con el <strong>Administrador</strong> del centro o con tu <strong>Profesor</strong>.
          </p>
          
          <button type="button" onclick="cerrarModalAvisoMatricula()" class="w-full bg-zinc-800 hover:bg-zinc-700 text-white font-bold py-3 rounded-xl transition-colors border border-zinc-700 cursor-pointer">
              Entendido
          </button>
      </div>
  </div>

  <script>
      const modalAviso = document.getElementById('modalAvisoMatricula');
      const modalAvisoCard = document.getElementById('modalAvisoMatriculaCard');

      function abrirModalAvisoMatricula() {
          modalAviso.classList.remove('hidden');
          modalAviso.classList.add('flex');
          setTimeout(() => {
              modalAviso.classList.add('opacity-100');
              modalAvisoCard.classList.remove('scale-95');
              modalAvisoCard.classList.add('scale-100');
          }, 10);
      }

      function cerrarModalAvisoMatricula() {
          modalAviso.classList.remove('opacity-100');
          modalAvisoCard.classList.remove('scale-100');
          modalAvisoCard.classList.add('scale-95');
          setTimeout(() => {
              modalAviso.classList.add('hidden');
              modalAviso.classList.remove('flex');
          }, 300);
      }
  </script>

  <?php include 'includes/footer.php'; ?> 
</body>
</html>