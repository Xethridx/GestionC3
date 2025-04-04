<?php
include 'conexion.php';

$listadoId = isset($_GET['listadoId']) ? intval($_GET['listadoId']) : 0;

if (!$listadoId) {
    echo "<div class='alert alert-danger'>Error: Listado ID no válido.</div>";
    exit;
}

try {
    $stmtUsuarios = $conn->prepare("
        SELECT e.CURP, CONCAT(e.Nombre, ' ', e.ApellidoP, ' ', e.ApellidoM) AS NombreCompleto
        FROM programacion_evaluados e
        WHERE e.idListadoEvaluados = :listadoId
        ORDER BY e.ApellidoP, e.ApellidoM, e.Nombre
    ");
    $stmtUsuarios->bindParam(':listadoId', $listadoId, PDO::PARAM_INT);
    $stmtUsuarios->execute();
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al obtener evaluados: " . $e->getMessage() . "</div>";
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
                <tr><td colspan="4" class="text-center">No hay evaluados asignados a este listado.</td></tr>
            <?php else: ?>
                <?php foreach ($usuarios as $index => $usuario): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($usuario['CURP']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['NombreCompleto']); ?></td>
                        <td>
                            <a href="ver_documentos.php?curp=<?php echo htmlspecialchars($usuario['CURP']); ?>&listadoId=<?php echo $listadoId; ?>" class="btn btn-info btn-sm">Ver Documentos</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>