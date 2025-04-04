<?php
require 'usuarios.php';
require '../auth.php';
require '../conexion.php';
require 'auditoria.php'; // Auditoría para registrar reseteos


// Verificar rol (administrador o gestor, o define roles permitidos para resetear passwords)
if (!verificarRol(['administrador', 'gestor'])) { // Ajusta roles permitidos
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado. No tienes permisos para resetear contraseñas.']);
    exit;
}

$conn = conectarDB();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar a la base de datos.']);
    exit;
}

// Recibir ID de usuario a resetear contraseña (por POST en JSON)
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $data) {
    $idUsuario = isset($data['idUsuario']) ? intval($data['idUsuario']) : 0; // Asegurar que sea entero

    if ($idUsuario <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'ID de usuario inválido.']);
        exit;
    }

    // Generar nueva contraseña aleatoria
    $nuevaPassword = bin2hex(random_bytes(8)); // 16 caracteres hexadecimales (8 bytes)

    // TODO: Implementar lógica para resetear la contraseña en la base de datos
    $password_reseteado = resetearPasswordUsuario($conn, $idUsuario, $nuevaPassword); // Llamar a función en usuarios.php


    if ($password_reseteado) {
        // Auditoría: Registrar reseteo de contraseña
        $usuarioAdmin = $_SESSION['usuario']; // Usuario admin que realiza la acción
        $idAdminUsuario =  $_SESSION['usuario_id'];
        $detallesAuditoria = "Contraseña reseteada para usuario ID " . $idUsuario . ". Nueva contraseña generada.";
        registrarAuditoria($conn, $idAdminUsuario, 'Reseteo de Contraseña', $detallesAuditoria);


        http_response_code(200); // OK
        echo json_encode(['mensaje' => 'Contraseña reseteada exitosamente.', 'password_temporal' => $nuevaPassword]); // Devolver nueva pass temporal (¡considerar seguridad!)

        // TODO:  Implementar envío de correo electrónico al usuario con la nueva contraseña temporal (¡Sistema real!)

    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Error al resetear la contraseña. Inténtalo de nuevo.']);
    }

} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Petición no válida.']);
}

$conn = null;
?>