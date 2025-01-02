<?php
session_start();

// Mensajes de éxito o error
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Procesar solicitud de recuperación de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'conexion.php';

    $email = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);

    if ($email) {
        try {
            // Verificar si el correo existe en la base de datos
            $sql = "SELECT * FROM usuarios WHERE Correo = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Simulación del envío de correo
                $_SESSION['success'] = "Se ha enviado un correo con las instrucciones para restablecer tu contraseña.";
            } else {
                $_SESSION['error'] = "El correo proporcionado no está registrado.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al procesar la solicitud: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Por favor, proporciona un correo válido.";
    }

    header("Location: recuperar_contraseña.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container my-5">
        <h1 class="text-center mb-4">Recuperar Contraseña</h1>
        <p class="text-center">Ingresa tu correo electrónico para recibir las instrucciones de recuperación de tu contraseña.</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="recuperar_contraseña.php">
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingresa tu correo" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
