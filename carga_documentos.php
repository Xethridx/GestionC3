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
$idListadoEvaluados = isset($_GET['idListadoEvaluados']) ? intval($_GET['idListadoEvaluados']) : 0;
$curp = isset($_GET['curp']) ? htmlspecialchars($_GET['curp']) : '';
$tipoEvaluacionId = isset($_GET['tipoEvaluacion']) ? intval($_GET['tipoEvaluacion']) : 0;

if (!$idListadoEvaluados || !$curp || !$tipoEvaluacionId) {
    die("Error: Parámetros insuficientes.");
}

// Definir la ruta base para los listados (debe ser la misma que en gestion_listados.php y programacion.php)
define('LISTADOS_PATH', __DIR__ . '/Listados');

// Función para obtener información del listado y carpeta
function obtenerListadoInfo($conn, $idListadoEvaluados) {
    $stmtListado = $conn->prepare("SELECT NumeroOficio FROM listados_evaluados WHERE idExpediente = :idListadoEvaluados");
    $stmtListado->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
    $stmtListado->execute();
    $listado = $stmtListado->fetch(PDO::FETCH_ASSOC);

    if (!$listado) {
        die("Error: Listado no encontrado.");
    }
    return [
        'numeroOficio' => $listado['NumeroOficio'],
        'carpetaBase' => LISTADOS_PATH // La carpeta base ahora es la de Listados
    ];
}

