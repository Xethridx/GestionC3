<?php
session_start();
require 'conexion.php';
/*
// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['TipoUsuario']) || $_SESSION['TipoUsuario'] !== 'Administrador') {
    header("Location: index.php");
    exit;
}

// Función para generar una contraseña aleatoria
function generarContrasenaAleatoria($longitud = 12) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    return substr(str_shuffle($caracteres), 0, $longitud);
}

// Procesar acciones de la página
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        if ($accion === 'agregar') {
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $rol = $_POST['rol'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
            $stmt->execute();
        } elseif ($accion === 'editar') {
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $rol = $_POST['rol'];

            $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $email, $rol, $id);
            $stmt->execute();
        } elseif ($accion === 'eliminar') {
            $id = $_POST['id'];
            $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } elseif ($accion === 'resetear') {
            $id = $_POST['id'];
            $nuevaContrasena = generarContrasenaAleatoria();
            $hashContrasena = password_hash($nuevaContrasena, PASSWORD_BCRYPT);

            // Actualizar la contraseña
            $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashContrasena, $id);
            $stmt->execute();

            // Obtener el correo del usuario
            $stmt = $conexion->prepare("SELECT email FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();

            // Enviar correo con la nueva contraseña
            $to = $usuario['email'];
            $subject = "Reseteo de Contraseña";
            $message = "Tu nueva contraseña temporal es: $nuevaContrasena\nPor favor, cámbiala en tu próximo inicio de sesión.";
            $headers = "From: soporte@sistema.com";
            mail($to, $subject, $message, $headers);
        }
    }
}

// Obtener la lista de usuarios
$usuarios = $conexion->query("SELECT * FROM usuarios"); */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <div class="container my-5">
        <h1>Gestión de Usuarios</h1>

        <!-- Botón para agregar usuario -->
        <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar Usuario</button>

        <!-- Tabla de usuarios -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?= $usuario['id'] ?></td>
                        <td><?= $usuario['nombre'] ?></td>
                        <td><?= $usuario['email'] ?></td>
                        <td><?= $usuario['rol'] ?></td>
                        <td><?= $usuario['estado'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar" data-id="<?= $usuario['id'] ?>" data-nombre="<?= $usuario['nombre'] ?>" data-email="<?= $usuario['email'] ?>" data-rol="<?= $usuario['rol'] ?>">Editar</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <button class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                <input type="hidden" name="accion" value="resetear">
                                <button class="btn btn-secondary btn-sm">Resetear Contraseña</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include 'footer.php'; ?>
    <!-- Modal para agregar usuario -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="accion" value="agregar">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarLabel">Agregar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-control" name="rol">
                                <option value="admin">Administrador</option>
                                <option value="user">Usuario</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
        
    </div>

    <!-- Modal para editar usuario -->
    <!-- Similar al de agregar -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
