<?php
session_start();
include 'conexion.php';

// Validar si el usuario está autenticado
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
    die("Error: No se ha encontrado un usuario válido en la sesión.");
}

// Variable para mensajes de éxito o error
$mensaje = "";

// Ruta base para guardar los expedientes
define('BASE_PATH', __DIR__ . '/Expedientes');

// Crear la carpeta base si no existe
if (!file_exists(BASE_PATH)) {
    mkdir(BASE_PATH, 0777, true);
}

// Crear un nuevo expediente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_expediente'])) {
    $folio_expediente = trim($_POST['numero_expediente']);
    $id_usuario = $_SESSION['id_usuario'];
    $id_corporacion = 1; // Puedes ajustar según tu lógica

    if (empty($folio_expediente)) {
        $mensaje = "Error: El número de expediente no puede estar vacío.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO expedientes (FolioExpediente, idUsuario, idCorporacion) 
                                    VALUES (:FolioExpediente, :idUsuario, :idCorporacion)");
            $stmt->bindParam(':FolioExpediente', $folio_expediente, PDO::PARAM_STR);
            $stmt->bindParam(':idUsuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':idCorporacion', $id_corporacion, PDO::PARAM_INT);
            $stmt->execute();

            $expediente_path = BASE_PATH . '/' . $folio_expediente;
            if (!file_exists($expediente_path)) {
                mkdir($expediente_path, 0777, true);
            }

            $mensaje = "Expediente creado correctamente.";
        } catch (PDOException $e) {
            $mensaje = "Error al crear expediente: " . $e->getMessage();
        }
    }
}

// Eliminar un expediente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_expediente'])) {
    $expediente_id = intval($_POST['expediente_id']);

    try {
        $stmt_select = $conn->prepare("SELECT FolioExpediente FROM expedientes WHERE idExpediente = :id");
        $stmt_select->bindParam(':id', $expediente_id, PDO::PARAM_INT);
        $stmt_select->execute();
        $expediente = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($expediente) {
            $folio_expediente = $expediente['FolioExpediente'];

            $stmt_delete = $conn->prepare("DELETE FROM expedientes WHERE idExpediente = :id");
            $stmt_delete->bindParam(':id', $expediente_id, PDO::PARAM_INT);
            $stmt_delete->execute();

            $expediente_path = BASE_PATH . '/' . $folio_expediente;
            if (file_exists($expediente_path)) {
                array_map('unlink', glob("$expediente_path/*.*"));
                rmdir($expediente_path);
            }

            $mensaje = "Expediente eliminado correctamente.";
        } else {
            $mensaje = "Error: Expediente no encontrado.";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar expediente: " . $e->getMessage();
    }
}

// Consultar todos los expedientes
try {
    $stmt = $conn->query("SELECT * FROM expedientes ORDER BY FechaCreacion DESC");
    $expedientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar expedientes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Expedientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4">Gestión de Expedientes</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3 mb-5">
            <div class="col-md-8">
                <label for="numero_expediente" class="form-label">Número de Expediente</label>
                <input type="text" class="form-control" id="numero_expediente" name="numero_expediente" placeholder="Ej. EX12345" required>
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" name="crear_expediente" class="btn btn-primary">Crear Expediente</button>
            </div>
        </form>

        <h2 class="mb-4">Listado de Expedientes</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Folio</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($expedientes)): ?>
                        <?php foreach ($expedientes as $expediente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expediente['idExpediente']); ?></td>
                                <td><?php echo htmlspecialchars($expediente['FolioExpediente']); ?></td>
                                <td><?php echo htmlspecialchars($expediente['Estado']); ?></td>
                                <td><?php echo htmlspecialchars($expediente['FechaCreacion']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="expediente_id" value="<?php echo $expediente['idExpediente']; ?>">
                                        <button type="submit" name="eliminar_expediente" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este expediente?')">Eliminar</button>
                                    </form>
                                    <a href="programacion.php?idExpediente=<?php echo $expediente['idExpediente']; ?>" class="btn btn-info btn-sm">Administrar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay expedientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Footer -->
    <?php include 'footer.php'; ?>
</body>
</html>
