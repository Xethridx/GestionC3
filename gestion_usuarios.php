<?php
session_start();
include 'conexion.php';

// Verificar permisos: Solo administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

$mensaje = ""; // Para mensajes de éxito o error

// --- Funciones para Auditoría ---
function registrarAuditoria($conn, $idUsuario, $accion, $detalles = null) {
    $stmtAuditoria = $conn->prepare("INSERT INTO auditoria_usuarios (idUsuario, Accion, Detalles) VALUES (:idUsuario, :accion, :detalles)");
    $stmtAuditoria->execute([':idUsuario' => $idUsuario, ':accion' => $accion, ':detalles' => $detalles]);
}

// --- Procesamiento de Formulario: Crear Usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nUsuario = $_POST['nUsuario'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // Encriptar contraseña
    $nombre = $_POST['nombre'];
    $apellidoP = $_POST['apellidoP'];
    $apellidoM = $_POST['apellidoM'] ?? null;
    $fechaNac = $_POST['fechaNac'] ?? null;
    $curp = $_POST['curp'];
    $correo = $_POST['correo'];
    $tipoUsuario = $_POST['tipoUsuario'];

    try {
        $stmtInsertar = $conn->prepare("
            INSERT INTO usuarios (NUsuario, Contraseña, Nombre, ApellidoP, ApellidoM, FechaNac, CURP, Correo, TipoUsuario)
            VALUES (:nUsuario, :contraseña, :nombre, :apellidoP, :apellidoM, :fechaNac, :curp, :correo, :tipoUsuario)
        ");
        $stmtInsertar->execute([
            ':nUsuario' => $nUsuario,
            ':contraseña' => $contraseña,
            ':nombre' => $nombre,
            ':apellidoP' => $apellidoP,
            ':apellidoM' => $apellidoM,
            ':fechaNac' => $fechaNac,
            ':curp' => $curp,
            ':correo' => $correo,
            ':tipoUsuario' => $tipoUsuario
        ]);
        registrarAuditoria($conn, $_SESSION['idUsuario'], 'Creación de Usuario', "Usuario creado: NUsuario={$nUsuario}, Nombre={$nombre} {$apellidoP}");
        $mensaje = "<div class='alert alert-success'>Usuario creado correctamente.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error al crear usuario: " . $e->getMessage() . "</div>";
    }
}

// --- Procesamiento de Formulario: Editar Usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $idUsuarioEditar = $_POST['idUsuarioEditar'];
    $nombre = $_POST['nombre'];
    $apellidoP = $_POST['apellidoP'];
    $apellidoM = $_POST['apellidoM'] ?? null;
    $fechaNac = $_POST['fechaNac'] ?? null;
    $curp = $_POST['curp'];
    $correo = $_POST['correo'];
    $tipoUsuario = $_POST['tipoUsuario'];

    try {
        $stmtActualizar = $conn->prepare("
            UPDATE usuarios SET Nombre = :nombre, ApellidoP = :apellidoP, ApellidoM = :apellidoM,
                                FechaNac = :fechaNac, CURP = :curp, Correo = :correo, TipoUsuario = :tipoUsuario
            WHERE idUsuario = :idUsuarioEditar
        ");
        $stmtActualizar->execute([
            ':idUsuarioEditar' => $idUsuarioEditar,
            ':nombre' => $nombre,
            ':apellidoP' => $apellidoP,
            ':apellidoM' => $apellidoM,
            ':fechaNac' => $fechaNac,
            ':curp' => $curp,
            ':correo' => $correo,
            ':tipoUsuario' => $tipoUsuario
        ]);
        registrarAuditoria($conn, $_SESSION['idUsuario'], 'Edición de Usuario', "Usuario editado: ID={$idUsuarioEditar}, Nombre={$nombre} {$apellidoP}");
        $mensaje = "<div class='alert alert-success'>Usuario actualizado correctamente.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error al actualizar usuario: " . $e->getMessage() . "</div>";
    }
}

