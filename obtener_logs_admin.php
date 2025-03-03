<?php
// obtener_logs_admin.php

include 'conexion.php'; // Incluir archivo de conexión

$logsAccesos = []; // Inicializar array para logs

try {
    // Consulta SQL para obtener los 5 logs de acceso más recientes
    $stmtLogs = $conn->prepare("
        SELECT usuario, fecha_hora, accion
        FROM logs_accesos
        ORDER BY fecha_hora DESC
        LIMIT 5 -- Obtener los 5 logs más recientes (ajusta el límite si deseas más o menos)
    ");
    $stmtLogs->execute();
    $logsAccesos = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los logs como JSON
    header('Content-Type: application/json'); // Establecer cabecera para JSON
    echo json_encode($logsAccesos); // Convertir array a JSON y enviarlo

} catch (PDOException $e) {
    // En caso de error, devolver un JSON con mensaje de error
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al cargar logs: ' . $e->getMessage()]);
}
?>