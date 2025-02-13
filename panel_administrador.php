<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Nombre del administrador
$nombre_admin = htmlspecialchars($_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Administrador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
</head>
<body>
    <!-- Navbar -->
<?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <div class="bg-light py-4">
        <div class="container text-center">
            <h1 class="fw-bold">Panel del Administrador</h1>
            <p class="lead">Accede a todos los módulos del sistema y gestiona usuarios, documentos y más.</p>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="container my-5">
        <div class="row g-4" data-aos="fade-up">
            <!-- Card: Gestión de Usuarios -->
            <div class="col-md-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text">Administra usuarios y roles del sistema.</p>
                        <a href="gestion_usuarios.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            <!-- Card: Gestion Expedientes -->
            <div class="col-md-4">
    <div class="card text-center shadow-sm">
        <div class="card-body">
            <i class="fas fa-folder-open fa-3x text-primary mb-3"></i>
            <h5 class="card-title">Gestión de Expedientes</h5>
            <p class="card-text">Crea y administra expedientes, elementos, y sus documentos.</p>
            <a href="gestion_expedientes.php" class="btn btn-primary">Acceder</a>
        </div>
    </div>
</div>

            <!-- Card: Programación
            <div class="col-md-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Programación</h5>
                        <p class="card-text">Organiza y programa actividades.</p>
                        <a href="programacion.php" class="btn btn-success">Acceder</a>
                    </div>
                </div>
            </div>
            <!-- Card: Carga de Documentos
            <div class="col-md-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-upload fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Carga de Documentos</h5>
                        <p class="card-text">Sube documentos al sistema.</p>
                        <a href="carga_documentos.php" class="btn btn-warning">Acceder</a>
                    </div>
                </div> -->
            <!-- Card: Validación de Documentos -->
            <div class="col-md-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Validación de Documentos</h5>
                        <p class="card-text">Valida los documentos cargados.</p>
                        <a href="validacion_documentos.php" class="btn btn-info">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- Card: Padrón de Evaluados -->
            <div class="col-md-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-user-check fa-3x text-secondary mb-3"></i>
                        <h5 class="card-title">Padrón de Evaluados</h5>
                        <p class="card-text">Consulta la información de los evaluados.</p>
                        <a href="padron_evaluados.php" class="btn btn-secondary">Acceder</a>
                    </div>
                </div>
            </div>
            
                        <!-- Card: Tickets -->
<div class="col-md-4">
    <div class="card text-center shadow-sm">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-ticket-alt"></i> Gestión de Tickets</h5>
            <p class="card-text">Visualiza y responde a las solicitudes de soporte enviadas por los usuarios.</p>
            <a href="tickets_soporte.php" class="btn btn-primary">Acceder</a>
        </div>
    </div>
</div>


            <!-- Card: Ayuda -->
            <div class="col-md-4">
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

    <!-- Logs de Acceso -->
    <div class="container my-5">
        <h2>Logs de Acceso Recientes</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Fecha y Hora</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>jperez</td>
                        <td>2024-12-10 14:00:00</td>
                        <td>Inicio de sesión</td>
                    </tr>
                    <tr>
                        <td>alopez</td>
                        <td>2024-12-11 09:30:00</td>
                        <td>Carga de documentos</td>
                    </tr>
                    <tr>
                        <td>mmartinez</td>
                        <td>2024-12-11 10:00:00</td>
                        <td>Validación de documentos</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
        <!-- Footer -->
    <?php include 'footer.php'; ?>
</body>
</html>
