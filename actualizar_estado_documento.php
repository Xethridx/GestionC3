<?php
session_start();
include 'conexion.php';

// Verificar permisos: Solo administrador y coordinador
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'coordinacion'])) {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idDocumento = $_POST['idDocumento'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $comentarios = $_POST['comentarios'] ?? '';

    if ($idDocumento !== null && $estado !== null) {
        try {
            $stmt = $conn->prepare("
                UPDATE documentos_expediente
                SET EstadoRevision = :estado, Comentarios = :comentarios
                WHERE idDocumento = :idDocumento
            ");
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':comentarios', $comentarios, PDO::PARAM_STR);
            $stmt->bindParam(':idDocumento', $idDocumento, PDO::PARAM_INT);
            $stmt->execute();

            echo "Estado del documento actualizado correctamente.";

        } catch (PDOException $e) {
            echo "Error al actualizar el estado del documento:" . $e->getMessage();
        }
    } else {
        echo "Error: Datos incompletos para actualizar el estado.";
    }
} else {
    echo "Error: Método no permitido.";
}
?>