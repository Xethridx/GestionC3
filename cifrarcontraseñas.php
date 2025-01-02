<?php
include 'conexion.php';

try {
    // Seleccionar usuarios con contraseñas MD5
    $sql = "SELECT idUsuario, Contraseña FROM usuarios";
    $stmt = $conn->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario) {
        $hashedPassword = password_hash($usuario['Contraseña'], PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $sqlUpdate = "UPDATE usuarios SET Contraseña = ? WHERE idUsuario = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([$hashedPassword, $usuario['idUsuario']]);
    }

    echo "Contraseñas migradas con éxito.";
} catch (PDOException $e) {
    die("Error al migrar contraseñas: " . $e->getMessage());
}
?>
