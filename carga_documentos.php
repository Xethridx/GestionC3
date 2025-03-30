<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validar permisos: Administrador y Enlace pueden acceder
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'enlace')) {
    header("Location: login.php");
    exit();
}

// Validar parámetros GET y asignar variables
$idExpediente = isset($_GET['expediente']) ? intval($_GET['expediente']) : 0;
$curp = isset($_GET['curp']) ? htmlspecialchars($_GET['curp']) : '';
$tipoEvaluacionId = isset($_GET['tipoEvaluacion']) ? intval($_GET['tipoEvaluacion']) : 0;

if (!$idExpediente || !$curp || !$tipoEvaluacionId) {
    die("Error: Parámetros insuficientes.");
}

// Función para obtener información del expediente y carpeta
function obtenerExpedienteInfo($conn, $idExpediente) {
    $stmtExpediente = $conn->prepare("SELECT FolioExpediente FROM expedientes WHERE idExpediente = :idExpediente");
    $stmtExpediente->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
    $stmtExpediente->execute();
    $expediente = $stmtExpediente->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        die("Error: Expediente no encontrado.");
    }
    return [
        'folioExpediente' => $expediente['FolioExpediente'],
        'carpetaExpediente' => __DIR__ . "/Expedientes/" . $expediente['FolioExpediente']
    ];
}

