<?php
// Iniciar sesión
session_start();
// Verificar si el usuario ha iniciado sesión como administrador o gestor
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'enlace')) {
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
    <title>Padrón de Evaluados</title>
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
            <h1 class="fw-bold">Padrón de Evaluados</h1>
            <p class="lead">Consulta y gestiona la información de los evaluados en el sistema.</p>
        </div>
    </div>

    <!-- Search Section -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Búsqueda de Evaluados</h2>
        <form class="row g-3">
            <div class="col-md-4">
                <label for="numeroOficio" class="form-label">Número de Oficio</label>
                <input type="text" class="form-control" id="numeroOficio" name="numeroOficio" placeholder="Ej: OF-12345">
            </div>
            <div class="col-md-4">
                <label for="nombreEvaluado" class="form-label">Nombre del Evaluado</label>
                <input type="text" class="form-control" id="nombreEvaluado" name="nombreEvaluado" placeholder="Ej: Juan Pérez">
            </div>
            <div class="col-md-4">
                <label for="curpEvaluado" class="form-label">CURP</label>
                <input type="text" class="form-control" id="curpEvaluado" name="curpEvaluado" placeholder="Ej: ABCD123456HGR">
            </div>
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Resultados</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Número de Oficio</th>
                        <th>Nombre</th>
                        <th>CURP</th>
                        <th>Corporación</th>
                        <th>Categoría</th>
                        <th>Municipio</th>
                        <th>Estado de Documentación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ejemplo de datos -->
                    <tr>
                        <td>OF-12345</td>
                        <td>Juan Pérez</td>
                        <td>ABCD123456HGR</td>
                        <td>Policía Estatal</td>
                        <td>Oficial</td>
                        <td>Acapulco</td>
                        <td><span class="badge bg-success">Completo</span></td>
                        <td>
                            <a href="ver_documentos.php?id=1" class="btn btn-info btn-sm"><i class="fas fa-folder-open"></i> Ver Documentos</a>
                        </td>
                    </tr>
                    <tr>
                        <td>OF-67890</td>
                        <td>Ana López</td>
                        <td>EFGH789012HGR</td>
                        <td>Policía Estatal</td>
                        <td>Inspector</td>
                        <td>Chilpancingo</td>
                        <td><span class="badge bg-warning">Pendiente</span></td>
                        <td>
                            <a href="ver_documentos.php?id=2" class="btn btn-info btn-sm"><i class="fas fa-folder-open"></i> Ver Documentos</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
