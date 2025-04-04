<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario tiene sesión activa y el rol permitido
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'gestor', 'enlace'])) {
    header("Location: login.php");
    exit();
}

// Verificar que el ID del elemento y el número de expediente estén presentes
if (!isset($_GET['elemento_id']) || !isset($_GET['numero_expediente'])) {
    die("Error: Elemento o expediente no especificado.");
}


$elemento_id = intval($_GET['elemento_id']);
$numero_expediente = htmlspecialchars($_GET['numero_expediente']);

// Conectar a la base de datos
include 'conexion.php';

// Obtener la información del elemento
try {
    $stmt = $conn->prepare("SELECT * FROM elementos WHERE id = :elemento_id");
    $stmt->bindParam(':elemento_id', $elemento_id, PDO::PARAM_INT);
    $stmt->execute();
    $elemento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$elemento) {
        die("Error: Elemento no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al obtener elemento: " . $e->getMessage());
}

// Procesar la carga de documentos
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $nombre_documento = htmlspecialchars($_POST['nombre_documento']);
    $archivo = $_FILES['documento'];

    // Validar el archivo
    if ($archivo['error'] === UPLOAD_ERR_OK) {
        if ($archivo['size'] > 5242880) { // Limitar tamaño a 5 MB
            $mensaje = "Error: El archivo excede el tamaño máximo permitido de 5 MB.";
        } elseif (mime_content_type($archivo['tmp_name']) !== 'application/pdf') { // Validar tipo de archivo
            $mensaje = "Error: Solo se permiten archivos en formato PDF.";
        } else {
            // Ajustar la ruta del documento
            $curp = $elemento['curp'];
            $ruta_base = __DIR__ . "/Expedientes/$numero_expediente/$curp";
            $nombre_archivo = $curp . '_' . str_replace(' ', '_', $nombre_documento) . '.pdf';
            $ruta_documento = "$ruta_base/$nombre_archivo";

            // Crear las carpetas necesarias si no existen
            if (!is_dir($ruta_base)) {
                mkdir($ruta_base, 0777, true);
            }

            // Verificar si el documento ya existe
            $stmtVerificar = $conn->prepare("
                SELECT * FROM documentos 
                WHERE elemento_id = :elemento_id AND nombre_documento = :nombre_documento
            ");
            $stmtVerificar->bindParam(':elemento_id', $elemento_id, PDO::PARAM_INT);
            $stmtVerificar->bindParam(':nombre_documento', $nombre_documento, PDO::PARAM_STR);
            $stmtVerificar->execute();

            if ($stmtVerificar->rowCount() > 0) {
                $mensaje = "Error: El documento '$nombre_documento' ya está registrado para este elemento.";
            } else {
                // Mover el archivo subido a la ubicación deseada
                if (move_uploaded_file($archivo['tmp_name'], $ruta_documento)) {
                    // Registrar el documento en la base de datos
                    try {
                        $stmtInsertar = $conn->prepare("
                            INSERT INTO documentos (elemento_id, nombre_documento, ruta_documento, estado) 
                            VALUES (:elemento_id, :nombre_documento, :ruta_documento, 'subido')
                        ");
                        $stmtInsertar->bindParam(':elemento_id', $elemento_id, PDO::PARAM_INT);
                        $stmtInsertar->bindParam(':nombre_documento', $nombre_documento, PDO::PARAM_STR);
                        $stmtInsertar->bindParam(':ruta_documento', $ruta_documento, PDO::PARAM_STR);
                        $stmtInsertar->execute();

                        $mensaje = "Documento subido correctamente.";
                    } catch (PDOException $e) {
                        $mensaje = "Error al registrar el documento: " . $e->getMessage();
                    }
                } else {
                    $mensaje = "Error al mover el archivo.";
                }
            }
        }
    } else {
        $mensaje = "Error en el archivo subido.";
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Documentos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center">Subir Documentos para <?php echo htmlspecialchars($elemento['nombre_completo']); ?></h2>
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Detalles del Elemento -->
        <div class="mt-4">
            <h4>Detalles del Elemento</h4>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($elemento['nombre_completo']); ?></p>
            <p><strong>CURP:</strong> <?php echo htmlspecialchars($elemento['curp']); ?></p>
            <p><strong>Expediente:</strong> <?php echo $numero_expediente; ?></p>
        </div>

        <!-- Formulario para Subir Documentos -->
        <form action="subir_documentos.php?elemento_id=<?php echo $elemento_id; ?>&numero_expediente=<?php echo $numero_expediente; ?>" 
              method="POST" enctype="multipart/form-data" class="mt-4">
<div class="mb-3">
    <label for="nombre_documento" class="form-label">Nombre del Documento</label>
    <select id="nombre_documento" name="nombre_documento" class="form-select" required>
        <option value="" selected disabled>Selecciona un documento...</option>
        <option value="Historia de vida firmado">Historia de vida firmado</option>
        <option value="Acta de nacimiento formato nuevo">Acta de nacimiento formato nuevo</option>
        <option value="CURP actualizada">CURP actualizada</option>
        <option value="Comprobante de domicilio">Comprobante de domicilio</option>
        <!-- Añadir más documentos según sea necesario -->
    </select>
</div>
<div class="mb-3">
    <label for="documento" class="form-label">Archivo PDF</label>
    <input type="file" id="documento" name="documento" class="form-control" accept="application/pdf" required>
</div>

            <button type="submit" class="btn btn-primary">Subir Documento</button>
        </form>

        <!-- Documentos Subidos -->
        <div class="mt-5">
            <h4>Documentos Subidos</h4>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre del Documento</th>
                        <th>Estado</th>
                        <th>Fecha de Subida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Obtener documentos del elemento
                    try {
                        $stmt = $conn->prepare("SELECT * FROM documentos WHERE elemento_id = :elemento_id");
                        $stmt->bindParam(':elemento_id', $elemento_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if ($documentos):
                            foreach ($documentos as $documento):
                    ?>
                                <tr>
                                    <td><?php echo $documento['id']; ?></td>
                                    <td><?php echo htmlspecialchars($documento['nombre_documento']); ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo ucfirst($documento['estado']); ?></span>
                                    </td>
                                    <td><?php echo $documento['fecha_subida']; ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($documento['ruta_documento']); ?>" target="_blank" class="btn btn-secondary btn-sm">Ver</a>
                                    </td>
                                </tr>
                    <?php
                            endforeach;
                        else:
                    ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay documentos subidos.</td>
                            </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
