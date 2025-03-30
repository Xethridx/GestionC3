<?php
session_start();
include 'conexion.php';
require 'auth.php';

// Verificar si el usuario ha iniciado sesión y su rol es válido (gestor, enlace o administrador)
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'enlace' && $_SESSION['rol'] !== 'administrador')) {
    header("Location: login.php");
    exit();
}

// Mensajes de éxito o error
$mensaje = "";

// Ruta base para guardar los expedientes
define('BASE_PATH', __DIR__ . '/Expedientes');

// Crear la carpeta base si no existe
if (!file_exists(BASE_PATH)) {
    mkdir(BASE_PATH, 0777, true);
}

// Crear un nuevo expediente (Permitido para Administrador y Gestor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_expediente']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
    $folio_expediente = trim($_POST['numero_expediente']);
    $comentarios = trim($_POST['comentarios']);

    if (empty($folio_expediente)) {
        $mensaje = "Error: El número de expediente no puede estar vacío.";
    } else {
        try {
            // Verificar duplicados
            $stmtVerificar = $conn->prepare("SELECT * FROM expedientes WHERE FolioExpediente = :FolioExpediente");
            $stmtVerificar->bindParam(':FolioExpediente', $folio_expediente, PDO::PARAM_STR);
            $stmtVerificar->execute();

            if ($stmtVerificar->rowCount() > 0) {
                $mensaje = "Error: Ya existe un expediente con el número $folio_expediente.";
            } else {
                // Insertar el expediente
                $stmt = $conn->prepare("INSERT INTO expedientes (FolioExpediente, Estado, Comentarios)
                                        VALUES (:FolioExpediente, 'Abierto', :Comentarios)");
                $stmt->bindParam(':FolioExpediente', $folio_expediente, PDO::PARAM_STR);
                $stmt->bindParam(':Comentarios', $comentarios, PDO::PARAM_STR);
                $stmt->execute();

                // Crear la carpeta para el expediente
                $expediente_path = BASE_PATH . '/' . $folio_expediente;
                if (!file_exists($expediente_path)) {
                    mkdir($expediente_path, 0777, true);
                }

                $mensaje = "Expediente creado correctamente.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear expediente: " . $e->getMessage();
        }
    }
}

// Eliminar un expediente (Permitido para Administrador y Gestor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_expediente']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
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

// Editar un expediente (Permitido para Administrador y Gestor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_expediente']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
    $expediente_id = intval($_POST['expediente_id']);
    $estado = $_POST['estado'];
    $comentarios = trim($_POST['comentarios']);

    try {
        $stmt = $conn->prepare("UPDATE expedientes SET Estado = :Estado, Comentarios = :Comentarios WHERE idExpediente = :id");
        $stmt->bindParam(':Estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':Comentarios', $comentarios, PDO::PARAM_STR);
        $stmt->bindParam(':id', $expediente_id, PDO::PARAM_INT);
        $stmt->execute();

        $mensaje = "Expediente actualizado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar expediente: " . $e->getMessage();
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
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <h1 class="mb-4 text-center">Gestión de Expedientes</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
        <form method="POST" class="row g-3 mb-5">
            <div class="col-md-6">
                <label for="numero_expediente" class="form-label">Número de Expediente</label>
                <input type="text" class="form-control" id="numero_expediente" name="numero_expediente" placeholder="Ej. EX12345" required>
            </div>
            <div class="col-md-6">
                <label for="comentarios" class="form-label">Comentarios</label>
                <input type="text" class="form-control" id="comentarios" name="comentarios" placeholder="Comentarios opcionales">
            </div>
            <div class="col-md-12 text-center">
                <button type="submit" name="crear_expediente" class="btn btn-primary">Crear Expediente</button>
            </div>
        </form>
        <?php endif; ?>

        <h2 class="mb-4 text-center">Listado de Expedientes</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Folio</th>
                        <th>Estado</th>
                        <th>Comentarios</th>
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
                                <td><?php echo htmlspecialchars($expediente['Comentarios']); ?></td>
                                <td><?php echo htmlspecialchars($expediente['FechaCreacion']); ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                    <a href="programacion.php?idExpediente=<?php echo $expediente['idExpediente']; ?>" class="btn btn-info btn-sm w-100 mb-1">Ver Programación</a>
                                    <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
                                        <form method="POST" class="d-inline w-100 mb-1">
                                            <input type="hidden" name="expediente_id" value="<?php echo $expediente['idExpediente']; ?>">
                                            <select name="estado" class="form-select form-select-sm d-inline w-auto">
                                                <option value="Abierto" <?php echo $expediente['Estado'] === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                                                <option value="En Revisión" <?php echo $expediente['Estado'] === 'En Revisión' ? 'selected' : ''; ?>>En Revisión</option>
                                                <option value="Cerrado" <?php echo $expediente['Estado'] === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                                            </select>
                                            <input type="text" name="comentarios" class="form-control form-control-sm d-inline w-auto" value="<?php echo htmlspecialchars($expediente['Comentarios']); ?>">
                                            <button type="submit" name="editar_expediente" class="btn btn-success btn-sm">Actualizar Estado</button>
                                        </form>
                                        <form method="POST" class="d-inline w-100">
                                            <input type="hidden" name="expediente_id" value="<?php echo $expediente['idExpediente']; ?>">
                                            <button type="submit" name="eliminar_expediente" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este expediente?')">Eliminar Expediente</button>
                                        </form>
                                    <?php endif; ?>
                                    </div>
                                        <?php if($_SESSION['rol'] === 'enlace'): ?>
                                            <span class="text-muted">Solo Visualización</span>
                                        <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay expedientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <?php include 'footer.php'; ?>
</body>
</html>