<?php
session_start();
require 'conexion.php';
/*
// Verificar si el usuario tiene permisos de acceso
if (!isset($_SESSION['TipoUsuario']) || $_SESSION['TipoUsuario'] !== 'Administrador') {
    header("Location: index.php");
    exit;
}
*/
// Buscar evaluados
$evaluados = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    $busqueda = $_GET['buscar'];
    $stmt = $conexion->prepare("SELECT * FROM evaluados WHERE nombre LIKE ? OR apellidos LIKE ? OR curp LIKE ?");
    $param = "%" . $busqueda . "%";
    $stmt->bind_param("sss", $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    $evaluados = $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener documentos del evaluado seleccionado
$documentos = [];
if (isset($_GET['evaluado_id'])) {
    $evaluado_id = $_GET['evaluado_id'];
    $stmt = $conexion->prepare("SELECT * FROM documentos WHERE evaluado_id = ?");
    $stmt->bind_param("i", $evaluado_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $documentos = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Padrón de Evaluados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <div class="container my-5">
        <h1>Módulo de Padrón de Evaluados</h1>

        <!-- Formulario de búsqueda -->
        <form class="my-4" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="buscar" placeholder="Buscar por nombre, apellidos o CURP" value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
        </form>

        <!-- Resultados de búsqueda -->
        <?php if (!empty($evaluados)): ?>
            <h2>Resultados de Búsqueda</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Número de Solicitud</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>CURP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluados as $evaluado): ?>
                        <tr>
                            <td><?= htmlspecialchars($evaluado['numero_solicitud']) ?></td>
                            <td><?= htmlspecialchars($evaluado['nombre']) ?></td>
                            <td><?= htmlspecialchars($evaluado['apellidos']) ?></td>
                            <td><?= htmlspecialchars($evaluado['curp']) ?></td>
                            <td>
                                <a href="padron_evaluados.php?evaluado_id=<?= $evaluado['id'] ?>" class="btn btn-info btn-sm">Ver Documentos</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])): ?>
            <div class="alert alert-warning">No se encontraron resultados para la búsqueda.</div>
        <?php endif; ?>

        <!-- Visualización de documentos -->
        <?php if (!empty($documentos)): ?>
            <h2 class="mt-5">Documentos del Evaluado</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tipo de Documento</th>
                        <th>Archivo</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentos as $documento): ?>
                        <tr>
                            <td><?= htmlspecialchars($documento['tipo_documento']) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($documento['ruta_archivo']) ?>" target="_blank" class="btn btn-info btn-sm">Ver Documento</a>
                            </td>
                            <td><?= htmlspecialchars($documento['estado_validacion'] ?? 'Pendiente') ?></td>
                            <td><?= htmlspecialchars($documento['observaciones'] ?? 'Ninguna') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($_GET['evaluado_id'])): ?>
            <div class="alert alert-warning">No se encontraron documentos para este evaluado.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
