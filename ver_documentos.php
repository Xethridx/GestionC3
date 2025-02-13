<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar permisos
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'gestor'])) {
    header("Location: login.php");
    exit();
}

// Obtener información del usuario y documentos
$curp = $_GET['curp'] ?? null;
$expedienteId = $_GET['expedienteId'] ?? null;

if (!$curp || !$expedienteId) {
    die("Error: Parámetros insuficientes.");
}

try {
    // Información del usuario
    $stmtUser = $conn->prepare("SELECT Nombre, ApellidoP, ApellidoM FROM programacion_evaluados WHERE CURP = :curp LIMIT 1");
    $stmtUser->bindParam(':curp', $curp, PDO::PARAM_STR);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Error: Usuario no encontrado.");
    }

    // Documentos del usuario
    $stmtDocs = $conn->prepare("
        SELECT idDocumento, NombreArchivo, EstadoRevision, RutaArchivo, Comentarios
        FROM documentos_expediente
        WHERE idExpediente = :expedienteId AND idElemento = (
            SELECT idSolicitud FROM programacion_evaluados WHERE CURP = :curp LIMIT 1
        )
    ");
    $stmtDocs->bindParam(':expedienteId', $expedienteId, PDO::PARAM_INT);
    $stmtDocs->bindParam(':curp', $curp, PDO::PARAM_STR);
    $stmtDocs->execute();
    $documentos = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar datos: " . $e->getMessage());
}

// Manejar edición del documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_documento'])) {
    $idDocumento = intval($_POST['idDocumento']);
    $nuevoEstado = $_POST['estado'] ?? 'Pendiente';
    $nuevosComentarios = $_POST['comentarios'] ?? '';

    try {
        $stmtEditar = $conn->prepare("
            UPDATE documentos_expediente
            SET EstadoRevision = :estado, Comentarios = :comentarios
            WHERE idDocumento = :idDocumento
        ");
        $stmtEditar->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
        $stmtEditar->bindParam(':comentarios', $nuevosComentarios, PDO::PARAM_STR);
        $stmtEditar->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
        $stmtEditar->execute();

        header("Location: ver_documentos.php?curp=$curp&expedienteId=$expedienteId");
        exit();
    } catch (PDOException $e) {
        die("Error al actualizar el documento: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualización de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="fw-bold">Documentos de <?php echo htmlspecialchars($user['Nombre'] . ' ' . $user['ApellidoP'] . ' ' . $user['ApellidoM']); ?></h1>
        <p class="lead">Consulta los documentos cargados por el evaluado.</p>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del Documento</th>
                        <th>Estado</th>
                        <th>Comentarios</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['NombreArchivo']); ?></td>
                            <td>
                                <?php if ($doc['EstadoRevision'] === 'Validado'): ?>
                                    <span class="badge bg-success">Validado</span>
                                <?php elseif ($doc['EstadoRevision'] === 'Pendiente'): ?>
                                    <span class="badge bg-warning">Pendiente</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Observaciones</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($doc['Comentarios'] ?? ''); ?></td>
                            <td>
                                <!-- Botón Ver Documento -->
<a href="<?php echo htmlspecialchars($doc['RutaArchivo']); ?>" target="_blank" class="btn btn-primary btn-sm">
    Ver
</a>
 <!-- <?php echo "Ruta generada: " . htmlspecialchars($doc['RutaArchivo']); ?>-->

                                <!-- Botón Editar Documento -->
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $doc['idDocumento']; ?>">
                                    Editar
                                </button>

                                <!-- Modal para Editar -->
                                <div class="modal fade" id="editarModal<?php echo $doc['idDocumento']; ?>" tabindex="-1" aria-labelledby="editarModalLabel<?php echo $doc['idDocumento']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editarModalLabel<?php echo $doc['idDocumento']; ?>">Editar Documento</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="estado" class="form-label">Estado</label>
                                                        <select name="estado" class="form-control">
                                                            <option value="Pendiente" <?php echo $doc['EstadoRevision'] === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                            <option value="Validado" <?php echo $doc['EstadoRevision'] === 'Validado' ? 'selected' : ''; ?>>Validado</option>
                                                            <option value="Observaciones" <?php echo $doc['EstadoRevision'] === 'Observaciones' ? 'selected' : ''; ?>>Observaciones</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="comentarios" class="form-label">Comentarios</label>
                                                        <textarea name="comentarios" class="form-control" rows="3"><?php echo htmlspecialchars($doc['Comentarios'] ?? ''); ?></textarea>
                                                    </div>
                                                    <input type="hidden" name="idDocumento" value="<?php echo $doc['idDocumento']; ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" name="editar_documento" class="btn btn-primary">Guardar Cambios</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
     <?php include 'footer.php'; ?>
</body>
</html>
