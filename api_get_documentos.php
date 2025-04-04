<?php
include 'conexion.php';

$curp = $_GET['curp'];
$expediente = $_GET['expediente'];

// Consultar documentos del usuario específico
$stmt = $conn->prepare("
    SELECT nombre_documento, 
           CONCAT('/Expedientes/', :expediente, '/', :curp, '/', REPLACE(nombre_documento, ' ', '_'), '.pdf') AS ruta_completa
    FROM documentos 
    WHERE elemento_id IN (
        SELECT id FROM elementos WHERE curp = :curp
    )
");
$stmt->execute([':curp' => $curp, ':expediente' => $expediente]);

// Devolver resultados en formato JSON
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