// Función para obtener información del evaluado
function obtenerEvaluadoInfo($conn, $curp) {
    $stmtElemento = $conn->prepare("
        SELECT idSolicitud, Nombre, ApellidoP, ApellidoM
        FROM programacion_evaluados
        WHERE CURP = :curp
    ");
    $stmtElemento->bindParam(':curp', $curp, PDO::PARAM_STR);
    $stmtElemento->execute();
    $elemento = $stmtElemento->fetch(PDO::FETCH_ASSOC);

    if (!$elemento) {
        die("Error: Evaluado no encontrado.");
    }
    return $elemento;
}

// Función para consultar documentos requeridos por motivo de evaluación
function obtenerDocumentosRequeridos($conn, $tipoEvaluacionId) {
    $stmtRequeridos = $conn->prepare("
        SELECT td.idTipoDocumento, td.Documento
        FROM tipo_documento_motivo tdm
        JOIN tipo_documento td ON tdm.idTipoDocumento = td.idTipoDocumento
        WHERE tdm.idMotivo = :idMotivo
    ");
    $stmtRequeridos->bindParam(':idMotivo', $tipoEvaluacionId, PDO::PARAM_INT);
    $stmtRequeridos->execute();
    return $stmtRequeridos->fetchAll(PDO::FETCH_ASSOC);
}

// Función para consultar documentos existentes en el expediente del evaluado
function obtenerDocumentosExistentes($conn, $idExpediente, $idElemento) {
    $stmtDocumentos = $conn->prepare("
        SELECT d.idDocumento, d.idTipoDocumento, d.NombreArchivo, d.RutaArchivo, d.EstadoRevision, d.Comentarios, td.Documento
        FROM documentos_expediente d
        JOIN tipo_documento td ON d.idTipoDocumento = td.idTipoDocumento
        WHERE d.idExpediente = :idExpediente AND d.idElemento = :idElemento
    ");
    $stmtDocumentos->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
    $stmtDocumentos->bindParam(':idElemento', $idElemento, PDO::PARAM_INT);
    $stmtDocumentos->execute();
    return $stmtDocumentos->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener información del expediente
$expedienteInfo = obtenerExpedienteInfo($conn, $idExpediente);
$folioExpediente = $expedienteInfo['folioExpediente'];
$carpetaExpediente = $expedienteInfo['carpetaExpediente'];

// Obtener información del evaluado
$elemento = obtenerEvaluadoInfo($conn, $curp);
$idElemento = $elemento['idSolicitud'];

// Crear carpeta del evaluado si no existe
$carpetaEvaluado = $carpetaExpediente . "/" . $curp;
if (!file_exists($carpetaEvaluado)) {
    mkdir($carpetaEvaluado, 0777, true);
}

// Obtener documentos requeridos y existentes
$documentosRequeridos = obtenerDocumentosRequeridos($conn, $tipoEvaluacionId);
$documentosExistentes = obtenerDocumentosExistentes($conn, $idExpediente, $idElemento);

// Combinar documentos requeridos y existentes (simplificado)
$documentos = array_map(function ($requerido) use ($documentosExistentes) {
    foreach ($documentosExistentes as $existente) {
        if ($requerido['idTipoDocumento'] == $existente['idTipoDocumento']) {
            return $existente;
        }
    }
    return [
        'idDocumento' => null,
        'idTipoDocumento' => $requerido['idTipoDocumento'],
        'NombreArchivo' => null,
        'RutaArchivo' => null,
        'EstadoRevision' => 'Pendiente',
        'Comentarios' => null,
        'Documento' => $requerido['Documento']
    ];
}, $documentosRequeridos);


// Función para verificar si todos los documentos están en estado 'Enviado' o superior
function todosDocumentosEnviados($documentos) {
    if (empty($documentos)) return false;
    foreach ($documentos as $doc) {
        if ($doc['EstadoRevision'] === 'Pendiente') return false;
    }
    return true;
}

$mensajes = [];

// Función para manejar la carga de documentos vía AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'subir_documento_ajax') {
    $idTipoDocumento = intval($_POST['idTipoDocumento']);
    $comentarios = trim($_POST['comentarios'] ?? '');
    $archivo = $_FILES['documento'];

    $maxSizeBytesPHP = 5 * 1024 * 1024; // 5MB
    $allowedTypesPHP = ['application/pdf']; // PDF

    if ($archivo['error'] === UPLOAD_ERR_OK) {
        if ($archivo['size'] > $maxSizeBytesPHP) {
            echo json_encode(['error' => 'Error: Archivo excede 5MB.', 'idTipoDocumento' => $idTipoDocumento]);
            exit();
        } elseif (!in_array($archivo['type'], $allowedTypesPHP)) {
            echo json_encode(['error' => 'Error: Solo PDF permitidos.', 'idTipoDocumento' => $idTipoDocumento]);
            exit();
        } else {
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreDocumento = $documentosRequeridos[array_search($idTipoDocumento, array_column($documentosRequeridos, 'idTipoDocumento'))]['Documento'];
            $nombreArchivo = "{$curp}_" . str_replace(' ', '', $nombreDocumento) . ".$extension";
            $rutaDestinoRelativaWeb = "/Expedientes/$folioExpediente/$curp/$nombreArchivo";
            $rutaDestinoAbsolutaServidor = $carpetaEvaluado . "/" . $nombreArchivo;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoAbsolutaServidor)) {
                try {
                    $stmtInsertar = $conn->prepare("
                        INSERT INTO documentos_expediente (idExpediente, idElemento, idTipoDocumento, NombreArchivo, RutaArchivo, EstadoRevision, Comentarios)
                        VALUES (:idExpediente, :idElemento, :idTipoDocumento, :nombreArchivo, :rutaArchivo, 'Enviado', :comentarios)
                        ON DUPLICATE KEY UPDATE RutaArchivo = :rutaArchivo, EstadoRevision = 'Enviado', Comentarios = :comentarios
                    ");
                    $stmtInsertar->execute([
                        ':idExpediente' => $idExpediente,
                        ':idElemento' => $idElemento,
                        ':idTipoDocumento' => $idTipoDocumento,
                        ':nombreArchivo' => $nombreArchivo,
                        ':rutaArchivo' => $rutaDestinoRelativaWeb,
                        ':comentarios' => $comentarios,
                        'rutaArchivo' => $rutaDestinoRelativaWeb //Bind para ON DUPLICATE KEY UPDATE
                    ]);

                    // Obtener el ID del documento recién insertado
                    $idDocumentoInsertado = $conn->lastInsertId();

                    echo json_encode(['success' => 'Documento subido correctamente.', 'idTipoDocumento' => $idTipoDocumento, 'nombreArchivo' => $nombreArchivo, 'rutaArchivo' => $rutaDestinoRelativaWeb, 'idDocumento' => $idDocumentoInsertado]);
                    exit();
                } catch (PDOException $e) {
                    echo json_encode(['error' => 'Error al registrar documento: ' . $e->getMessage(), 'idTipoDocumento' => $idTipoDocumento]);
                    exit();
                }
            } else {
                echo json_encode(['error' => 'Error al subir documento.', 'idTipoDocumento' => $idTipoDocumento]);
                exit();
            }
        }
    } elseif ($archivo['error'] !== UPLOAD_ERR_NO_FILE) {
        echo json_encode(['error' => 'Error en la subida del archivo, código: ' . $archivo['error'], 'idTipoDocumento' => $idTipoDocumento]);
        exit();
    }
}
// Función para manejar la eliminación de documentos vía AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_documento_ajax') {
    $idDocumento = intval($_POST['idDocumento']);
    try {
        // Obtener información del documento antes de eliminar
        $stmtObtenerInfo = $conn->prepare("SELECT RutaArchivo, idTipoDocumento, idElemento, idExpediente FROM documentos_expediente WHERE idDocumento = :idDocumento");
        $stmtObtenerInfo->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
        $stmtObtenerInfo->execute();
        $docInfo = $stmtObtenerInfo->fetch(PDO::FETCH_ASSOC);

        if ($docInfo) {
            $rutaArchivoAbsoluta = __DIR__ . $docInfo['RutaArchivo'];
            if (file_exists($rutaArchivoAbsoluta)) {
                if (unlink($rutaArchivoAbsoluta)) {
                    $stmtEliminar = $conn->prepare("DELETE FROM documentos_expediente WHERE idDocumento = :idDocumento");
                    $stmtEliminar->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
                    $stmtEliminar->execute();
                    echo json_encode(['success' => 'Documento eliminado correctamente.', 'idTipoDocumento' => $docInfo['idTipoDocumento']]);
                    exit();
                } else {
                    echo json_encode(['error' => 'Error al eliminar el archivo del servidor.', 'idTipoDocumento' => $docInfo['idTipoDocumento']]);
                    exit();
                }
            } else {
                // El archivo no existe, pero se elimina la entrada de la base de datos
                $stmtEliminar = $conn->prepare("DELETE FROM documentos_expediente WHERE idDocumento = :idDocumento");
                $stmtEliminar->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
                $stmtEliminar->execute();
                echo json_encode(['success' => 'Documento eliminado de la base de datos (archivo no encontrado).', 'idTipoDocumento' => $docInfo['idTipoDocumento']]);
                exit();
            }
        } else {
            echo json_encode(['error' => 'Documento no encontrado para eliminar.', 'idDocumento' => $idDocumento]);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al eliminar documento: ' . $e->getMessage()]);
        exit();
    }
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <h1 class="mb-4">Carga de Documentos - <?php echo htmlspecialchars($elemento['Nombre'] . ' ' . $elemento['ApellidoP'] . ' ' . $elemento['ApellidoM']); ?></h1>
        <p><strong>Expediente:</strong> <?php echo htmlspecialchars($folioExpediente); ?></p>
        <p><strong>CURP:</strong> <?php echo htmlspecialchars($curp); ?></p>

        <div id="mensajes"></div>

        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Documento</th>
                    <th>Estado</th>
                    <th>Comentarios</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-documentos">
                <?php foreach ($documentos as $index => $documento): ?>
                    <tr id="fila-documento-<?php echo $documento['idTipoDocumento']; ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($documento['Documento']); ?></td>
                        <td id="estado-documento-<?php echo $documento['idTipoDocumento']; ?>">
                            <span class="badge <?php echo $documento['EstadoRevision'] === 'Pendiente' ? 'bg-warning text-dark' : (isset($documento['EstadoRevision']) && $documento['EstadoRevision'] !== 'Pendiente' ? 'bg-success' : ''); ?>"
                                  id="badge-estado-<?php echo $documento['idTipoDocumento']; ?>">
                                <?php echo htmlspecialchars($documento['EstadoRevision'] ?? 'Pendiente'); ?>
                            </span>
                        </td>
                        <td id="comentarios-documento-<?php echo $documento['idTipoDocumento']; ?>"><?php echo htmlspecialchars($documento['Comentarios'] ?? ''); ?></td>
                        <td id="acciones-documento-<?php echo $documento['idTipoDocumento']; ?>">
                            <?php if (is_null($documento['RutaArchivo'])): ?>
                                <form id="form-subir-<?php echo $documento['idTipoDocumento']; ?>" enctype="multipart/form-data">
                                    <div class="d-flex gap-1 align-items-center">
                                        <input type="hidden" name="accion" value="subir_documento_ajax">
                                        <input type="hidden" name="idTipoDocumento" value="<?php echo $documento['idTipoDocumento']; ?>">
                                        <input type="file" name="documento" class="form-control form-control-sm w-50" required accept=".pdf">
                                        <input type="text" name="comentarios" class="form-control form-control-sm w-50" placeholder="Comentarios (opcional)">
                                        <button type="button" class="btn btn-success btn-sm mt-0 btn-subir"
                                                data-id-tipo-documento="<?php echo $documento['idTipoDocumento']; ?>">Subir</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-1">
                                    <form id="form-eliminar-<?php echo $documento['idTipoDocumento']; ?>">
                                        <input type="hidden" name="accion" value="eliminar_documento_ajax">
                                        <input type="hidden" name="idDocumento" value="<?php echo $documento['idDocumento']; ?>">
                                        <button type="button" class="btn btn-danger btn-sm w-100 btn-eliminar"
                                                data-id-tipo-documento="<?php echo $documento['idTipoDocumento']; ?>"
                                                data-id-documento="<?php echo $documento['idDocumento']; ?>"
                                                data-ruta-archivo="<?php echo htmlspecialchars($documento['RutaArchivo']); ?>">Eliminar</button>
                                    </form>
                                    <a href="#" class="btn btn-info btn-sm visualizar-documento w-100"
                                       data-url="<?php echo htmlspecialchars($documento['RutaArchivo']); ?>">Visualizar</a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST" class="mt-4 text-center" id="enviar-revision-form">
            <button type="submit" name="enviar_revision" class="btn btn-primary" <?php echo todosDocumentosEnviados($documentos) ? '' : 'disabled'; ?>>
                Enviar a Revisión
            </button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        $(document).ready(function () {
            // Función para manejar la subida de documentos vía AJAX
            $('.btn-subir').on('click', function () {
                const idTipoDocumento = $(this).data('id-tipo-documento');
                const form = $('#form-subir-' + idTipoDocumento)[0];
                const formData = new FormData(form);
                const filaDocumento = $('#fila-documento-' + idTipoDocumento);
                const accionesDocumento = $('#acciones-documento-' + idTipoDocumento);
                const estadoDocumento = $('#estado-documento-' + idTipoDocumento);

                $.ajax({
                    url: '', // La misma página
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('#mensajes').html('<div class="alert alert-success">' + response.success + '</div>');
                            estadoDocumento.html('<span class="badge bg-success" id="badge-estado-' + idTipoDocumento + '">Enviado</span>');
                            accionesDocumento.html(`
                                <div class="d-flex flex-column gap-1">
                                    <form id="form-eliminar-${idTipoDocumento}">
                                        <input type="hidden" name="accion" value="eliminar_documento_ajax">
                                        <input type="hidden" name="idDocumento" value="${response.idDocumento}">
                                        <button type="button" class="btn btn-danger btn-sm w-100 btn-eliminar"
                                                data-id-tipo-documento="${idTipoDocumento}"
                                                data-id-documento="${response.idDocumento}"
                                                data-ruta-archivo="${response.rutaArchivo}">Eliminar</button>
                                    </form>
                                    <a href="#" class="btn btn-info btn-sm visualizar-documento w-100" data-url="${response.rutaArchivo}">Visualizar</a>
                                </div>
                            `);
                        } else if (response.error) {
                            $('#mensajes').html('<div class="alert alert-danger">' + response.error + '</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#mensajes').html('<div class="alert alert-danger">Error al subir el documento: ' + error + '</div>');
                    }
                });
            });

            // Función para manejar la eliminación de documentos vía AJAX
            $(document).on('click', '.btn-eliminar', function () {
                const idDocumento = $(this).data('id-documento');
                const idTipoDocumento = $(this).data('id-tipo-documento');
                const rutaArchivo = $(this).data('ruta-archivo');
                const filaDocumento = $('#fila-documento-' + idTipoDocumento);
                const accionesDocumento = $('#acciones-documento-' + idTipoDocumento);
                const estadoDocumento = $('#estado-documento-' + idTipoDocumento);

                if (confirm('¿Estás seguro de que deseas eliminar este documento?')) {
                    $.ajax({
                        url: '', // La misma página
                        type: 'POST',
                        data: { accion: 'eliminar_documento_ajax', idDocumento: idDocumento },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                $('#mensajes').html('<div class="alert alert-success">' + response.success + '</div>');
                                estadoDocumento.html('<span class="badge bg-warning text-dark" id="badge-estado-' + idTipoDocumento + '">Pendiente</span>');
                                accionesDocumento.html(`
                                    <form id="form-subir-${idTipoDocumento}" enctype="multipart/form-data">
                                        <div class="d-flex gap-1 align-items-center">
                                            <input type="hidden" name="accion" value="subir_documento_ajax">
                                            <input type="hidden" name="idTipoDocumento" value="${idTipoDocumento}">
                                            <input type="file" name="documento" class="form-control form-control-sm w-50" required accept=".pdf">
                                            <input type="text" name="comentarios" class="form-control form-control-sm w-50" placeholder="Comentarios (opcional)">
                                            <button type="button" class="btn btn-success btn-sm mt-0 btn-subir" data-id-tipo-documento="${idTipoDocumento}">Subir</button>
                                        </div>
                                    </form>
                                `);
                            } else if (response.error) {
                                $('#mensajes').html('<div class="alert alert-danger">' + response.error + '</div>');
                            }
                        },
                        error: function (xhr, status, error) {
                            $('#mensajes').html('<div class="alert alert-danger">Error al eliminar el documento: ' . error + '</div>');
                        }
                    });
                }
            });

            // Visualizar Documento
            $(document).on('click', '.visualizar-documento', function (e) {
                e.preventDefault();
                const url = $(this).data('url');
                const modal = `
                    <div class="modal fade" id="visualizarModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Visualizar Documento</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <iframe src="${url}" width="100%" height="500px"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(modal);
                $('#visualizarModal').modal('show').on('hidden.bs.modal', function () {
                    $(this).remove();
                });
            });
        });
    </script>   

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>