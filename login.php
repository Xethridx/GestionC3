<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $query = "SELECT * FROM usuarios WHERE NUsuario = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['Contraseña'])) {
        $_SESSION['usuario'] = $row['NUsuario'];
        $_SESSION['nombre'] = $row['Nombre'];
        $_SESSION['tipo_usuario'] = $row['TipoUsuario'];

        switch ($row['TipoUsuario']) {
            case 'administrador':
                header("Location: panel_administrador.php");
                break;
            case 'gestor':
                header("Location: panel_gestor.php");
                break;
            case 'enlace':
                header("Location: panel_enlace.php");
                break;
            default:
                $_SESSION['error'] = "Tipo de usuario no válido.";
                header("Location: index.php");
        }
    } else {
        $_SESSION['error'] = "Usuario o contraseña incorrectos.";
        header("Location: index.php");
    }
}
