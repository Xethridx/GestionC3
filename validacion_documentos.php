<?php
session_start();
// Verificar si el usuario ha iniciado sesión y tiene el rol permitido (administrador o gestor)
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'gestor'])) {
    header("Location: login.php");
    exit();
}

// Incluir conexión a la base de datos
include 'conexion.php';

// Obtener el listado de documentos para validar
try {
    $sql = "SELECT de.idDocumento, de.NombreArchivo, de.EstadoRevision, e.FolioExpediente, e.Comentarios 
            FROM documentos_expediente de
            JOIN expedientes e ON de.idElemento = e.idExpediente
            ORDER BY de.FechaCarga DESC";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los documentos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Documentos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <!-- Navbar -->
<?php include 'navbar.php'; ?>


    <!-- Main Content -->
    <div class="container my-5">
        <h3>Documentos para Validar</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Número de Expediente</th>
                        <th>Documento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result): ?>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?php echo $row['idDocumento']; ?></td>
                                <td><?php echo $row['FolioExpediente']; ?></td>
                                <td><a href="<?php echo $row['NombreArchivo']; ?>" target="_blank"><?php echo $row['NombreArchivo']; ?></a></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['EstadoRevision'] === 'Validado' ? 'success' : 'danger'; ?>">
                                        <?php echo $row['EstadoRevision']; ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="actualizar_documento.php" method="POST" class="d-inline">
                                        <input type="hidden" name="documentoId" value="<?php echo $row['idDocumento']; ?>">
                                        <input type="hidden" name="estado" value="Validado">
                                        <button type="submit" class="btn btn-success btn-sm">Validar</button>
                                    </form>
                                    <form action="actualizar_documento.php" method="POST" class="d-inline">
                                        <input type="hidden" name="documentoId" value="<?php echo $row['idDocumento']; ?>">
                                        <input type="hidden" name="estado" value="Observaciones">
                                        <button type="submit" class="btn btn-danger btn-sm">Observación</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay documentos pendientes de validación.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
