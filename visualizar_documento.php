<?php
// visualizar_documento.php

// Asegurarse de que se proporciona el parámetro 'ruta'
if (!isset($_GET['ruta']) || empty($_GET['ruta'])) {
    die("Error: No se especificó la ruta del documento.");
}

$rutaRelativa = $_GET['ruta'];

// Construir la ruta absoluta al archivo
$rutaAbsoluta = __DIR__ . $rutaRelativa;

// Seguridad: Verificar que la ruta no intente acceder a archivos fuera del directorio esperado
$rutaAbsolutaReal = realpath($rutaAbsoluta);
$directorioBaseListadosReal = realpath(__DIR__ . '/Listados');

if ($rutaAbsolutaReal === false || strpos($rutaAbsolutaReal, $directorioBaseListadosReal) !== 0) {
    die("Error: Ruta de documento inválida o acceso no autorizado.");
}

// Verificar si el archivo existe
if (!file_exists($rutaAbsolutaReal)) {
    header("HTTP/1.0 404 Not Found");
    die("Error: El documento no se encontró.");
}

// Obtener el tipo MIME del archivo
$mime = mime_content_type($rutaAbsolutaReal);

// Establecer las cabeceras para mostrar el PDF en el navegador
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($rutaAbsolutaReal) . '"');
header('Content-Length: ' . filesize($rutaAbsolutaReal));
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Fecha antigua para evitar caché en navegadores antiguos

// Leer y enviar el archivo al navegador
readfile($rutaAbsolutaReal);

exit;
?>