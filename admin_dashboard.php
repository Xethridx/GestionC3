<?php
include 'auth.php';

// Solo administradores pueden acceder a este módulo
verificarRol(['administrador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
</head>
<body>
    <h1>Bienvenido, <?php echo $_SESSION['nombre']; ?> (Administrador)</h1>
    <p>Aquí puedes gestionar todos los módulos del sistema.</p>
</body>
</html>
