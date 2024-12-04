<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php"); // Redirige si no está autenticado
    exit();
}

// Función para verificar el rol
function verificarRol($rolesPermitidos) {
    if (!in_array($_SESSION['tipo_usuario'], $rolesPermitidos)) {
        header("Location: acceso_denegado.php"); // Redirige si no tiene permisos
        exit();
    }
}
?>
