<?php
include 'conexion.php';

$expedienteId = isset($_GET['expedienteId']) ? intval($_GET['expedienteId']) : 0;

if (!$expedienteId) {
    echo "<div class='alert alert-danger'>Error: Expediente ID no válido.</div>";
    exit;
}

try {
    $stmtUsuarios = $conn->prepare("
        SELECT e.CURP, CONCAT(e.Nombre, ' ', e.ApellidoP, ' ', e.ApellidoM) AS NombreCompleto
        FROM programacion_evaluados e
        INNER JOIN documentos_expediente de ON e.idSolicitud = de.idElemento
        WHERE de.idExpediente = :expedienteId
        GROUP BY e.CURP, e.Nombre, e.ApellidoP, e.ApellidoM
        ORDER BY e.ApellidoP, e.ApellidoM, e.Nombre
    ");
    $stmtUsuarios->bindParam(':expedienteId', $expedienteId, PDO::PARAM_INT);
    $stmtUsuarios->execute();
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al obtener usuarios: " . $e->getMessage() . "</div>";
    exit;
}
?>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>CURP</th>
                <th>Nombre Completo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($usuarios)): ?>
                <tr><td colspan="4" class="text-center">No hay usuarios asignados a este expediente.</td></tr>
            <?php else: ?>
                <?php foreach ($usuarios as $index => $usuario): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($usuario['CURP']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['NombreCompleto']); ?></td>
                        <td>
                            <a href="ver_documentos.php?curp=<?php echo htmlspecialchars($usuario['CURP']); ?>&expedienteId=<?php echo $expedienteId; ?>" class="btn btn-info btn-sm">Ver Documentos</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>