// --- Procesamiento de Formulario: Eliminar Usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_usuario'])) {
    $idUsuarioEliminar = $_POST['idUsuarioEliminar'];
    if ($idUsuarioEliminar == $_SESSION['idUsuario']) {
        $mensaje = "<div class='alert alert-warning'>No puedes eliminar tu propio usuario.</div>";
    } else {
        try {
            $stmtEliminar = $conn->prepare("DELETE FROM usuarios WHERE idUsuario = :idUsuarioEliminar");
            $stmtEliminar->execute([':idUsuarioEliminar' => $idUsuarioEliminar]);
            registrarAuditoria($conn, $_SESSION['idUsuario'], 'Eliminación de Usuario', "Usuario eliminado: ID={$idUsuarioEliminar}");
            $mensaje = "<div class='alert alert-success'>Usuario eliminado correctamente.</div>";
        } catch (PDOException $e) {
            $mensaje = "<div class='alert alert-danger'>Error al eliminar usuario: " . $e->getMessage() . "</div>";
        }
    }
}
// --- Función para Generar Contraseña Aleatoria (Añadir esta función en gestion_usuarios.php) ---
function generarContraseñaAleatoria($longitud = 12) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_+={}[]\|;:\'",.<>/?';
    $contraseña = '';
    $max = strlen($caracteres) - 1;
    for ($i = 0; $i < $longitud; $i++) {
        $contraseña .= $caracteres[random_int(0, $max)];
    }
    return $contraseña;
}


// --- Procesamiento de Formulario: Resetear Contraseña (MODIFICADO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resetear_contraseña'])) {
    $idUsuarioResetear = $_POST['idUsuarioResetear'];

    // Generar contraseña aleatoria
    $nuevaContraseñaTexto = generarContraseñaAleatoria();
    $nuevaContraseñaHash = password_hash($nuevaContraseñaTexto, PASSWORD_DEFAULT);

    try {
        $stmtResetear = $conn->prepare("UPDATE usuarios SET Contraseña = :nuevaContraseña WHERE idUsuario = :idUsuarioResetear");
        $stmtResetear->execute([':nuevaContraseña' => $nuevaContraseñaHash, ':idUsuarioResetear' => $idUsuarioResetear]);

        // Obtener correo electrónico del usuario
        $stmtObtenerCorreo = $conn->prepare("SELECT NUsuario, Correo FROM usuarios WHERE idUsuario = :idUsuarioResetear");
        $stmtObtenerCorreo->execute([':idUsuarioResetear' => $idUsuarioResetear]);
        $usuario = $stmtObtenerCorreo->fetch(PDO::FETCH_ASSOC);
        $correoUsuario = $usuario['Correo'];
        $nombreUsuario = $usuario['NUsuario'];

        // Enviar correo electrónico (¡Configura tus datos de envío!)
        $asunto = "Contraseña Reseteada - Sistema de Carga de Documentos";
        $mensajeCorreo = "Hola " . $nombreUsuario . ",\n\n";
        $mensajeCorreo .= "Tu contraseña para el Sistema de Carga de Documentos ha sido reseteada temporalmente por un administrador.\n";
        $mensajeCorreo .= "Tu nueva contraseña temporal es: " . $nuevaContraseñaTexto . "\n\n";
        $mensajeCorreo .= "Por favor, inicia sesión con esta contraseña y cámbiala inmediatamente en tu perfil.\n\n";
        $mensajeCorreo .= "Atentamente,\nEl equipo de Soporte del Sistema";
        $cabeceras = 'From: tu_correo_de_envio@example.com' . "\r\n" .    // <--- ¡Reemplaza con tu correo de envío!
                     'Reply-To: tu_correo_de_envio@example.com' . "\r\n" . // <--- ¡Reemplaza con tu correo de envío!
                     'X-Mailer: PHP/' . phpversion();

        $enviado = mail($correoUsuario, $asunto, $mensajeCorreo, $cabeceras);

        if ($enviado) {
            registrarAuditoria($conn, $_SESSION['idUsuario'], 'Reseteo de Contraseña', "Contraseña reseteada para usuario ID={$idUsuarioResetear}, nueva contraseña enviada por correo a {$correoUsuario}");
            $mensaje = "<div class='alert alert-success'>Contraseña reseteada y enviada a <strong>" . htmlspecialchars($correoUsuario) . "</strong> correctamente. El usuario deberá cambiarla al iniciar sesión.</div>";
        } else {
            $mensaje = "<div class='alert alert-warning'>Contraseña reseteada, pero hubo un error al enviar el correo electrónico a <strong>" . htmlspecialchars($correoUsuario) . "</strong>. Por favor, informa al usuario la nueva contraseña temporal manualmente.</div>";
             registrarAuditoria($conn, $_SESSION['idUsuario'], 'Reseteo de Contraseña (Correo Fallido)', "Contraseña reseteada para usuario ID={$idUsuarioResetear}, error al enviar correo a {$correoUsuario}. Contraseña temporal: {$nuevaContraseñaTexto}");
        }


    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error al resetear contraseña: " . $e->getMessage() . "</div>";
    }
}

