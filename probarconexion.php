<?php
// Configuración de la base de datos
$host = "localhost";       // Cambiar por el host de tu base de datos
$username = "root";        // Cambiar por tu usuario de la base de datos
$password = "";            // Cambiar por tu contraseña de la base de datos
$dbname = "GestionDocumental"; // Cambiar por el nombre de tu base de datos

// Intentar la conexión
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("<h1 style='color: red; text-align: center;'>Error: No se pudo conectar a la base de datos. <br>" . $conn->connect_error . "</h1>");
} else {
    echo "<h1 style='color: green; text-align: center;'>Conexión exitosa a la base de datos.</h1>";
}

// Cerrar la conexión (opcional para pruebas simples)
$conn->close();
?>
GestionDocumental