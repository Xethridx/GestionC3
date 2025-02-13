<?php
require '../conexion.php'; // Incluye el archivo de conexión

// Función para registrar un evento de auditoría
function registrarAuditoria(
    $conn,
    $idUsuario,
    $accion,
    $detalles = null
) {
    $sql = "INSERT INTO auditoria_usuarios (idUsuario, Accion, Detalles) VALUES (?, ?, ?)";
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$idUsuario, $accion, $detalles]);
    } catch (PDOException $e) {
        error_log("Error al registrar auditoría: " . $e->getMessage());
        return false;
    }
}

// Función para obtener los registros de auditoría
function obtenerRegistrosAuditoria($conn) {
    $sql = "SELECT au.idAuditoria, u.NUsuario, au.Accion, au.FechaHora, au.Detalles
            FROM auditoria_usuarios au
            INNER JOIN usuarios u ON au.idUsuario = u.idUsuario
            ORDER BY au.FechaHora DESC"; // Ordenar por fecha y hora descendente
    try {
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener registros de auditoría: " . $e->getMessage());
        return false;
    }
}
?>