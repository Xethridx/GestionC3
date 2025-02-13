<?php
session_start();
include 'conexion.php';

// Verificar permisos
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'gestor'])) {
    header("Location: login.php");
    exit();
}

// Obtener Expedientes
try {
    $stmtExpedientes = $conn->query("SELECT idExpediente, FolioExpediente, FechaCreacion FROM expedientes ORDER BY FechaCreacion DESC");
    $expedientes = $stmtExpedientes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener expedientes: " . $e->getMessage());
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

        <!-- Tabla de Expedientes -->
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
                    <?php foreach ($expedientes as $index => $expediente): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($expediente['FolioExpediente']); ?></td>
                            <td><?php echo htmlspecialchars($expediente['FechaCreacion']); ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm load-users" 
                                        data-expediente-id="<?php echo $expediente['idExpediente']; ?>" 
                                        data-folio-expediente="<?php echo htmlspecialchars($expediente['FolioExpediente']); ?>">
                                    Ver Usuarios
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="mt-5" id="usuarios-container" style="display: none;">
            <h3>Usuarios del Expediente: <span id="folio-actual"></span></h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>CURP</th>
                            <th>Nombre Completo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usuarios-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.load-users').forEach(button => {
            button.addEventListener('click', function () {
                const expedienteId = this.dataset.expedienteId;
                const folioExpediente = this.dataset.folioExpediente;

                fetch(`api_get_usuarios.php?expedienteId=${expedienteId}`)
                    .then(response => response.json())
                    .then(users => {
                        const tbody = document.getElementById('usuarios-tbody');
                        tbody.innerHTML = '';
                        users.forEach(user => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${user.CURP}</td>
                                    <td>${user.NombreCompleto}</td>
                                    <td>
                                        <a href="ver_documentos.php?curp=${user.CURP}&expedienteId=${expedienteId}" class="btn btn-info btn-sm">Ver Documentos</a>
                                    </td>
                                </tr>
                            `;
                        });

                        document.getElementById('folio-actual').textContent = folioExpediente;
                        document.getElementById('usuarios-container').style.display = 'block';
                    });
            });
        });
    </script>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