// Función para obtener información del evaluado
function obtenerEvaluadoInfo($conn, $curp, $idListadoEvaluados) {
    $stmtElemento = $conn->prepare("
        SELECT idSolicitud, Nombre, ApellidoP, ApellidoM
        FROM programacion_evaluados
        WHERE CURP = :curp AND idListadoEvaluados = :idListadoEvaluados
    ");
    $stmtElemento->bindParam(':curp', $curp, PDO::PARAM_STR);
    $stmtElemento->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
    $stmtElemento->execute();
    $elemento = $stmtElemento->fetch(PDO::FETCH_ASSOC);

    if (!$elemento) {
        die("Error: Evaluado no encontrado en este listado.");
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

// Función para consultar documentos existentes en el listado del evaluado
function obtenerDocumentosExistentes($conn, $idListadoEvaluados, $idElemento) {
    $stmtDocumentos = $conn->prepare("
        SELECT d.idDocumento, d.idTipoDocumento, d.NombreArchivo, d.RutaArchivo, d.EstadoRevision, d.Comentarios, td.Documento
        FROM documentos_expediente d
        JOIN tipo_documento td ON d.idTipoDocumento = td.idTipoDocumento
WHERE d.idExpediente = :idListadoEvaluados AND d.idElemento = :idElemento    ");
    $stmtDocumentos->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
    $stmtDocumentos->bindParam(':idElemento', $idElemento, PDO::PARAM_INT);
    $stmtDocumentos->execute();
    return $stmtDocumentos->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener información del listado
$listadoInfo = obtenerListadoInfo($conn, $idListadoEvaluados);
$numeroOficio = $listadoInfo['numeroOficio'];
$carpetaBaseListado = $listadoInfo['carpetaBase'];

// Obtener información del evaluado
$elemento = obtenerEvaluadoInfo($conn, $curp, $idListadoEvaluados);
$idElemento = $elemento['idSolicitud'];

// Construir la ruta a la carpeta del evaluado
$carpetaEvaluado = $carpetaBaseListado . '/' . $numeroOficio . '/' . $curp;

// Obtener documentos requeridos y existentes
$documentosRequeridos = obtenerDocumentosRequeridos($conn, $tipoEvaluacionId);
$documentosExistentes = obtenerDocumentosExistentes($conn, $idListadoEvaluados, $idElemento);

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
            $rutaDestinoRelativaWeb = "/Listados/{$numeroOficio}/{$curp}/$nombreArchivo";
            $rutaDestinoAbsolutaServidor = $carpetaEvaluado . "/" . $nombreArchivo;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoAbsolutaServidor)) {
                try {
                    $stmtInsertar = $conn->prepare("
                    INSERT INTO documentos_expediente (idExpediente, idElemento, idTipoDocumento, NombreArchivo, RutaArchivo, EstadoRevision, Comentarios)
                    VALUES (:idListadoEvaluados, :idElemento, :idTipoDocumento, :nombreArchivo, :rutaArchivo, 'Enviado', :comentarios)
                    ON DUPLICATE KEY UPDATE RutaArchivo = :rutaArchivo, EstadoRevision = 'Enviado', Comentarios = :comentarios
                ");
                $stmtInsertar->execute([
                    ':idListadoEvaluados' => $idListadoEvaluados, // Esta variable contiene el ID del listado
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

// Obtener listado de evaluados para la barra lateral
$evaluados = [];
try {
    $stmtEvaluados = $conn->prepare("
        SELECT CURP, Nombre, ApellidoP, ApellidoM
        FROM programacion_evaluados
        WHERE idListadoEvaluados = :idListadoEvaluados
        ORDER BY ApellidoP, ApellidoM, Nombre
    ");
    $stmtEvaluados->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
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
    <title>Carga de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.5/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <h1 class="mb-4">Carga de Documentos - <?php echo htmlspecialchars($elemento['Nombre'] . ' ' . $elemento['ApellidoP'] . ' ' . $elemento['ApellidoM']); ?></h1>
        <p><strong>Listado:</strong> <?php echo htmlspecialchars($numeroOficio); ?></p>
        <p><strong>CURP:</strong> <?php echo htmlspecialchars($curp); ?></p>

        <p class="mb-4">
        <a href="programacion.php?idListadoEvaluados=<?php echo htmlspecialchars($idListadoEvaluados); ?>&tipoEvaluacion=<?php echo htmlspecialchars($tipoEvaluacionId); ?>" class="btn btn-secondary"> <i class="bi bi-arrow-left me-2"></i> Volver a Programacion
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
                            <a href="carga_documentos.php?idListadoEvaluados=<?php echo htmlspecialchars($idListadoEvaluados); ?>&curp=<?php echo urlencode($evaluado['CURP']); ?>&tipoEvaluacion=<?php echo htmlspecialchars($tipoEvaluacionId); ?>" class="list-group-item list-group-item-action<?php echo ($evaluado['CURP'] === $curp) ? ' active' : ''; ?>">
                                <?php echo htmlspecialchars($evaluado['ApellidoP'] . ' ' . $evaluado['ApellidoM'] . ', ' . $evaluado['Nombre']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
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
                                            <button type="button" class="btn btn-info btn-sm w-100 visualizar-documento"
                                                data-bs-toggle="modal" data-bs-target="#visualizarModal"
                                                data-url="visualizar_documento.php?ruta=<?php echo urlencode($documento['RutaArchivo']); ?>">Visualizar</button>
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
                    <button type="submit" name="enviar_parcial" class="btn btn-warning ms-2">
                        Enviar Parcial
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visualizarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Visualizar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe src="" id="iframe-visualizar-documento" width="100%" height="500px"></iframe>
                </div>
            </div>
        </div>
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
                                    <button type="button" class="btn btn-info btn-sm visualizar-documento w-100"
                                                data-bs-toggle="modal" data-bs-target="#visualizarModal"
                                                data-url="visualizar_documento.php?ruta=${encodeURIComponent(response.rutaArchivo)}">Visualizar</button>
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
                            $('#mensajes').html('<div class="alert alert-danger">Error al eliminar el documento: ' + error + '</div>');
                        }
                    });
                }
            });

            // Visualizar Documento
            $(document).on('click', '.visualizar-documento', function (e) {
                e.preventDefault();
                const url = $(this).data('url');
                $('#iframe-visualizar-documento').attr('src', url);
                $('#visualizarModal').modal('show');
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>