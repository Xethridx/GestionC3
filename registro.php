<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $nombre = htmlspecialchars($_POST['nombre']);
    $usuario = htmlspecialchars($_POST['usuario']);
    $correo = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($correo && $password === $confirmPassword) {
        try {
            // Crear un hash seguro de la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insertar el usuario en la base de datos
            $sql = "INSERT INTO usuarios (NUsuario, Nombre, Correo, Contraseña, TipoUsuario) VALUES (?, ?, ?, ?, 'enlace')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$usuario, $nombre, $correo, $hashedPassword]);

            $_SESSION['success'] = "Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al registrar el usuario: " . $e->getMessage();
            header("Location: registro.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Verifica los datos ingresados. Las contraseñas deben coincidir.";
        header("Location: registro.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Registro de Usuario</h1>
        <p class="text-center">Llena los siguientes campos para registrarte en el sistema.</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <form method="POST" action="registro.php">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingresa tu nombre completo" required>
                    </div>
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Crea un nombre de usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingresa tu correo electrónico" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Crea una contraseña" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirma tu contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
