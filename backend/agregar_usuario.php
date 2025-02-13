<?php
require 'usuarios.php';     // Incluye funciones de usuario
require 'auditoria.php';    // Incluye funciones de auditoría
require '../conexion.php';    // Incluye conexión a la base de datos
require '../auth.php';       // Incluye autenticación

// Verificar que el usuario sea administrador
if (!verificarRol(['administrador'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso denegado. No tienes permisos para agregar usuarios.']);
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
    $nombre = isset($data['nombre']) ? trim(htmlspecialchars($data['nombre'])) : ''; // Sanitizar entrada
    $apellidoP = isset($data['apellidoP']) ? trim(htmlspecialchars($data['apellidoP'])) : ''; // Sanitizar entrada
    $apellidoM = isset($data['apellidoM']) ? trim(htmlspecialchars($data['apellidoM'])) : ''; // Sanitizar entrada
    $usuario = isset($data['usuario']) ? trim(htmlspecialchars($data['usuario'])) : ''; // Sanitizar entrada
    $correo = isset($data['correo']) ? trim(htmlspecialchars($data['correo'])) : '';     // Sanitizar entrada
    $rol = isset($data['rol']) ? trim(htmlspecialchars($data['rol'])) : '';         // Sanitizar entrada


    // Validaciones en el servidor (¡IMPORTANTE!)
    if (empty($nombre) || empty($apellidoP) || empty($usuario) || empty($correo) || empty($rol)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Todos los campos (Nombre, Apellido Paterno, Usuario, Correo, Rol) son obligatorios.']);
        exit;
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo electrónico no válido.']);
        exit;
    }
    if (existeNombreUsuario($conn, $usuario)) {
        http_response_code(409); // Conflict - Recurso ya existe
        echo json_encode(['error' => 'El nombre de usuario ya existe.']);
        exit;
    }


    // Generar una contraseña aleatoria segura
    $password = bin2hex(random_bytes(8)); // 16 caracteres hexadecimales (8 bytes)


    // Intentar agregar el usuario
    if (agregarUsuario($conn, $usuario, $password, $nombre, $apellidoP, $apellidoM, $correo, $rol)) {
        // Auditoría: Registrar la creación del usuario
        $usuarioAdmin = $_SESSION['usuario']; // Nombre de usuario del administrador que realiza la acción
        $idAdminUsuario =  $_SESSION['usuario_id']; // ID del admin
        $detallesAuditoria = "Usuario creado: " . $usuario . ", Rol: " . $rol . ", Correo: " . $correo;
        registrarAuditoria($conn, $idAdminUsuario, 'Creación de Usuario', $detallesAuditoria);


        http_response_code(201); // Created
        echo json_encode(['mensaje' => 'Usuario agregado exitosamente.', 'password_temporal' => $password]); // Devuelve pass temporal

        // TODO:  Implementar envío de correo electrónico al nuevo usuario con la contraseña temporal (¡Sistema real!)

    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Error al agregar el usuario. Inténtalo de nuevo.']);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Petición no válida.']);
}

$conn = null; // Cerrar la conexión
?>