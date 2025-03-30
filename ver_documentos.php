<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar permisos: Solo administrador y coordinador
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'coordinador'])) {
    header("Location: login.php");
    exit();
}

// Obtener información del usuario y documentos
$curp = $_GET['curp'] ?? null;
$expedienteId = $_GET['expedienteId'] ?? null;

$mensaje_error_usuario = ""; // Para errores al obtener usuario
$mensaje_error_documentos = ""; // Para errores al obtener documentos
$user = []; // Inicializar para evitar errores si falla la consulta
$documentos = []; // Inicializar para evitar errores si falla la consulta


// Validar parámetros GET explícitamente
if (empty($curp) || empty($expedienteId)) {
    die("Error: Parámetros CURP y Expediente ID son obligatorios.");
}


try {
    // Información del usuario
    $stmtUser = $conn->prepare("SELECT Nombre, ApellidoP, ApellidoM FROM programacion_evaluados WHERE CURP = :curp LIMIT 1");
    $stmtUser->bindParam(':curp', $curp, PDO::PARAM_STR);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $mensaje_error_usuario = "Error: Usuario no encontrado.";
    }
} catch (PDOException $e) {
    $mensaje_error_usuario = "Error al cargar datos del usuario: " . $e->getMessage();
     // En un entorno de producción, considerar registrar este error en un log
}


try {
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
     if (empty($documentos)) {
        $mensaje_error_documentos = "Este usuario no ha subido documentos aún."; // Mensaje si no hay documentos
    }

} catch (PDOException $e) {
    $mensaje_error_documentos = "Error al cargar documentos: " . $e->getMessage();
     // En un entorno de producción, considerar registrar este error en un log
}


// Manejar edición del documento (sin cambios en la lógica)
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

         <?php if (!empty($mensaje_error_usuario)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensaje_error_usuario; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($mensaje_error_documentos)): ?>
            <div class="alert alert-warning" role="alert">
                <?php echo $mensaje_error_documentos; ?>
            </div>
        <?php endif; ?>

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
                    <?php if (empty($documentos) && empty($mensaje_error_documentos)): ?>
                        <tr><td colspan="4" class="text-center">Este usuario no ha subido documentos aún.</td></tr>
                    <?php else: ?>
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
                                    <?php if (!is_null($doc['RutaArchivo'])): ?>
                                        <button type="button" class="btn btn-primary btn-sm visualizar-documento" data-url="<?php echo htmlspecialchars($doc['RutaArchivo']); ?>">
                                            Ver
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">No Disponible</span>
                                    <?php endif; ?>


                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $doc['idDocumento']; ?>">
                                        Editar
                                    </button>

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
                    <?php endif; ?>
                </tbody>
            </table>
             <?php if (empty($documentos) && empty($mensaje_error_documentos)): ?>
                 <p class="text-center text-muted">No hay documentos cargados para este usuario.</p>
             <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.visualizar-documento').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.dataset.url;
                const modal = `
                    <div class="modal fade" id="visualizarModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl"> <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Visualizar Documento</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <iframe src="${url}" width="100%" height="600px"></iframe> </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modal); // Insertar modal al final del body
                const visualizarModal = new bootstrap.Modal(document.getElementById('visualizarModal')); // Crear instancia de Bootstrap Modal
                visualizarModal.show(); // Mostrar el modal
                const modalElement = document.getElementById('visualizarModal');
                modalElement.addEventListener('hidden.bs.modal', function () { // Listener para limpiar el modal al cerrarse
                    modalElement.remove(); // Eliminar el modal del DOM al cerrarse
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
     <?php include 'footer.php'; ?>
</body>
</html>