<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/config.php';
require_once '../config/db_pdo.php';
$db = DB::open();

$id_usuario = $_SESSION['user_id'];
$rol = $_SESSION['tipo_usuario'];
$tabla = ($rol === 'profesor') ? 'Profesores' : 'Alumnos';

$mensaje = "";
$error = "";

// 1. Obtener datos actuales del usuario
$user = $db->query("SELECT * FROM $tabla WHERE id = ?", [$id_usuario]);
$user = $user[0];

// 2. Lógica de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ELIMINAR FOTO DE PERFIL (NUEVO) ---
    if (isset($_POST['eliminar_foto'])) {
        if (!empty($user['foto']) && file_exists("../" . $user['foto'])) {
            unlink("../" . $user['foto']);
        }
        $db->query("UPDATE $tabla SET foto = NULL WHERE id = ?", [$id_usuario]);
        $_SESSION['foto'] = null;
        $mensaje = "Foto de perfil eliminada correctamente.";
        header("Refresh:1");
    }

    // --- CAMBIAR CONTRASEÑA ---
    if (isset($_POST['update_pass'])) {
        $pass_actual = $_POST['pass_actual'];
        $pass_nueva = $_POST['pass_nueva'];

        if (password_verify($pass_actual, $user['password'])) {
            $pass_hash = password_hash($pass_nueva, PASSWORD_BCRYPT);
            $db->query("UPDATE $tabla SET password = ? WHERE id = ?", [$pass_hash, $id_usuario]);
            $mensaje = "Contraseña actualizada correctamente.";
        } else {
            $error = "La contraseña actual no es correcta.";
        }
    }

    // --- SUBIR FOTO DE PERFIL ---
    if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['nueva_foto'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $tipos_permitidos)) {
            $ruta_dest = '../assets/uploads/perfiles/';
            if (!is_dir($ruta_dest)) mkdir($ruta_dest, 0777, true);

            $nombre_archivo = "user_" . $id_usuario . "_" . time() . "." . $ext;
            $ruta_bd = "assets/uploads/perfiles/" . $nombre_archivo;

            if (move_uploaded_file($file['tmp_name'], $ruta_dest . $nombre_archivo)) {
                if (!empty($user['foto']) && file_exists("../" . $user['foto'])) {
                    unlink("../" . $user['foto']);
                }
                $db->query("UPDATE $tabla SET foto = ? WHERE id = ?", [$ruta_bd, $id_usuario]);
                $_SESSION['foto'] = $ruta_bd;
                $mensaje = "Foto de perfil actualizada.";
                header("Refresh:1");
            }
        } else {
            $error = "Formato de imagen no permitido.";
        }
    }
}

$foto_display = !empty($user['foto']) ? "../" . $user['foto'] : "https://ui-avatars.com/api/?name=".urlencode($user['nombre'])."&background=0891b2&color=fff&size=200&length=1";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Academia Terra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>" />
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-4xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold text-white mb-8">Mi Perfil</h1>

        <?php if($mensaje): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl mb-6">
                <i class="fa-solid fa-circle-check mr-2"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl mb-6">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 text-center shadow-xl h-fit">
                <div class="relative inline-block">
                    <img src="<?php echo $foto_display; ?>" class="w-40 h-40 rounded-full object-cover border-4 border-zinc-800 shadow-2xl">
                    
                    <label for="input_foto" class="absolute bottom-1 right-1 bg-cyan-500 hover:bg-cyan-400 text-zinc-900 w-10 h-10 rounded-full flex items-center justify-center cursor-pointer transition-all shadow-lg border-4 border-zinc-900">
                        <i class="fa-solid fa-camera"></i>
                    </label>

                    <?php if(!empty($user['foto'])): ?>
                    <form action="" method="POST" class="absolute -top-1 -right-1">
                        <button type="submit" name="eliminar_foto" class="bg-red-500 hover:bg-red-400 text-white w-9 h-9 rounded-full flex items-center justify-center cursor-pointer transition-all shadow-lg border-4 border-zinc-900" title="Eliminar foto">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <form action="" method="POST" enctype="multipart/form-data" id="form_foto">
                    <input type="file" name="nueva_foto" id="input_foto" class="hidden" onchange="document.getElementById('form_foto').submit()">
                </form>
                
                <h2 class="text-xl font-bold text-white mt-6"><?php echo $user['nombre']; ?></h2>
                <div class="mt-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-cyan-500 bg-cyan-500/10 px-3 py-1 rounded-full border border-cyan-500/20">
                        <?php echo $rol; ?>
                    </span>
                </div>
                <p class="text-zinc-500 text-sm mt-4"><?php echo $user['email']; ?></p>
            </div>

            <div class="md:col-span-2 space-y-6">
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 shadow-xl">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-address-card text-cyan-500"></i> Información Personal
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs text-zinc-500 uppercase font-bold">Nombre Completo</label>
                            <p class="text-white mt-1"><?php echo $user['nombre'] . " " . $user['apellidos']; ?></p>
                        </div>
                        <div>
                            <label class="text-xs text-zinc-500 uppercase font-bold">Curso / Ciclo</label>
                            <p class="text-white mt-1"><?php echo $user['curso_matriculado'] ?? $user['curso_asignado'] ?? 'N/A'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 shadow-xl">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-shield-halved text-amber-500"></i> Seguridad
                    </h3>
                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="update_pass" value="1">
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Contraseña Actual</label>
                            <input type="password" name="pass_actual" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-white outline-none focus:border-cyan-500" required>
                        </div>
                        <div>
                            <label class="block text-sm text-zinc-400 mb-2">Nueva Contraseña</label>
                            <input type="password" name="pass_nueva" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-white outline-none focus:border-cyan-500" required>
                        </div>
                        <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 text-white font-bold py-3 px-6 rounded-xl transition-all border border-zinc-700">
                            Actualizar Seguridad
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>