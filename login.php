<?php
session_start();
include 'conexion.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = htmlspecialchars($_POST['usuario']);
    $password = $_POST['password'];

    try {
        // Preparar consulta para obtener el usuario
        $sql = "SELECT idUsuario, NUsuario, Contraseña, TipoUsuario FROM usuarios WHERE NUsuario = :usuario";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar usuario y contraseña
        if ($user && password_verify($password, $user['Contraseña'])) {
            // Guardar información en la sesión
            $_SESSION['id_usuario'] = $user['idUsuario']; // Guardar ID del usuario
            $_SESSION['usuario'] = $user['NUsuario'];     // Guardar nombre de usuario
            $_SESSION['rol'] = $user['TipoUsuario'];      // Guardar rol del usuario

            // Redirigir según el rol
            switch ($user['TipoUsuario']) {
                case 'administrador':
                    header("Location: panel_administrador.php");
                    exit;
                case 'gestor':
                    header("Location: panel_gestor.php");
                    exit;
                case 'enlace':
                    header("Location: panel_enlace.php");
                    exit;
                default:
                    $_SESSION['error'] = "Tipo de usuario no reconocido.";
                    header("Location: index.php");
                    exit;
            }
        } else {
            // Usuario o contraseña inválidos
            $error = "Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        $error = "Error al procesar el inicio de sesión: " . $e->getMessage();
    }
}

// Guardar error en la sesión y redirigir al index
if ($error) {
    $_SESSION['error'] = $error;
}
header("Location: index.php");
exit();
?>
