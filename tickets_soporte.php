<?php
session_start();

// Verificar si el usuario tiene el rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Incluir conexión a la base de datos
include 'conexion.php';

// Obtener tickets de la base de datos
try {
    $query = "SELECT * FROM tickets ORDER BY estado, fecha DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener tickets: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tickets</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h2 class="text-center">Gestión de Tickets</h2>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <table class="table table-striped table-bordered mt-4">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Asunto</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tickets)): ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['asunto']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['descripcion']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $ticket['estado'] === 'pendiente' ? 'warning' : 'success'; ?>">
                                    <?php echo ucfirst($ticket['estado']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['fecha']); ?></td>
                            <td>
                                <?php if ($ticket['estado'] === 'pendiente'): ?>
                                    <a href="resolver_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-success btn-sm">Marcar como Resuelto</a>
                                <?php else: ?>
                                    <span class="badge bg-success">Resuelto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay tickets registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
