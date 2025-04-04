<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'gestor') {
    header("Location: login.php");
    exit();
}

$nombre_gestor = htmlspecialchars($_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Gestor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <style>
        /* Estilos personalizados - Reutilizando los estilos del admin panel */
        #sidebar {
            background-color: var(--color-fondo);
            padding: 20px;
            border-right: 1px solid #dee2e6;
            height: calc(100vh - 70px);
            overflow-y: auto;
            position: sticky;
            top: 70px;
        }
        #contenido-principal {
            padding: 20px;
        }
        .admin-card {
            margin-bottom: 20px;
            border-radius: 10px;
        }
        .admin-card img {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block">
                <div class="admin-card card shadow-sm">
                    <div class="card-body text-center">
                        <img src="assets/images/default-gestor-avatar.png" alt="Foto del Gestor" class="img-fluid">
                        <h5 class="card-title">Bienvenido, <?php echo htmlspecialchars($nombre_gestor); ?></h5>
                    </div>
                </div>
            </nav>

            <main id="contenido-principal" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="bg-light py-4">
                    <div class="container text-center">
                        <h1 class="fw-bold">Panel del Gestor</h1>
                        <p class="lead">Herramientas para la gestión de expedientes.</p>
                    </div>
                </div>

                <div class="container my-3">
                    <div class="row g-4" data-aos="fade-up">
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <i class="fas fa-folder-open fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Gestión de Listados</h5>
                                    <p class="card-text">Crea y administra listados de elementos, y sus documentos.</p>
                                    <a href="gestion_listados.php" class="btn btn-primary">Acceder</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>