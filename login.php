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
            $_SESSION['id_usuario'] = $user['idUsuario'];
            $_SESSION['usuario'] = $user['NUsuario'];
            $_SESSION['rol'] = $user['TipoUsuario'];

            // *** NUEVO - REGISTRO DE LOG ***
            try {
                $accion_log = "Inicio de sesión"; // Define la acción a registrar
                $stmt_log = $conn->prepare("
                    INSERT INTO logs_accesos (usuario, accion)
                    VALUES (:usuario, :accion)
                ");
                $stmt_log->bindParam(':usuario', $usuario, PDO::PARAM_STR);
                $stmt_log->bindParam(':accion', $accion_log, PDO::PARAM_STR);
                $stmt_log->execute();
            } catch (PDOException $e_log) {
                // Si falla el registro del log, puedes registrar el error en los logs de PHP del servidor
                error_log("Error al registrar log de acceso en login.php: " . $e_log->getMessage());
                // **Opcional:**  Puedes mostrar un mensaje de advertencia al administrador, pero NO interrumpas el login principal.
                // echo "<div class='alert alert-warning'>Advertencia: Error al registrar log de acceso.</div>";
            }
            // *** FIN - REGISTRO DE LOG ***


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