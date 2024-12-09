<?php
/* 
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como gestor
if (!isset($_SESSION['nombre_usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gestor') {
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
    <title>Panel del Gestor</title>
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
                        <a class="nav-link active" href="#">Panel Gestor</a>
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
                    <h4 class="text-white text-center py-3">Gestor</h4>
                    <p class="text-white text-center">Bienvenido, <strong><?php echo $nombreUsuario; ?></strong></p>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_validacion.php">Validación de Documentos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_programacion.php">Programación</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_carga.php">Carga de Documentos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logs.php">Ver Logs de Usuarios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="modulo_reporte.php">Generar Reportes</a>
                        </li>
                    </ul>
                    <a href="logout.php" class="btn btn-danger w-100 mt-3">Cerrar Sesión</a>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Panel del Gestor</h1>
                </div>
                <p>Utiliza las opciones del menú lateral para gestionar los documentos y realizar seguimiento.</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Validación de Documentos</h5>
                                <p class="card-text">Valida los documentos ingresados en el sistema.</p>
                                <a href="modulo_validacion.php" class="btn btn-primary">Ir al Módulo</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Programación</h5>
                                <p class="card-text">Organiza y gestiona la programación de actividades.</p>
                                <a href="modulo_programacion.php" class="btn btn-primary">Ir al Módulo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p class="mb-0">© 2024 Gestión C3. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
