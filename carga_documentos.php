<?php
session_start();
include 'conexion.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos ($conn)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// *** FUNCIONES DE CONSULTA A LA BASE DE DATOS ***

function obtenerFolioExpediente($conn, $idExpediente) {
    try {
        $stmtExpediente = $conn->prepare("SELECT FolioExpediente FROM expedientes WHERE idExpediente = :idExpediente");
        $stmtExpediente->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
        $stmtExpediente->execute();
        $expediente = $stmtExpediente->fetch(PDO::FETCH_ASSOC);

        if (!$expediente) {
            error_log("carga_documentos.php: Error - Expediente no encontrado: idExpediente=$idExpediente");
            return false;
        }
        return $expediente['FolioExpediente'];
    } catch (PDOException $e) {
        error_log("carga_documentos.php: PDOException - obtenerFolioExpediente: " . $e->getMessage());
        return false;
    }
}

function obtenerDetallesEvaluado($conn, $curp) {
    try {
        $stmtElemento = $conn->prepare("
            SELECT e.idSolicitud, e.Nombre, e.ApellidoP, e.ApellidoM
            FROM programacion_evaluados e
            WHERE e.CURP = :curp
        ");
        $stmtElemento->bindParam(':curp', $curp, PDO::PARAM_STR);
        $stmtElemento->execute();
        $elemento = $stmtElemento->fetch(PDO::FETCH_ASSOC);

        if (!$elemento) {
            error_log("carga_documentos.php: Error - Evaluado no encontrado: CURP=$curp");
            return false;
        }
        return $elemento;
    } catch (PDOException $e) {
        error_log("carga_documentos.php: PDOException - obtenerDetallesEvaluado: " . $e->getMessage());
        return false;
    }
}

function obtenerDocumentosRequeridos($conn, $tipoEvaluacionId) {
    try {
        $stmtRequeridos = $conn->prepare("
            SELECT td.idTipoDocumento, td.Documento
            FROM tipo_documento_motivo tdm
            JOIN tipo_documento td ON tdm.idTipoDocumento = td.idTipoDocumento
            WHERE tdm.idMotivo = :idMotivo
        ");
        $stmtRequeridos->bindParam(':idMotivo', $tipoEvaluacionId, PDO::PARAM_INT);
        $stmtRequeridos->execute();
        return $documentosRequeridos = $stmtRequeridos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("carga_documentos.php: PDOException - obtenerDocumentosRequeridos: " . $e->getMessage());
        return false;
    }
}

function obtenerDocumentosExistentes($conn, $idExpediente, $idElemento) {
    try {
        $stmtDocumentos = $conn->prepare("
            SELECT d.idDocumento, d.idTipoDocumento, d.NombreArchivo, d.RutaArchivo, d.EstadoRevision, d.Comentarios, td.Documento
            FROM documentos_expediente d
            JOIN tipo_documento td ON d.idTipoDocumento = td.idTipoDocumento
            WHERE d.idExpediente = :idExpediente AND d.idElemento = :idElemento
        ");
        $stmtDocumentos->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
        $stmtDocumentos->bindParam(':idElemento', $idElemento, PDO::PARAM_INT);
        $stmtDocumentos->execute();
        return $documentosExistentes = $stmtDocumentos->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("carga_documentos.php: PDOException - obtenerDocumentosExistentes: " . $e->getMessage());
        return false;
    }
}

function insertarOActualizarDocumento($conn, $dataDocumento) {
    try {
        $stmtInsertar = $conn->prepare("
            INSERT INTO documentos_expediente (idExpediente, idElemento, idTipoDocumento, NombreArchivo, RutaArchivo, EstadoRevision, Comentarios)
            VALUES (:idExpediente, :idElemento, :idTipoDocumento, :nombreArchivo, :rutaArchivo, 'Enviado', :comentarios)
            ON DUPLICATE KEY UPDATE
            NombreArchivo = :nombreArchivo,
            RutaArchivo = :rutaArchivo,
            EstadoRevision = 'Enviado',
            Comentarios = :comentarios,
            FechaCarga = CURRENT_TIMESTAMP
        ");
        $stmtInsertar->bindParam(':idExpediente', $dataDocumento['idExpediente'], PDO::PARAM_INT);
        $stmtInsertar->bindParam(':idElemento', $dataDocumento['idElemento'], PDO::PARAM_INT);
        $stmtInsertar->bindParam(':idTipoDocumento', $dataDocumento['idTipoDocumento'], PDO::PARAM_INT);
        $stmtInsertar->bindParam(':nombreArchivo', $dataDocumento['nombreArchivo'], PDO::PARAM_STR);
        $stmtInsertar->bindParam(':rutaArchivo', $dataDocumento['rutaArchivo'], PDO::PARAM_STR);
        $stmtInsertar->bindParam(':comentarios', $dataDocumento['comentarios'], PDO::PARAM_STR);
        if (!$stmtInsertar->execute()) {
            error_log("carga_documentos.php: Error en execute Insertar/Actualizar Documento: " . print_r($stmtInsertar->errorInfo(), true));
            return false;
        }
        return true;
    } catch (PDOException $e) {
        error_log("carga_documentos.php: PDOException - insertarOActualizarDocumento: " . $e->getMessage());
        return false;
    }
}


function eliminarDocumento($conn, $idDocumento) {
    try {
        // Obtener idTipoDocumento, idElemento, idExpediente antes de eliminar
        $stmtObtenerInfo = $conn->prepare("SELECT idTipoDocumento, idElemento, idExpediente FROM documentos_expediente WHERE idDocumento = :idDocumento");
        $stmtObtenerInfo->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
        $stmtObtenerInfo->execute();
        $docInfo = $stmtObtenerInfo->fetch(PDO::FETCH_ASSOC);

        if ($docInfo) {
            $idTipoDocumento = $docInfo['idTipoDocumento'];
            $idElemento = $docInfo['idElemento'];
            $idExpediente = $docInfo['idExpediente'];

            // Eliminar documento de la tabla documentos_expediente
            $stmtEliminar = $conn->prepare("DELETE FROM documentos_expediente WHERE idDocumento = :idDocumento");
            $stmtEliminar->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
            $stmtEliminar->execute();

             // Insertar de nuevo registro con estado 'Pendiente'
             try {
                $stmtInsertarPendiente = $conn->prepare("
                    INSERT INTO documentos_expediente (idExpediente, idElemento, idTipoDocumento, EstadoRevision)
                    VALUES (:idExpediente, :idElemento, :idTipoDocumento, 'Pendiente')
                    ON DUPLICATE KEY UPDATE EstadoRevision = 'Pendiente', RutaArchivo = NULL, NombreArchivo = NULL, Comentarios = NULL
                ");
                $stmtInsertarPendiente->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
                $stmtInsertarPendiente->bindParam(':idElemento', $idElemento, PDO::PARAM_INT);
                $stmtInsertarPendiente->bindParam(':idTipoDocumento', $idTipoDocumento, PDO::PARAM_INT);
                $stmtInsertarPendiente->execute();
                return true; // Eliminación y actualización a pendiente exitosas
            } catch (PDOException $e) {
                error_log("carga_documentos.php: PDOException - actualizarEstadoPendiente en eliminarDocumento: " . $e->getMessage());
                return false; // Error al actualizar a pendiente
            }
        } else {
            error_log("carga_documentos.php: Error - Documento no encontrado para eliminar: idDocumento=$idDocumento");
            return false; // Documento no encontrado
        }
    } catch (PDOException $e) {
        error_log("carga_documentos.php: PDOException - eliminarDocumento: " . $e->getMessage());
        return false; // Error general al eliminar
    }
}


function todosDocumentosEnviados($documentos) {
    if (empty($documentos)) {
        return false;
    }
    foreach ($documentos as $documento) {
        if ($documento['EstadoRevision'] === 'Pendiente') {
            return false;
        }
    }
    return true;
}

// *** FIN DE FUNCIONES DE CONSULTA ***

// *** VALIDACIÓN INICIAL DE PARÁMETROS GET ***
if (!isset($_GET['expediente']) || !isset($_GET['curp']) || !isset($_GET['tipoEvaluacion'])) {
    error_log("carga_documentos.php: Error - Parámetros GET insuficientes");
    die("Error: Parámetros insuficientes para cargar documentos.");
}

$idExpediente = intval($_GET['expediente']);
$curp = htmlspecialchars($_GET['curp']);
$tipoEvaluacionId = intval($_GET['tipoEvaluacion']);

// *** OBTENER DATOS INICIALES USANDO FUNCIONES ***
$folioExpediente = obtenerFolioExpediente($conn, $idExpediente);
if (!$folioExpediente) {
    die("Error al obtener Folio del Expediente.");
}

$elemento = obtenerDetallesEvaluado($conn, $curp);
if (!$elemento) {
    die("Error al obtener detalles del Evaluado.");
}

$documentosRequeridos = obtenerDocumentosRequeridos($conn, $tipoEvaluacionId);
if ($documentosRequeridos === false) {
    die("Error al obtener documentos requeridos.");
}

$documentosExistentes = obtenerDocumentosExistentes($conn, $idExpediente, $elemento['idSolicitud']);
if ($documentosExistentes === false) {
    die("Error al obtener documentos existentes.");
}

$carpetaEvaluado = __DIR__ . "/Expedientes/$folioExpediente/$curp";
if (!file_exists($carpetaEvaluado)) {
    if (!mkdir($carpetaEvaluado, 0777, true)) { // Añadido '!' para verificar el resultado de mkdir
        error_log("carga_documentos.php: Error al crear carpeta: $carpetaEvaluado");
        die("Error al crear carpeta de expediente."); // Mensaje de error más específico
    }
    error_log("carga_documentos.php: Carpeta creada: $carpetaEvaluado");
}

$documentos = [];
foreach ($documentosRequeridos as $requerido) {
    $encontrado = false;
    foreach ($documentosExistentes as $existente) {
        if ($requerido['idTipoDocumento'] == $existente['idTipoDocumento']) {
            $documentos[] = $existente;
            $encontrado = true;
            break;
        }
    }
    if (!$encontrado) {
        $documentos[] = [
            'idDocumento' => null,
            'idTipoDocumento' => $requerido['idTipoDocumento'],
            'NombreArchivo' => null,
            'RutaArchivo' => null,
            'EstadoRevision' => 'Pendiente',
            'Comentarios' => null,
            'Documento' => $requerido['Documento']
        ];
    }
}


$mensajes = [];

// *** PROCESAMIENTO DE SUBIDA DE DOCUMENTOS (POST - SIN AJAX) ***
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_documento'])) {
    error_log("carga_documentos.php: Inicio procesamiento POST - Subir Documento (SIN AJAX)");

    $idExpedientePost = intval($_POST['expediente']);
    $curpPost = htmlspecialchars($_POST['curp']);
    $tipoEvaluacionIdPost = intval($_POST['tipoEvaluacion']);
    $idTipoDocumentoPost = intval($_POST['idTipoDocumento']);
    $comentariosPost = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';


    if (!empty($_FILES['documento']['name'])) {
        $archivo = $_FILES['documento'];

        $maxSizeBytesPHP = 5 * 1024 * 1024; // 5MB
        $allowedTypesPHP = ['application/pdf'];

        if ($archivo['size'] > $maxSizeBytesPHP) {
            $mensajes[] = "<div class='alert alert-danger'>Error: El archivo excede el tamaño máximo (5MB).</div>";
        } elseif (!in_array($archivo['type'], $allowedTypesPHP)) {
            $mensajes[] = "<div class='alert alert-danger'>Error: Formato no permitido. Solo PDF.</div>";
        } else {
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreDocumentoTipo = $documentosRequeridos[array_search($idTipoDocumentoPost, array_column($documentosRequeridos, 'idTipoDocumento'))]['Documento'];
            $nombreArchivo = "{$curpPost}_" . str_replace(' ', '', $nombreDocumentoTipo) . ".$extension";
            $rutaDestinoRelativaWeb = "/Expedientes/$folioExpediente/$curpPost/$nombreArchivo";
            $rutaDestinoAbsolutaServidor = __DIR__ . "/Expedientes/$folioExpediente/$curpPost/$nombreArchivo";


            if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoAbsolutaServidor)) {
                $dataDocumento = [
                    'idExpediente' => $idExpedientePost,
                    'idElemento' => $elemento['idSolicitud'],
                    'idTipoDocumento' => $idTipoDocumentoPost,
                    'nombreArchivo' => $nombreArchivo,
                    'rutaArchivo' => $rutaDestinoRelativaWeb,
                    'comentarios' => $comentariosPost,
                ];

                if (insertarOActualizarDocumento($conn, $dataDocumento)) {
                    $mensajes[] = "<div class='alert alert-success'>Documento subido correctamente.</div>";
                } else {
                    $mensajes[] = "<div class='alert alert-danger'>Error al registrar documento en la base de datos.</div>";
                }
            } else {
                $mensajes[] = "<div class='alert alert-danger'>Error al mover el archivo subido al servidor.</div>";
            }
        }
    } else {
        $mensajes[] = "<div class='alert alert-warning'>No se seleccionó ningún documento.</div>";
    }
}

// *** PROCESAMIENTO DE ELIMINACIÓN DE DOCUMENTOS (POST - SIN AJAX) ***
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_documento'])) {
    error_log("carga_documentos.php: Iniciando procesamiento POST - Eliminar Documento (SIN AJAX)");
    $idDocumentoEliminar = intval($_POST['idDocumento']);
    if (eliminarDocumento($conn, $idDocumentoEliminar)) {
         $mensajes[] = "<div class='alert alert-success'>Documento eliminado correctamente. Estado actualizado a 'Pendiente'.</div>";
    } else {
        $mensajes[] = "<div class='alert alert-danger'>Error al eliminar el documento.</div>";
    }
}

// *** PROCESAMIENTO DE ENVIAR A REVISIÓN (POST - SIN AJAX) ***
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_revision'])) {
    error_log("carga_documentos.php: Inicio procesamiento POST - Enviar a Revisión (SIN AJAX)");
     if (todosDocumentosEnviados($documentos)) {
        // *** IMPLEMENTAR LÓGICA PARA ENVIAR A REVISIÓN AQUÍ (ej., actualizar estado en base de datos, enviar notificaciones) ***
        $mensajes[] = "<div class='alert alert-success'>Documentos enviados a revisión correctamente!</div>";
    } else {
        $mensajes[] = "<div class='alert alert-warning'>Por favor, suba todos los documentos requeridos antes de enviar a revisión.</div>";
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
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <h1 class="mb-4">Carga de Documentos - <?php echo htmlspecialchars($elemento['Nombre'] . ' ' . $elemento['ApellidoP'] . ' ' . $elemento['ApellidoM']); ?></h1>
        <p><strong>Expediente:</strong> <?php echo htmlspecialchars($folioExpediente); ?></p>
        <p><strong>CURP:</strong> <?php echo htmlspecialchars($curp); ?></p>

        <div id="mensajes">
            <?php
             if (!empty($mensajes)) {
                 foreach ($mensajes as $mensaje) {
                     echo $mensaje;
                 }
             }
             ?>
         </div>

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
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($documento['Documento']); ?></td>
                        <td>
                            <span class="badge <?php echo $documento['EstadoRevision'] === 'Pendiente' ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                <?php echo htmlspecialchars($documento['EstadoRevision']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($documento['Comentarios'] ?? ''); ?></td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <form method="POST" enctype="multipart/form-data" class="upload-form">
                                    <input type="hidden" name="expediente" value="<?php echo htmlspecialchars($_GET['expediente']); ?>">
                                    <input type="hidden" name="curp" value="<?php echo htmlspecialchars($_GET['curp']); ?>">
                                    <input type="hidden" name="tipoEvaluacion" value="<?php echo htmlspecialchars($_GET['tipoEvaluacion']); ?>">

                                    <div class="d-flex gap-1 align-items-center">
                                        <input type="hidden" name="idTipoDocumento" value="<?php echo $documento['idTipoDocumento']; ?>">
                                        <input type="file" name="documento" class="form-control form-control-sm w-50" required accept=".pdf">
                                        <input type="text" name="comentarios" class="form-control form-control-sm w-50" placeholder="Comentarios (opcional)">
                                        <button type="submit" name="subir_documento" class="btn btn-success btn-sm mt-0">Subir</button>
                                    </div>
                                </form>

                                <?php if (!is_null($documento['RutaArchivo'])): ?>
                                    <form method="POST" class="eliminar-documento-form">
                                        <input type="hidden" name="idDocumento" value="<?php echo $documento['idDocumento']; ?>">
                                        <button type="submit" name="eliminar_documento" class="btn btn-danger btn-sm eliminar-documento w-100">Eliminar</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (!is_null($documento['RutaArchivo'])): ?>
                                    <a href="<?php echo htmlspecialchars($documento['RutaArchivo']); ?>" target="_blank" class="btn btn-info btn-sm visualizar-documento w-100">Visualizar</a>
                                <?php endif; ?>

                                <button type="button" class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $documento['idDocumento']; ?>">Editar</button>
                            </div>
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

     <div class="modal fade" id="cargandoModal" tabindex="-1" aria-labelledby="cargandoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cargandoModalLabel">Cargando Documento...</h5>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Por favor, espere...</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>