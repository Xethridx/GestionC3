<?php
// Procesar adición de elementos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_elemento'])) {
    $nombre_completo = htmlspecialchars($_POST['nombre_completo']);
    $curp = htmlspecialchars($_POST['curp']);
    $expediente_id = intval($_GET['expediente_id']);

    include 'conexion.php';

    try {
        $stmt = $conn->prepare("INSERT INTO elementos (expediente_id, nombre_completo, curp) VALUES (:expediente_id, :nombre_completo, :curp)");
        $stmt->bindParam(':expediente_id', $expediente_id);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':curp', $curp);
        $stmt->execute();
        $mensaje = "Elemento añadido correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al añadir elemento: " . $e->getMessage();
    }
}
?>
