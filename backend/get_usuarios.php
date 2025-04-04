<?php
require 'usuarios.php'; // Incluye el archivo con funciones de usuario
require '../auth.php';  // Incluye el archivo de autenticación
require '../conexion.php';

// Verificar que el usuario sea administrador
if (!verificarRol(['administrador'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado. No tienes permisos para listar usuarios.']);
    exit;
}

$conn = conectarDB();
if (!$conn) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error al conectar a la base de datos.']);
    exit;
}

$usuarios = obtenerUsuarios($conn);

if ($usuarios !== false) {
    echo json_encode(['usuarios' => $usuarios]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error al obtener la lista de usuarios.']);
}

$conn = null; // Cerrar la conexión
?>