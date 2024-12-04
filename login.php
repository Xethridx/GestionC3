<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consultar el usuario en la base de datos
    $query = "SELECT * FROM usuarios WHERE NUsuario = ? AND Contraseña = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $usuario, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['usuario'] = $row['NUsuario'];
        $_SESSION['nombre'] = $row['Nombre'];
        $_SESSION['tipo_usuario'] = $row['tipo_usuario']; // Guardar el rol del usuario

        // Redirigir según el tipo de usuario
        switch ($row['tipo_usuario']) {
            case 'administrador':
                header("Location: admin_dashboard.php");
                break;
            case 'gestor':
                header("Location: gestor_dashboard.php");
                break;
            case 'enlace':
                header("Location: enlace_dashboard.php");
                break;
            case 'administrativo':
                header("Location: administrativo_dashboard.php");
                break;
            default:
                header("Location: index.php");
        }
    } else {
        // Credenciales incorrectas
        $_SESSION['error'] = "Usuario o contraseña incorrectos.";
        header("Location: index.php");
    }
    $stmt->close();
    $conn->close();
}
?>