// --- Procesamiento de Formulario: Resetear Contraseña ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resetear_contraseña'])) {
    $idUsuarioResetear = $_POST['idUsuarioResetear'];
    $nuevaContraseña = password_hash('12345678', PASSWORD_DEFAULT); // Contraseña por defecto: 12345678
    try {
        $stmtResetear = $conn->prepare("UPDATE usuarios SET Contraseña = :nuevaContraseña WHERE idUsuario = :idUsuarioResetear");
$stmtResetear->execute([':nuevaContraseña' => $nuevaContraseña, ':idUsuarioResetear' => $idUsuarioResetear]); // <--- Corregir aquí, mantener 'idUsuarioResetear'
        registrarAuditoria($conn, $_SESSION['idUsuario'], 'Reseteo de Contraseña', "Contraseña reseteada para usuario ID={$idUsuarioResetear}, a contraseña por defecto.");
        $mensaje = "<div class='alert alert-success'>Contraseña reseteada a '12345678' correctamente.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error al resetear contraseña: " . $e->getMessage() . "</div>";
    }
}


// --- Obtener Listado de Usuarios ---
try {
    $stmtUsuarios = $conn->query("SELECT * FROM usuarios ORDER BY ApellidoP, ApellidoM, Nombre");
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "<div class='alert alert-danger'>Error al cargar listado de usuarios: " . $e->getMessage() . "</div>";
    $usuarios = []; // Evitar errores si no se obtienen usuarios
}

$tiposUsuario = ['administrador', 'gestor', 'administrativo', 'enlace', 'coordinacion']; // Opciones de TipoUsuario
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="text-center mb-4"><i class="fas fa-users-cog me-2"></i> Gestión de Usuarios</h1>

        <?php if ($mensaje): ?>
            <?php echo $mensaje; ?>
        <?php endif; ?>

        <section class="mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                <i class="fas fa-user-plus me-2"></i> Crear Nuevo Usuario
            </button>
        </section>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>CURP</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="7" class="text-center">No hay usuarios registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $index => $usuario): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($usuario['NUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['ApellidoP'] . ' ' . $usuario['ApellidoM']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['CURP']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Correo']); ?></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($usuario['TipoUsuario']); ?></span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario<?php echo $usuario['idUsuario']; ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEliminarUsuario<?php echo $usuario['idUsuario']; ?>">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalResetearContraseña<?php echo $usuario['idUsuario']; ?>">
                                            <i class="fas fa-key"></i> Resetear Contraseña
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <?php include 'modal_editar_usuario.php'; ?>
                            <?php include 'modal_eliminar_usuario.php'; ?>
                            <?php include 'modal_resetear_contraseña.php'; ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php include 'modal_crear_usuario.php'; ?>

    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>