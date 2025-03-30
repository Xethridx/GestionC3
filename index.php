<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize $error variable to null or an empty string
$error = null; // Or $error = "";

/* Cerrar sesión automáticamente al acceder a index.php
session_unset();
session_destroy();*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Documental C3 - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/opt/lampp/htdocs/GestionC3/assets/styles/styles.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/hero-bg-optimized.png') center/cover no-repeat;
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
    <?php include 'navbar.php'; ?>

    <div class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold">Gestión Documental C3</h1>
            <p class="lead">Simplifica la gestión y validación de documentos en un solo lugar.</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-6 d-flex flex-column justify-content-center" data-aos="fade-right"> <h2 class="fw-bold"> <i class="fas fa-file-alt text-primary me-2"></i> Gestión Documental C3</h2> <p class="lead">Eficiencia y seguridad en la gestión documental</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Agilizar Procesos:</strong>  .</li>
                    <li class="mb-2"><i class="fas fa-shield-alt text-info me-2"></i> <strong>Seguridad Robusta:</strong> .</li>
                    <li class="mb-2"><i class="fas fa-users-cog text-secondary me-2"></i> <strong>Colaboración Eficaz:</strong>  </li>
                    <li class="mb-2"><i class="fas fa-chart-line text-warning me-2"></i> <strong>Mejora Continua:</strong> </li>
                </ul>
            </div>
            <div class="col-md-6" data-aos="fade-left"> <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4"><i class="fas fa-sign-in-alt text-primary me-2"></i> Iniciar Sesión</h3> <form action="login.php" method="POST">
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
                            <button type="submit" class="btn btn-primary w-100"> <i class="fas fa-lock me-2"></i> Iniciar Sesión</button> </form>
                        <div class="text-center mt-3">
                            <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a> |
                            <a href="registro.php">¿No tienes cuenta? Regístrate aquí.</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
             duration: 800, // Duración de la animación
             easing: 'ease-in-out', // Tipo de easing
             once: true, //  Si la animación se reproduce solo una vez
             mirror: false, // Si las animaciones en scroll deben espejarse
             offset: 100, // Offset (en px) desde el punto de activación original
        });
    </script>
</body>
</html>