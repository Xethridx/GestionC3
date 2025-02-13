<?php
require 'usuarios.php';
require '../auth.php';
require '../conexion.php';
require 'auditoria.php'; // Incluir auditoría para registrar eliminaciones


// Verificar rol (solo administradores pueden eliminar usuarios, o define tu propia lógica de roles)
if (!verificarRol(['administrador'])) { // Ajusta roles permitidos
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado. No tienes permisos para eliminar usuarios.']);
    exit;
}

$conn = conectarDB();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar a la base de datos.']);
    exit;
}

// Recibir ID de usuario a eliminar (por POST en JSON)
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] == "POST && $data") {
    $idUsuario = isset($data['idUsuario']) ? intval($data['idUsuario']) : 0; // Asegurar que sea entero

    if ($idUsuario <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'ID de usuario inválido.']);
        exit;
    }

    // TODO:  Implementar lógica para eliminar el usuario de la base de datos
    $usuario_eliminado = eliminarUsuario($conn, $idUsuario); // Llamar a función en usuarios.php

    if ($usuario_eliminado) {
        // Auditoría: Registrar la eliminación del usuario
        $usuarioAdmin = $_SESSION['usuario']; // Usuario admin que realiza la acción
        $idAdminUsuario =  $_SESSION['usuario_id'];
        $detallesAuditoria = "Usuario eliminado: ID " . $idUsuario;
        registrarAuditoria($conn, $idAdminUsuario, 'Eliminación de Usuario', $detallesAuditoria);


        http_response_code(200); // OK
        echo json_encode(['mensaje' => 'Usuario eliminado exitosamente.']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Error al eliminar el usuario. Inténtalo de nuevo.']);
    }

} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Petición no válida.']);
}

$conn = null;
?>