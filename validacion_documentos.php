<?php
session_start();
include 'conexion.php';

// Verificar permisos: Solo administrador y coordinador
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'coordinacion'])) {
    header("Location: login.php");
    exit();
}

$mensaje_error_listados = "";
$listados = [];

try {
    $stmtListados = $conn->prepare("
        SELECT
            le.idExpediente,
            le.NumeroOficio,
            le.FechaCreacion,
            c.NombreCorporacion AS Corporacion,
            m.Municipio
        FROM listados_evaluados le
        LEFT JOIN programacion_evaluados pe ON le.idExpediente = pe.idListadoEvaluados
        LEFT JOIN corporaciones c ON pe.idCorporacion = c.idCorporacion
        LEFT JOIN municipios m ON pe.idMunicipio = m.idMunicipio
        GROUP BY le.idExpediente
        ORDER BY le.FechaCreacion DESC
    ");
    $stmtListados->execute();
    $listados = $stmtListados->fetchAll(PDO::FETCH_ASSOC);
    if (empty($listados)) {
        $mensaje_error_listados = "No se encontraron listados de evaluados registrados.";
    }
} catch (PDOException $e) {
    $mensaje_error_listados = "Error al obtener listados de evaluados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Validación de Documentos</h1>

        <?php if (!empty($mensaje_error_listados)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensaje_error_listados; ?>
            </div>
        <?php endif; ?>

        <h3 class="mb-3">Listado de Evaluados</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Número de Oficio</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                        <th>Corporación</th>
                        <th>Municipio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listados) && empty($mensaje_error_listados)): ?>
                        <tr><td colspan="6" class="text-center">No hay listados de evaluados disponibles.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listados as $index => $listado): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($listado['NumeroOficio']); ?></td>
                                <td><?php echo htmlspecialchars($listado['FechaCreacion']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm btn-usuarios-modal"
                                            data-listado-id="<?php echo $listado['idExpediente']; ?>"
                                            data-numero-oficio="<?php echo htmlspecialchars($listado['NumeroOficio']); ?>">
                                        Ver Evaluados
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($listado['Corporacion'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($listado['Municipio'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="usuariosModal" tabindex="-1" aria-labelledby="usuariosModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="usuariosModalLabel">Evaluados del Listado: <span id="modal-folio-expediente"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-usuarios-body">
                        </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-usuarios-modal').forEach(button => {
            button.addEventListener('click', function () {
                const listadoId = this.dataset.listadoId;
                const numeroOficio = this.dataset.numeroOficio;
                const modalBody = document.getElementById('modal-usuarios-body');
                const modalFolio = document.getElementById('modal-folio-expediente');

                modalFolio.textContent = numeroOficio;
                modalBody.innerHTML = '<div class="text-center">Cargando evaluados... <div class="spinner-border spinner-border-sm" role="status"></div></div>'; // Mensaje de carga

                fetch(`modal_usuarios.php?listadoId=${listadoId}`)
                    .then(response => response.text())
                    .then(html => {
                        modalBody.innerHTML = html; // Insertar la tabla de usuarios en el modal
                        const usuariosModal = new bootstrap.Modal(document.getElementById('usuariosModal'));
                        usuariosModal.show(); // Mostrar el modal
                    })
                    .catch(error => {
                        modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar evaluados. Por favor, intenta nuevamente.</div>'; // Mensaje de error en el modal
                        console.error('Error al cargar evaluados:', error);
                    });
            });
        });
    </script>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>