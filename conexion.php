<?php
// Configuración de conexión a la base de datos
$host = 'localhost'; // Dirección del servidor de la base de datos
$dbname = 'GestionDocumental'; // Nombre de la base de datos
$username = 'root'; // Usuario de la base de datos
$password = ''; // Contraseña del usuario

try {
    // Crear una nueva conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configuración de atributos para manejo de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Manejo de errores de conexión
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
