<?php
require '../conexion.php'; // Incluye el archivo de conexión a la base de datos

// Función para obtener todos los usuarios
function obtenerUsuarios($conn) {
    $sql = "SELECT idUsuario, NUsuario, Nombre, ApellidoP, ApellidoM, Correo, TipoUsuario FROM usuarios";
    try {
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener usuarios: " . $e->getMessage());
        return false;
    }
}

// Función para agregar un nuevo usuario
function agregarUsuario(
    $conn,
    $nusuario,
    $password,
    $nombre,
    $apellidoP,
    $apellidoM,
    $correo,
    $tipoUsuario
) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash de la contraseña
    $sql = "INSERT INTO usuarios (NUsuario, Contraseña, Nombre, ApellidoP, ApellidoM, Correo, TipoUsuario, CURP) VALUES (?, ?, ?, ?, ?, ?, ?, '')"; // Se agrega CURP vacío inicialmente
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$nusuario, $hashedPassword, $nombre, $apellidoP, $apellidoM, $correo, $tipoUsuario]);
    } catch (PDOException $e) {
        error_log("Error al agregar usuario: " . $e->getMessage());
        return false;
    }
}


// Función para obtener un usuario por ID
function obtenerUsuarioPorId($conn, $idUsuario) {
    $sql = "SELECT idUsuario, NUsuario, Nombre, ApellidoP, ApellidoM, Correo, TipoUsuario FROM usuarios WHERE idUsuario = ?";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener usuario por ID: " . $e->getMessage());
        return false;
    }
}

// Función para actualizar un usuario (PLACEHOLDER - Implementar lógica)
function actualizarUsuario($conn, $idUsuario, $nusuario, $nombre, $apellidoP, $apellidoM, $correo, $tipoUsuario) {
    // TODO: Implementar lógica de actualización de usuario en la base de datos
    error_log("Función actualizarUsuario NO IMPLEMENTADA en usuarios.php");
    return false; // Placeholder por ahora
}

// Función para eliminar un usuario (PLACEHOLDER - Implementar lógica)
function eliminarUsuario($conn, $idUsuario) {
    // TODO: Implementar lógica de eliminación de usuario en la base de datos
    error_log("Función eliminarUsuario NO IMPLEMENTADA en usuarios.php");
    return false; // Placeholder por ahora
}

// Función para resetear la contraseña de un usuario (PLACEHOLDER - Implementar lógica)
function resetearPasswordUsuario($conn, $idUsuario, $nuevaPassword) {
    // TODO: Implementar lógica de reseteo de contraseña de usuario en la base de datos
    error_log("Función resetearPasswordUsuario NO IMPLEMENTADA en usuarios.php");
    return false; // Placeholder por ahora
}

// Función para verificar si un nombre de usuario ya existe
function existeNombreUsuario($conn, $nusuario) {
    $sql = "SELECT COUNT(*) FROM usuarios WHERE NUsuario = ?";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nusuario]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    } catch (PDOException $e) {
        error_log("Error al verificar nombre de usuario existente: " . $e->getMessage());
        return false;
    }
}

?>