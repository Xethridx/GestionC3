<?php
// Configuración de conexión a la base de datos
$host = "sql204.infinityfree.com";
$username = "if0_37893197";
$password = "zp6Awd2mofCBV4X";
$dbname = "if0_37893197_gestionc3";

try {
    // Crear conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configurar PDO para manejar errores como excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
?>
