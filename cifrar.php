<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

try {
    $query = "SELECT idUsuario, Contraseña FROM usuarios";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $updateQuery = "UPDATE usuarios SET Contraseña = ? WHERE idUsuario = ?";
    $updateStmt = $pdo->prepare($updateQuery);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hashedPassword = password_hash($row['Contraseña'], PASSWORD_BCRYPT);
        $updateStmt->execute([$hashedPassword, $row['idUsuario']]);
    }

    echo "Contraseñas cifradas exitosamente.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
