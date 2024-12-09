<?php
/* 
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como enlace
if (!isset($_SESSION['nombre_usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'enlace') {
    header("Location: login.php");
    exit();
}
*/

// Escapar el nombre del usuario para prevenir ataques XSS
$nombreUsuario = htmlspecialchars($_SESSION['nombre_usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Enlace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Gestión C3</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Panel Enlace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido -->
    <div class="container-fluid">
        <div class="row">
            <!-- Barra lateral -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
                <div class="position-sticky">
                    <h4 class="text-white text-center py-3">Enlace</h4>
                    <p class="text-white text-center">Bienvenido, <strong><?php echo $nombreUsuario; ?></strong></p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_carga.php">Carga de Documentos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_ayuda.php">Ayuda Técnica</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_reporte.php">Generar Reportes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_seguimiento.php">Seguimiento de Documentos</a>
                        </li>
                    </ul>
                    <a href="logout.php" class="btn btn-danger w-100 mt-3">Cerrar Sesión</a>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Panel del Enlace</h1>
                </div>
                <p>Utiliza las opciones del menú lateral para gestionar los documentos y realizar seguimiento.</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Carga de Documentos</h5>
                                <p class="card-text">Sube nuevos documentos al sistema de forma rápida y segura.</p>
                                <a href="modulo_carga.php" class="btn btn-primary">Ir al Módulo</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Generar Reportes</h5>
                                <p class="card-text">Crea reportes detallados sobre los documentos gestionados.</p>
                                <a href="modulo_reporte.php" class="btn btn-primary">Ir al Módulo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
