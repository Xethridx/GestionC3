<?php
session_start();
require 'conexion.php';
/*
// Verificar si el usuario tiene permisos de acceso
if (!isset($_SESSION['TipoUsuario']) || $_SESSION['TipoUsuario'] !== 'Validador') {
    header("Location: index.php");
    exit;
}

// Obtener la lista de evaluados
$evaluados = $conexion->query("SELECT * FROM evaluados");

// Procesar la validación de documentos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento_id = $_POST['documento_id'];
    $estado_validacion = $_POST['estado_validacion'];
    $observaciones = $_POST['observaciones'] ?? null;

    $stmt = $conexion->prepare("UPDATE documentos SET estado_validacion = ?, observaciones = ? WHERE id = ?");
    $stmt->bind_param("ssi", $estado_validacion, $observaciones, $documento_id);
    $stmt->execute();

    $mensaje = "La validación del documento se actualizó correctamente.";
}

// Obtener la lista de documentos
if (isset($_GET['evaluado_id'])) {
    $evaluado_id = $_GET['evaluado_id'];
    $documentos = $conexion->query("SELECT * FROM documentos WHERE evaluado_id = $evaluado_id");
}*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Validación de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <div class="container my-5">
        <h1>Módulo de Validación de Documentos</h1>

        <!-- Mensajes -->
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>

        <!-- Listado de evaluados -->
        <h2>Evaluados</h2>
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
                <?php while ($evaluado = $evaluados->fetch_assoc()): ?>
                    <tr>
                        <td><?= $evaluado['numero_solicitud'] ?></td>
                        <td><?= $evaluado['nombre'] ?></td>
                        <td><?= $evaluado['apellidos'] ?></td>
                        <td><?= $evaluado['curp'] ?></td>
                        <td>
                            <a href="validacion_documentos.php?evaluado_id=<?= $evaluado['id'] ?>" class="btn btn-primary btn-sm">Ver Documentos</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Validación de documentos -->
        <?php if (isset($documentos)): ?>
            <h2 class="mt-5">Documentos del Evaluado</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tipo de Documento</th>
                        <th>Archivo</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($documento = $documentos->fetch_assoc()): ?>
                        <tr>
                            <td><?= $documento['tipo_documento'] ?></td>
                            <td>
                                <a href="<?= $documento['ruta_archivo'] ?>" target="_blank" class="btn btn-info btn-sm">Ver Documento</a>
                            </td>
                            <td><?= $documento['estado_validacion'] ?? 'Pendiente' ?></td>
                            <td><?= $documento['observaciones'] ?? 'Ninguna' ?></td>
                            <td>
                                <button 
                                    class="btn btn-warning btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalValidacion" 
                                    data-id="<?= $documento['id'] ?>" 
                                    data-estado="<?= $documento['estado_validacion'] ?>" 
                                    data-observaciones="<?= $documento['observaciones'] ?>">Validar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Modal para validar documentos -->
    <div class="modal fade" id="modalValidacion" tabindex="-1" aria-labelledby="modalValidacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalValidacionLabel">Validar Documento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="documento_id" id="documento_id">
                        <div class="mb-3">
                            <label for="estado_validacion" class="form-label">Estado de Validación</label>
                            <select class="form-control" name="estado_validacion" id="estado_validacion" required>
                                <option value="Validado">Validado</option>
                                <option value="Observación">Observación</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="observaciones" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-bs-target="#modalValidacion"]').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('documento_id').value = button.getAttribute('data-id');
                document.getElementById('estado_validacion').value = button.getAttribute('data-estado') || 'Pendiente';
                document.getElementById('observaciones').value = button.getAttribute('data-observaciones') || '';
            });
        });
    </script>
</body>
</html>
