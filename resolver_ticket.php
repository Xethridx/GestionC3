<?php
session_start();

// Verificar si el usuario tiene el rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Incluir conexión a la base de datos
include 'conexion.php';

// Verificar que el ID del ticket esté presente en la solicitud
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Asegurar que el ID sea un entero

    try {
        // Actualizar el estado del ticket a "resuelto"
        $query = "UPDATE tickets SET estado = 'resuelto' WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redirigir a la página de gestión de tickets con éxito
            header("Location: tickets_soporte.php?mensaje=Ticket+marcado+como+resuelto");
            exit();
        } else {
            // Si la ejecución falla
            header("Location: tickets_soporte.php?error=No+se+pudo+actualizar+el+ticket");
            exit();
        }
    } catch (PDOException $e) {
        // Manejo de errores en la base de datos
        header("Location: tickets_soporte.php?error=Error+en+la+base+de+datos");
        exit();
    }
} else {
    // Si no se proporciona un ID válido
    header("Location: tickets_soporte.php?error=ID+de+ticket+no+proporcionado");
    exit();
}
?>
