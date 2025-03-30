<?php
session_start();
include 'conexion.php';

// Verificar permisos: Solo administrador y coordinador
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'coordinacion'])) {
    header("Location: login.php");
    exit();
}

$mensaje_error_expedientes = "";
$expedientes = [];

try {
    $stmtExpedientes = $conn->query("SELECT idExpediente, FolioExpediente, FechaCreacion FROM expedientes ORDER BY FechaCreacion DESC");
    $expedientes = $stmtExpedientes->fetchAll(PDO::FETCH_ASSOC);
    if (empty($expedientes)) {
        $mensaje_error_expedientes = "No se encontraron expedientes registrados.";
    }
} catch (PDOException $e) {
    $mensaje_error_expedientes = "Error al obtener expedientes: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Revisar Documentos</h1>

        <?php if (!empty($mensaje_error_expedientes)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensaje_error_expedientes; ?>
            </div>
        <?php endif; ?>

        <h3 class="mb-3">Listado de Expedientes</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Folio</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expedientes) && empty($mensaje_error_expedientes)): ?>
                        <tr><td colspan="4" class="text-center">No hay expedientes disponibles.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expedientes as $index => $expediente): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($expediente['FolioExpediente']); ?></td>
                                <td><?php echo htmlspecialchars($expediente['FechaCreacion']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm btn-usuarios-modal"
                                            data-expediente-id="<?php echo $expediente['idExpediente']; ?>"
                                            data-folio-expediente="<?php echo htmlspecialchars($expediente['FolioExpediente']); ?>">
                                        Ver Usuarios
                                    </button>
                                </td>
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
                        <h5 class="modal-title" id="usuariosModalLabel">Usuarios del Expediente: <span id="modal-folio-expediente"></span></h5>
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
                const expedienteId = this.dataset.expedienteId;
                const folioExpediente = this.dataset.folioExpediente;
                const modalBody = document.getElementById('modal-usuarios-body');
                const modalFolio = document.getElementById('modal-folio-expediente');

                modalFolio.textContent = folioExpediente;
                modalBody.innerHTML = '<div class="text-center">Cargando usuarios... <div class="spinner-border spinner-border-sm" role="status"></div></div>'; // Mensaje de carga

                fetch(`modal_usuarios.php?expedienteId=${expedienteId}`)
                    .then(response => response.text())
                    .then(html => {
                        modalBody.innerHTML = html; // Insertar la tabla de usuarios en el modal
                        const usuariosModal = new bootstrap.Modal(document.getElementById('usuariosModal'));
                        usuariosModal.show(); // Mostrar el modal
                    })
                    .catch(error => {
                        modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar usuarios. Por favor, intenta nuevamente.</div>'; // Mensaje de error en el modal
                        console.error('Error al cargar usuarios:', error);
                    });
            });
        });
    </script>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>