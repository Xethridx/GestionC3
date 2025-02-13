<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validar permisos
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'gestor')) {
    header("Location: login.php");
    exit();
}

// Validar parámetros GET
if (!isset($_GET['expediente']) || !isset($_GET['curp']) || !isset($_GET['tipoEvaluacion'])) {
    die("Error: Parámetros insuficientes para cargar documentos.");
}

$idExpediente = intval($_GET['expediente']);
$curp = htmlspecialchars($_GET['curp']);
$tipoEvaluacionId = intval($_GET['tipoEvaluacion']);

// Consultar expediente para obtener el FolioExpediente
try {
    $stmtExpediente = $conn->prepare("SELECT FolioExpediente FROM expedientes WHERE idExpediente = :idExpediente");
    $stmtExpediente->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
    $stmtExpediente->execute();
    $expediente = $stmtExpediente->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        die("Error: El expediente con ID $idExpediente no existe.");
    }

    $folioExpediente = $expediente['FolioExpediente'];
} catch (PDOException $e) {
    die("Error al validar el expediente: " . $e->getMessage());
}

// Consultar detalles del evaluado
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
        die("Error: No se encontró el evaluado.");
    }

    $idElemento = $elemento['idSolicitud'];
} catch (PDOException $e) {
    die("Error al consultar evaluado: " . $e->getMessage());
}

// Crear carpeta del evaluado si no existe
$carpetaEvaluado = __DIR__ . "/Expedientes/$folioExpediente/$curp";
if (!file_exists($carpetaEvaluado)) {
    mkdir($carpetaEvaluado, 0777, true);
}

// Consultar documentos requeridos
try {
    $stmtRequeridos = $conn->prepare("
        SELECT td.idTipoDocumento, td.Documento
        FROM tipo_documento_motivo tdm
        JOIN tipo_documento td ON tdm.idTipoDocumento = td.idTipoDocumento
        WHERE tdm.idMotivo = :idMotivo
    ");
    $stmtRequeridos->bindParam(':idMotivo', $tipoEvaluacionId, PDO::PARAM_INT);
    $stmtRequeridos->execute();
    $documentosRequeridos = $stmtRequeridos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar documentos requeridos: " . $e->getMessage());
}

// Consultar documentos existentes
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
    $documentosExistentes = $stmtDocumentos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar documentos existentes: " . $e->getMessage());
}

// Combinar documentos requeridos con existentes
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

// Función para verificar si todos los documentos están en estado 'Enviado' o superior
function todosDocumentosEnviados($documentos) {
    if (empty($documentos)) {
        return false; // Si no hay documentos, no se pueden enviar a revisión
    }
    foreach ($documentos as $documento) {
        if ($documento['EstadoRevision'] === 'Pendiente') {
            return false; // Si algún documento está pendiente, retornar false
        }
    }
    return true; // Si todos los documentos no están pendientes, retornar true
}


// Manejo de mensajes
$mensajes = [];

