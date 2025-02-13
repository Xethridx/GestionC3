<?php
require 'usuarios.php';
require '../auth.php';
require '../conexion.php';
require 'auditoria.php'; // Incluir auditoría para registrar cambios


// Verificar que el usuario sea administrador o gestor (o roles permitidos para editar usuarios)
if (!verificarRol(['administrador'])) { // Ajusta los roles permitidos según tus necesidades
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado. No tienes permisos para editar usuarios.']);
    exit;
}

$conn = conectarDB();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar a la base de datos.']);
    exit;
}

// Recibir datos del formulario (en formato JSON desde AJAX)
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $data) {
    $idUsuario = isset($data['idUsuario']) ? intval($data['idUsuario']) : 0; // Asegurar ID sea entero
    $nombre = isset($data['nombre']) ? trim(htmlspecialchars($data['nombre'])) : ''; // Sanitizar
    $apellidoP = isset($data['apellidoP']) ? trim(htmlspecialchars($data['apellidoP'])) : ''; // Sanitizar
    $apellidoM = isset($data['apellidoM']) ? trim(htmlspecialchars($data['apellidoM'])) : ''; // Sanitizar
    $usuario = isset($data['usuario']) ? trim(htmlspecialchars($data['usuario'])) : ''; // Sanitizar
    $correo = isset($data['correo']) ? trim(htmlspecialchars($data['correo'])) : '';     // Sanitizar
    $rol = isset($data['rol']) ? trim(htmlspecialchars($data['rol'])) : '';         // Sanitizar


    // Validaciones en el servidor (¡IMPORTANTE!)
    if ($idUsuario <= 0 || empty($nombre) || empty($apellidoP) || empty($usuario) || empty($correo) || empty($rol)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Datos de usuario inválidos o incompletos para la edición.']);
        exit;
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo electrónico no válido.']);
        exit;
    }
    // TODO:  Validar si el nuevo nombre de usuario ya existe (si se permite cambiar usuario) - ¡Cuidado con el usuario actual!


    // TODO: Implementar la lógica para actualizar el usuario en la base de datos
    $usuario_actualizado = actualizarUsuario(
        $conn,
        $idUsuario,
        $usuario,
        $nombre,
        $apellidoP,
        $apellidoM,
        $correo,
        $rol
    ); // Llamar a función en usuarios.php

    if ($usuario_actualizado) {
        // Auditoría: Registrar la edición del usuario
        $usuarioAdmin = $_SESSION['usuario']; // Usuario admin que realiza la acción
        $idAdminUsuario =  $_SESSION['usuario_id'];
        $detallesAuditoria = "Usuario editado: ID " . $idUsuario . ", Nuevo usuario: " . $usuario . ", Rol: " . $rol . ", Correo: " . $correo;
        registrarAuditoria($conn, $idAdminUsuario, 'Edición de Usuario', $detallesAuditoria);


        http_response_code(200); // OK
        echo json_encode(['mensaje' => 'Usuario actualizado exitosamente.']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Error al actualizar el usuario. Inténtalo de nuevo.']);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Petición no válida.']);
}

$conn = null;
?>