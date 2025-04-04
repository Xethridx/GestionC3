<?php
$carpeta_prueba = __DIR__ . '/prueba_listado';
if (!is_dir($carpeta_prueba)) {
    if (mkdir($carpeta_prueba, 0777, true)) {
        echo "Carpeta de prueba creada correctamente.";
    } else {
        echo "Error al crear la carpeta de prueba.";
        $error = error_get_last();
        echo "Mensaje de error: " . $error['message'];
    }
} else {
    echo "La carpeta de prueba ya existe.";
}
?>