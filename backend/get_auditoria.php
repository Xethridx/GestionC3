<?php
require 'auditoria.php';   // Incluir funciones de auditoría
require '../auth.php';      // Incluir autenticación
require '../conexion.php'; // Incluir conexión

// Verificar que el usuario sea administrador (o rol con permiso para ver auditoría)
if (!verificarRol(['administrador'])) { // Ajustar roles permitidos para auditoría
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado. No tienes permisos para ver la auditoría.']);
    exit;
}

$conn = conectarDB();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar a la base de datos.']);
    exit;
}

$registrosAuditoria = obtenerRegistrosAuditoria($conn);

if ($registrosAuditoria !== false) {
    echo json_encode(['auditoria' => $registrosAuditoria]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error al obtener los registros de auditoría.']);
}

$conn = null;
?>