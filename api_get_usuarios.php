<?php
include 'conexion.php';

$expedienteId = $_GET['expedienteId'];
$stmt = $conn->prepare("SELECT CURP, CONCAT(Nombre, ' ', ApellidoP, ' ', ApellidoM) AS NombreCompleto FROM programacion_evaluados WHERE idExpediente = ?");
$stmt->execute([$expedienteId]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
