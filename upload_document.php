<?php
session_start();

// Verificar si el usuario tiene permisos para cargar documentos
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'gestor')) {
    header("Location: login.php");
    exit();
}

// Incluir conexión a la base de datos
include 'conexion.php';

// Verificar si se recibió un archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $documentoId = intval($_POST['documentoId']);
    $numeroExpediente = htmlspecialchars($_POST['numeroExpediente']);

    // Verificar el archivo
    $file = $_FILES['documento'];
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    // Validar tipo de archivo
    $allowed = ['pdf'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize <= 5000000) { // Tamaño máximo 5MB
                $newFileName = $numeroExpediente . "_doc_" . $documentoId . "." . $fileExt;
                $uploadPath = "uploads/" . $newFileName;

                // Mover archivo a la carpeta de destino
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    try {
                        // Actualizar estado del documento en la base de datos
                        $sql = "UPDATE documentos_expediente 
                                SET EstadoRevision = 'Validado', NombreArchivo = ?, RutaArchivo = ? 
                                WHERE idDocumento = ? 
                                AND idElemento = (SELECT idExpediente FROM expedientes WHERE FolioExpediente = ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$fileName, $uploadPath, $documentoId, $numeroExpediente]);

                        $_SESSION['success'] = "Documento cargado exitosamente.";
                    } catch (PDOException $e) {
                        $_SESSION['error'] = "Error al actualizar la base de datos: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error'] = "Error al subir el archivo.";
                }
            } else {
                $_SESSION['error'] = "El archivo es demasiado grande (máximo 5MB).";
            }
        } else {
            $_SESSION['error'] = "Hubo un error al subir el archivo.";
        }
    } else {
        $_SESSION['error'] = "Tipo de archivo no permitido. Solo se aceptan PDFs.";
    }
} else {
    $_SESSION['error'] = "No se recibió ningún archivo.";
}

// Redirigir de vuelta al módulo de carga de documentos
header("Location: cargar_documentos.php");
exit();
?>
