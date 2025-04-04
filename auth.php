<?php
session_start(); // Inicializar la sesión al principio de auth.php

// Configuración de seguridad para las cookies de sesión
ini_set('session.cookie_httponly', true); // Previene acceso JS a la cookie (protección XSS)
ini_set('session.cookie_secure', true);   // Envía la cookie solo por HTTPS (si estás en HTTPS)
ini_set('session.cookie_samesite', 'Lax'); // Protección contra CSRF (revisar 'Strict' si Lax causa problemas)

// Definir tiempo de vida máximo de la sesión (ejemplo: 1 hora = 3600 segundos)
$session_timeout_seconds = 3600;

// Verificar si la sesión tiene más tiempo del permitido
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > $session_timeout_seconds)) {
    session_unset();     // Limpiar variables de sesión
    session_destroy();   // Destruir la sesión en el servidor
    setcookie(session_name(), '', 0, '/', '', true, true); // Eliminar cookie del cliente
    header("Location: login.php?timeout=1"); // Redirigir a login con mensaje de timeout
    exit();
}

// Actualizar la marca de tiempo del último acceso en cada petición
$_SESSION['ultimo_acceso'] = time();


// Función para regenerar el ID de sesión (después del login exitoso)
function regenerarIdSesion() {
    if (!headers_sent()) { // Verificar que no se hayan enviado headers para evitar error
        session_regenerate_id(true); // true para borrar la sesión antigua
    }
}

// Función para verificar si el usuario ha iniciado sesión
function isLoggedIn() {
    return isset($_SESSION['usuario_id']); // Verifica si existe la variable de sesión 'usuario_id'
}

// Función para verificar el rol del usuario y permitir acceso a ciertas páginas
function verificarRol($roles_permitidos) {
    if (!isLoggedIn()) {
        return false; // No ha iniciado sesión, acceso denegado
    }

    if (!isset($_SESSION['rol'])) {
        return false; // No hay rol definido en la sesión (algo salió mal en el login)
    }

    $rol_usuario = $_SESSION['rol'];

    // Verificar si el rol del usuario está dentro de los roles permitidos
    if (in_array($rol_usuario, $roles_permitidos)) {
        return true; // Rol permitido, acceso concedido
    } else {
        return false; // Rol no permitido, acceso denegado
    }
}

// Función para escapar datos para prevenir XSS (ejemplo básico, usar funciones de escape más robustas según necesidad)
function escaparHtml($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// Función para redirigir si no es administrador (ejemplo de uso de verificarRol en auth.php)
function protegerAdmin() {
    if (!verificarRol(['administrador'])) {
        header("Location: index.php"); // Redirige a página no autorizada o index
        exit();
    }
}

// Ejemplo de otra función de protección para rol 'gestor' (puedes crear más según roles)
function protegerGestor() {
    if (!verificarRol(['administrador', 'gestor'])) { // Permite admin y gestor
        header("Location: index.php"); // Redirige
        exit();
    }
}
?>