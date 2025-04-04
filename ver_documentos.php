<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar permisos: Solo administrador y coordinador
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'coordinacion'])) {
    header("Location: login.php");
    exit();
}

// Obtener información del usuario y documentos
$curp = $_GET['curp'] ?? null;
$listadoId = $_GET['listadoId'] ?? null; // Cambiar expedienteId a listadoId

$mensaje_error_usuario = ""; // Para errores al obtener usuario
$mensaje_error_documentos = ""; // Para errores al obtener documentos
$user = []; // Inicializar para evitar errores si falla la consulta
$documentos = []; // Inicializar para evitar errores si falla la consulta


// Validar parámetros GET explícitamente
if (empty($curp) || empty($listadoId)) { // Cambiar expedienteId a listadoId
    die("Error: Parámetros CURP y Listado ID son obligatorios."); // Cambiar Expediente ID a Listado ID
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
        WHERE idExpediente = :listadoId AND idElemento = ( 
            SELECT idSolicitud FROM programacion_evaluados WHERE CURP = :curp LIMIT 1
        )
    ");
    $stmtDocs->bindParam(':listadoId', $listadoId, PDO::PARAM_INT); // Cambiar expedienteId a listadoId
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

$evaluados = [];
try {
    $stmtEvaluados = $conn->prepare("
        SELECT CURP, Nombre, ApellidoP, ApellidoM
        FROM programacion_evaluados
        WHERE idListadoEvaluados = :listadoId
        ORDER BY ApellidoP, ApellidoM, Nombre
    ");
    $stmtEvaluados->bindParam(':listadoId', $listadoId, PDO::PARAM_INT);
    $stmtEvaluados->execute();
    $evaluados = $stmtEvaluados->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar el listado de evaluados: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos del Evaluado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.5/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="fw-bold mb-4">Documentos del Evaluado</h1>
        <p class="lead mb-4">Consulta los documentos cargados por el evaluado.</p>

        <p class="mb-4">
            <a href="validacion_documentos.php?idListado=<?php echo htmlspecialchars($listadoId); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Volver al Panel Anterior
            </a>
        </p>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        Evaluados
                    </div>
                    <div class="list-group list-group-flush" id="lista-evaluados">
                        <?php foreach ($evaluados as $evaluado): ?>
                            <a href="ver_documentos.php?curp=<?php echo urlencode($evaluado['CURP']); ?>&listadoId=<?php echo htmlspecialchars($listadoId); ?>" class="list-group-item list-group-item-action<?php echo ($evaluado['CURP'] === $curp) ? ' active' : ''; ?>">
                                <?php echo htmlspecialchars($evaluado['ApellidoP'] . ' ' . $evaluado['ApellidoM'] . ', ' . $evaluado['Nombre']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
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
                                        <input type="hidden" name="idDocumento" value="<?php echo $doc['idDocumento']; ?>">
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
                                                <button type="button" class="btn btn-primary btn-sm visualizar-documento" data-url="visualizar_documento.php?ruta=<?php echo urlencode(htmlspecialchars($doc['RutaArchivo'])); ?>">
                                                    Ver
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">No Disponible</span>
                                            <?php endif; ?>
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
        </div>
    </div>

    <div class="modal fade" id="visualizarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Visualizar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="iframe-visualizar-documento" src="" width="100%" height="600px"></iframe>
                </div>
                <div class="modal-footer">
                    <form id="form-validar-documento" method="POST" action="actualizar_estado_documento.php">
                        <input type="hidden" name="idDocumento" id="modal-idDocumento">
                        <input type="hidden" name="curp" value="<?php echo htmlspecialchars($curp); ?>">
                        <input type="hidden" name="listadoId" value="<?php echo htmlspecialchars($listadoId); ?>">
                        <div class="mb-3">
                            <label for="modal-comentarios" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="modal-comentarios" name="comentarios" rows="3"></textarea>
                        </div>
                        <button type="button" class="btn btn-success" id="btn-validar-documento">Validar</button>
                        <button type="button" class="btn btn-danger" id="btn-observaciones-documento">Observaciones</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            document.querySelectorAll('.visualizar-documento').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = this.dataset.url;
                    const idDocumento = this.closest('tr').querySelector('input[name="idDocumento"]').value; // Obtener idDocumento de la fila
                    const modalElement = document.getElementById('visualizarModal');
                    const iframe = modalElement.querySelector('#iframe-visualizar-documento');
                    const modalIdDocumento = modalElement.querySelector('#modal-idDocumento');
                    const modalComentarios = modalElement.querySelector('#modal-comentarios');
                    const comentariosActuales = this.closest('tr').querySelector('td:nth-child(3)').textContent.trim();

                    iframe.src = url;
                    modalIdDocumento.value = idDocumento;
                    modalComentarios.value = comentariosActuales; // Precargar comentarios usando la propiedad value del DOM
                    const visualizarModal = new bootstrap.Modal(modalElement);
                    visualizarModal.show();

                    modalElement.addEventListener('hidden.bs.modal', function () {
                        iframe.src = '';
                        modalComentarios.val(''); // Limpiar comentarios al cerrar
                    });
                });
            });

            $('#btn-validar-documento').on('click', function() {
                actualizarEstadoDocumento('Validado');
            });

            $('#btn-observaciones-documento').on('click', function() {
                actualizarEstadoDocumento('Observaciones');
            });

            function actualizarEstadoDocumento(estado) {
                const form = $('#form-validar-documento');
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize() + '&estado=' + estado,
                    success: function(response) {
                        alert(response); // Muestra un mensaje (puedes personalizar esto)
                        window.location.reload(); // Recargar la página para ver los cambios
                    },
                    error: function(xhr, status, error) {
                        alert("Error al actualizar el estado: " + error);
                    }
                });
            }
        });
    </script>
     <?php include 'footer.php'; ?>
</body>
</html>