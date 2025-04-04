<?php
include 'conexion.php';

try {
    // Intentar una consulta simple
    $sql = "SELECT 1";
    $stmt = $conn->query($sql);

    echo "<h1>Conexión exitosa a la base de datos</h1>";
    echo "<p>La base de datos está operativa y la conexión fue exitosa.</p>";
} catch (PDOException $e) {
    echo "<h1>Error en la conexión</h1>";
    echo "<p>Detalles: " . $e->getMessage() . "</p>";
}
?>
