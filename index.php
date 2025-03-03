<?php
session_start();
include 'conexion.php';
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



 Cerrar sesión automáticamente al acceder a index.php
session_unset();
session_destroy();*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Documental C3 - Inicio</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/hero-bg-optimized.jpg') center/cover no-repeat;
            color: #fff;
            padding: 80px 20px;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
        }
        .hero-section p {
            font-size: 1.5rem;
            margin-top: 10px;
        }
        .card {
            border: none;
        }
        .btn-primary {
            background-color: #0056b3;
            border: none;
        }
        .btn-primary:hover {
            background-color: #004080;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold">Gestión Documental C3</h1>
            <p class="lead">Simplifica la gestión y validación de documentos en un solo lugar.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Welcome Section -->
            <div class="col-md-6 d-flex flex-column justify-content-center">
                <h2 class="fw-bold">Bienvenido</h2>
                <p>Nuestra plataforma asegura procesos ágiles y seguros en la recepción y validación de documentos, fortaleciendo la confianza institucional.</p>
            </div>
            <!-- Login Form -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Iniciar Sesión</h3>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                            </div>
                            <?php if ($error): ?>
                                <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a> |
                            <a href="registro.php">¿No tienes cuenta? Regístrate aquí.</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