// Procesar carga de documentos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_documento'])) {
    $idTipoDocumento = intval($_POST['idTipoDocumento']);
    $comentarios = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';

    if (!empty($_FILES['documento']['name'])) {
        $archivo = $_FILES['documento']; // Obtener información del archivo

        $maxSizeBytesPHP = 5 * 1024 * 1024; // 5MB en bytes (igual que en JS, pero en PHP)
        $allowedTypesPHP = ['application/pdf']; // Tipos MIME permitidos (PDF)

        if ($archivo['size'] > $maxSizeBytesPHP) {
            $mensajes[] = "<div class='alert alert-danger'>Error: El archivo excede el tamaño máximo permitido (5MB). Subida cancelada.</div>";
        } elseif (!in_array($archivo['type'], $allowedTypesPHP)) {
            $mensajes[] = "<div class='alert alert-danger'>Error: Formato de archivo no permitido. Solo se permiten archivos PDF. Subida cancelada.</div>";
        } else { // Si pasa las validaciones, PROCESAR LA SUBIDA
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreDocumento = $documentosRequeridos[array_search($idTipoDocumento, array_column($documentosRequeridos, 'idTipoDocumento'))]['Documento'];
            $nombreArchivo = "{$curp}_" . str_replace(' ', '', $nombreDocumento) . ".$extension";
            $rutaDestinoRelativaWeb = "/Expedientes/$folioExpediente/$curp/$nombreArchivo";
            $rutaDestinoAbsolutaServidor = __DIR__ . "/Expedientes/$folioExpediente/$curp/$nombreArchivo";

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestinoAbsolutaServidor)) {
                try {
                    $stmtInsertar = $conn->prepare("
                        INSERT INTO documentos_expediente (idExpediente, idElemento, idTipoDocumento, NombreArchivo, RutaArchivo, EstadoRevision, Comentarios)
                        VALUES (:idExpediente, :idElemento, :idTipoDocumento, :nombreArchivo, :rutaArchivo, 'Enviado', :comentarios)
                        ON DUPLICATE KEY UPDATE RutaArchivo = :rutaArchivo, EstadoRevision = 'Enviado', Comentarios = :comentarios
                    ");
                    $stmtInsertar->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
                    $stmtInsertar->bindParam(':idElemento', $idElemento, PDO::PARAM_INT);
                    $stmtInsertar->bindParam(':idTipoDocumento', $idTipoDocumento, PDO::PARAM_INT);
                    $stmtInsertar->bindParam(':nombreArchivo', $nombreArchivo, PDO::PARAM_STR);
                    $stmtInsertar->bindParam(':rutaArchivo', $rutaDestinoRelativaWeb, PDO::PARAM_STR);
                    $stmtInsertar->bindParam(':rutaArchivo', $rutaDestinoRelativaWeb, PDO::PARAM_STR);
                    $stmtInsertar->bindParam(':comentarios', $comentarios, PDO::PARAM_STR);
                    $stmtInsertar->execute();

                    $mensajes[] = "<div class='alert alert-success'>Documento subido correctamente.</div>";
                } catch (PDOException $e) {
                    $mensajes[] = "<div class='alert alert-danger'>Error al registrar documento: " . $e->getMessage() . "</div>";
                }
            } else {
                $mensajes[] = "<div class='alert alert-danger'>Error al subir el documento.</div>";
            }
        }
    } else {
        $mensajes[] = "<div class='alert alert-warning'>No se seleccionó ningún documento.</div>";
    }
}
// Procesar eliminar documento (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_documento']) && $_POST['eliminar_documento'] === 'true') {
    $idDocumento = intval($_POST['idDocumento']);
    try {
        // Obtener idTipoDocumento e idElemento antes de eliminar (Código completo de eliminación + estado pendiente)
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

            // Actualizar estado a 'Pendiente' (insertar de nuevo el registro, sin RutaArchivo y con estado Pendiente)
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

                $mensajes[] = "<div class='alert alert-success'>Documento eliminado correctamente. Estado actualizado a 'Pendiente'.</div>";
            } catch (PDOException $e) {
                $mensajes[] = "<div class='alert alert-danger'>Error al actualizar estado a 'Pendiente' después de eliminar documento: " . $e->getMessage() . "</div>";
            }
        } else {
            $mensajes[] = "<div class='alert alert-warning'>Documento no encontrado para eliminar.</div>";
        }
    } catch (PDOException $e) {
        $mensajes[] = "<div class='alert alert-danger'>Error al eliminar documento: " . $e->getMessage() . "</div>";
    }
}
// Eliminar documento (formulario tradicional - ya no se usa, código redundante, se puede eliminar si estás seguro)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_documento'])) {
    $idDocumento = intval($_POST['idDocumento']);
    try {
        $stmtEliminar = $conn->prepare("DELETE FROM documentos_expediente WHERE idDocumento = :idDocumento");
        $stmtEliminar->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
        $stmtEliminar->execute();

        $mensajes[] = "<div class='alert alert-success'>Documento eliminado correctamente.</div>";
    } catch (PDOException $e) {
        $mensajes[] = "<div class='alert alert-danger'>Error al eliminar documento: " . $e->getMessage() . "</div>";
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
                                    <div class="d-flex gap-1 align-items-center">
                                        <input type="hidden" name="idTipoDocumento" value="<?php echo $documento['idTipoDocumento']; ?>">
                                        <input type="file" name="documento" class="form-control form-control-sm w-50" required accept=".pdf">
                                        <input type="text" name="comentarios" class="form-control form-control-sm w-50" placeholder="Comentarios (opcional)">
                                        <button type="submit" name="subir_documento" class="btn btn-success btn-sm mt-0">Subir</button>
                                    </div>
                                </form>

                                <?php if (!is_null($documento['RutaArchivo'])): ?>
                                    <form class="eliminar-documento-form">
                                        <input type="hidden" name="idDocumento" value="<?php echo $documento['idDocumento']; ?>">
                                        <button type="submit" name="eliminar_documento" class="btn btn-danger btn-sm eliminar-documento w-100">Eliminar</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (!is_null($documento['RutaArchivo'])): ?>
                                    <a href="#" class="btn btn-info btn-sm visualizar-documento w-100" data-url="<?php echo htmlspecialchars($documento['RutaArchivo']); ?>">Visualizar</a>
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

    <script>
        $(document).ready(function () {
            // Subir Documento
            $('.upload-form').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const fileInput = $(this).find('input[type="file"]')[0];
                const file = fileInput.files[0];

                if (!file) {
                    $('#mensajes').html('<div class="alert alert-warning">Por favor, selecciona un documento.</div>');
                    return;
                }

                const maxSizeMB = 5; // Tamaño máximo en MB
                const maxSizeBytes = maxSizeMB * 1024 * 1024; // Convertir a bytes
                const allowedTypes = ['application/pdf']; // Tipos MIME permitidos (PDF)

                if (file.size > maxSizeBytes) {
                    $('#mensajes').html(`<div class="alert alert-danger">El archivo excede el tamaño máximo permitido (${maxSizeMB}MB).</div>`);
                    return;
                }

                if (!allowedTypes.includes(file.type)) {
                    $('#mensajes').html('<div class="alert alert-danger">Formato de archivo no permitido. Solo se permiten archivos PDF.</div>');
                    return;
                }

                // Mostrar modal de "Cargando..." ANTES de la llamada AJAX
                $('#cargandoModal').modal('show');

                $.ajax({
                    url: 'carga_documentos.php', // Asegúrate de que apunta a carga_documentos.php o a tu script PHP correcto
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        // Ocultar modal de "Cargando..." en caso de éxito
                        $('#cargandoModal').modal('hide');
                        $('#mensajes').html('<div class="alert alert-success">Documento subido correctamente.</div>');
                        $('#tabla-documentos').load(location.href + ' #tabla-documentos');
                    },
                    error: function () {
                        // Ocultar modal de "Cargando..." también en caso de error
                        $('#cargandoModal').modal('hide');
                        $('#mensajes').html('<div class="alert alert-danger">Error al subir el documento.</div>');
                    }
                });
            });

            // Eliminar Documento
            $('.eliminar-documento-form').on('submit', function (e) {
                e.preventDefault(); // Evita la recarga completa de la página
                const idDocumento = $(this).find('input[name="idDocumento"]').val(); // Obtén el idDocumento dentro del formulario
                $.post('carga_documentos.php', { eliminar_documento: true, idDocumento: idDocumento }, function (response) { // Apunta a carga_documentos.php y pasa 'eliminar_documento=true' y 'idDocumento'
                    $('#mensajes').html('<div class="alert alert-success">Documento eliminado correctamente. Estado actualizado a \'Pendiente\'.</div>');
                    $('#tabla-documentos').load(location.href + ' #tabla-documentos');
                }).fail(function () {
                    $('#mensajes').html('<div class="alert alert-danger">Error al eliminar el documento.</div>');
                });
            });


            // Visualizar Documento (sin cambios, pero importante que esté)
            $('.visualizar-documento').on('click', function (e) {
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

            // Editar Documento
            $('.editar-form').on('submit', function (e) {
                e.preventDefault();
                const idDocumento = $(this).find('input[name="idDocumento"]').val(); // Obtén el idDocumento dentro del formulario
                const data = $(this).serialize();
                $.post('carga_documentos.php', { editar_documento: true, idDocumento: idDocumento, ...data }, function (response) {
                    $('#mensajes').html('<div class="alert alert-success">Documento actualizado correctamente.</div>');
                    $('#tabla-documentos').load(location.href + ' #tabla-documentos');
                }).fail(function () {
                    $('#mensajes').html('<div class="alert alert-danger">Error al actualizar el documento.</div>');
                });
            });

            // Enviar a Revisión
            $('#enviar-revision-form').on('submit', function (e) {
                e.preventDefault();
                $.post('carga_documentos.php', { enviar_revision: true }, function (response) {
                    $('#mensajes').html('<div class="alert alert-success">Documentos enviados a revisión correctamente.</div>');
                    $('#tabla-documentos').load(location.href + ' #tabla-documentos');
                }).fail(function () {
                    $('#mensajes').html('<div class="alert alert-danger">Error al enviar documentos a revisión.</div>');
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>