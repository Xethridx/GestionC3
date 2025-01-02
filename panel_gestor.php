<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como gestor
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'gestor') {
    header("Location: login.php");
    exit();
}

// Nombre del usuario gestor
$nombre_gestor = htmlspecialchars($_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Gestor</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <!-- Navbar -->
<?php include 'navbar.php'; ?>


    <!-- Hero Section -->
    <div class="bg-light py-4">
        <div class="container text-center">
            <h1 class="fw-bold">Panel del Gestor</h1>
            <p class="lead">Gestiona y valida los documentos del sistema.</p>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="container my-5">
        <div class="row g-4">
            <!-- Validación de Documentos -->
            <div class="col-md-6">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Validación de Documentos</h5>
                        <p class="card-text">Revisa y valida los documentos cargados.</p>
                        <a href="validacion_documentos.php" class="btn btn-info">Acceder</a>
                    </div>
                </div>
            </div>
            <!-- Carga de Documentos -->
            <div class="col-md-6">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-upload fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Carga de Documentos</h5>
                        <p class="card-text">Sube documentos al sistema por número de solicitud.</p>
                        <a href="carga_documentos.php" class="btn btn-warning">Acceder</a>
                    </div>
                </div>
            </div>
            <!-- Ayuda -->
            <div class="col-md-12">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-life-ring fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Ayuda Técnica</h5>
                        <p class="card-text">Encuentra soporte y manuales.</p>
                        <a href="ayuda.php" class="btn btn-danger">Acceder</a>
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
