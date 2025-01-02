<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Obtener el ID del evaluado desde la URL
$idEvaluado = $_GET['id'] ?? null;

// Datos simulados del evaluado y sus documentos
// (Estos datos deben conectarse a la base de datos en un entorno real)
$evaluado = [
    'id' => 1,
    'nombre' => 'Juan Pérez',
    'curp' => 'ABCD123456HGR',
    'corporacion' => 'Policía Estatal',
    'categoria' => 'Oficial',
    'municipio' => 'Acapulco',
];

$documentos = [
    ['nombre' => 'CURP.pdf', 'estado' => 'Validado', 'ruta' => 'uploads/CURP.pdf'],
    ['nombre' => 'ActaNacimiento.pdf', 'estado' => 'Pendiente', 'ruta' => 'uploads/ActaNacimiento.pdf'],
    ['nombre' => 'ComprobanteDomicilio.pdf', 'estado' => 'Observaciones', 'ruta' => 'uploads/ComprobanteDomicilio.pdf'],
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualización de Documentos</title>
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


    <!-- Evaluado Info -->
    <div class="container my-5">
        <h1 class="fw-bold">Documentos de <?php echo htmlspecialchars($evaluado['nombre']); ?></h1>
        <p class="lead">Consulta los documentos cargados por el evaluado.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <p><strong>CURP:</strong> <?php echo htmlspecialchars($evaluado['curp']); ?></p>
                <p><strong>Corporación:</strong> <?php echo htmlspecialchars($evaluado['corporacion']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($evaluado['categoria']); ?></p>
                <p><strong>Municipio:</strong> <?php echo htmlspecialchars($evaluado['municipio']); ?></p>
            </div>
        </div>
    </div>

    <!-- Documentos Section -->
    <div class="container my-5">
        <h2 class="mb-4">Listado de Documentos</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del Documento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['nombre']); ?></td>
                            <td>
                                <?php if ($doc['estado'] === 'Validado'): ?>
                                    <span class="badge bg-success">Validado</span>
                                <?php elseif ($doc['estado'] === 'Pendiente'): ?>
                                    <span class="badge bg-warning">Pendiente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Observaciones</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo htmlspecialchars($doc['ruta']); ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-download"></i> Descargar</a>
                                <?php if ($doc['estado'] === 'Observaciones'): ?>
                                    <button class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Agregar Comentarios</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
