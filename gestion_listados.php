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


// Ruta base para los listados
define('LISTADOS_PATH', __DIR__ . '/Listados');

// Crear la carpeta base de Listados si no existe
if (!file_exists(LISTADOS_PATH)) {
    mkdir(LISTADOS_PATH, 0777, true);
}

// Obtener listas para los dropdowns
try {
    $stmt_motivos = $conn->query("SELECT idMotivo, Motivo FROM motivos_evaluacion ORDER BY Motivo");
    $motivos = $stmt_motivos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_corporaciones = $conn->query("SELECT idCorporacion, NombreCorporacion FROM corporaciones ORDER BY NombreCorporacion");
    $corporaciones = $stmt_corporaciones->fetchAll(PDO::FETCH_ASSOC);

    $stmt_categorias = $conn->query("SELECT idCategoria, Categoria FROM categorias ORDER BY Categoria");
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

    $stmt_municipios = $conn->query("SELECT idMunicipio, Municipio FROM municipios ORDER BY Municipio");
    $municipios = $stmt_municipios->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar catálogos: " . $e->getMessage());
}

// Crear un nuevo listado de evaluados (Permitido para Administrador y Gestor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_listado']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
    $numero_oficio = trim($_POST['numero_oficio']);
    $id_motivo = intval($_POST['motivo_evaluacion']);
    $id_corporacion = intval($_POST['corporacion']);
    $id_categoria = intval($_POST['categoria']);
    $id_municipio = intval($_POST['municipio']);
    $comentarios = trim($_POST['comentarios']);

    if (empty($numero_oficio)) {
        $mensaje = "Error: El número de oficio no puede estar vacío.";
    } else {
        try {
            // Verificar duplicados por número de oficio (podrías ajustar esto según tus necesidades)
            $stmtVerificar = $conn->prepare("SELECT * FROM listados_evaluados WHERE NumeroOficio = :NumeroOficio");
            $stmtVerificar->bindParam(':NumeroOficio', $numero_oficio, PDO::PARAM_STR);
            $stmtVerificar->execute();

            if ($stmtVerificar->rowCount() > 0) {
                $mensaje = "Error: Ya existe un listado con el número de oficio $numero_oficio.";
            } else {
// Insertar el listado
$stmt = $conn->prepare("INSERT INTO listados_evaluados (NumeroOficio, idMotivoEvaluacion, idCorporacion, idCategoria, idMunicipio, Estado, Comentarios)
VALUES (:NumeroOficio, :idMotivoEvaluacion, :idCorporacion, :idCategoria, :idMunicipio, 'Abierto', :Comentarios)");
$stmt->bindParam(':NumeroOficio', $numero_oficio, PDO::PARAM_STR);
$stmt->bindParam(':idMotivoEvaluacion', $id_motivo, PDO::PARAM_INT);
$stmt->bindParam(':idCorporacion', $id_corporacion, PDO::PARAM_INT);
$stmt->bindParam(':idCategoria', $id_categoria, PDO::PARAM_INT);
$stmt->bindParam(':idMunicipio', $id_municipio, PDO::PARAM_INT);
$stmt->bindParam(':Comentarios', $comentarios, PDO::PARAM_STR);
$stmt->execute();

$mensaje = "Listado de evaluados creado correctamente.";

// Crear la carpeta del listado
$numeroOficio_path =  LISTADOS_PATH . '/' . $numero_oficio;
$rutaCarpetaListado = $numeroOficio_path; // Corrección aquí
if (!is_dir($rutaCarpetaListado)) {
if (mkdir($rutaCarpetaListado, 0777, true)) {
$mensaje .= " Carpeta del listado creada.";
} else {
$mensaje .= " Error al crear la carpeta del listado.";
}
} else {
$mensaje .= " La carpeta del listado ya existía.";
}
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear el listado: " . $e->getMessage();
        }
    }
}

// Eliminar un listado de evaluados (Permitido para Administrador y Gestor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_listado']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
    $listado_id = intval($_POST['listado_id']);

    try {
        // Obtener el número de oficio antes de eliminar el listado
        $stmt_select_oficio = $conn->prepare("SELECT NumeroOficio FROM listados_evaluados WHERE idExpediente = :id");
        $stmt_select_oficio->bindParam(':id', $listado_id, PDO::PARAM_INT);
        $stmt_select_oficio->execute();
        $listado_info = $stmt_select_oficio->fetch(PDO::FETCH_ASSOC);

        $stmt_delete = $conn->prepare("DELETE FROM listados_evaluados WHERE idExpediente = :id");
        $stmt_delete->bindParam(':id', $listado_id, PDO::PARAM_INT);
        $stmt_delete->execute();

        if ($listado_info) {
            $numeroOficioSanitizado = preg_replace('/[^a-zA-Z0-9_.-]/', '', $listado_info['NumeroOficio']);
            $rutaCarpetaListado = LISTADOS_PATH . "/" . $numeroOficioSanitizado;
            if (is_dir($rutaCarpetaListado)) {
                // Función para eliminar directorios recursivamente
                function eliminarDirRecursivo($dir) {
                    $files = array_diff(scandir($dir), array('.','..'));
                    foreach ($files as $file) {
                        (is_dir("$dir/$file")) ? eliminarDirRecursivo("$dir/$file") : unlink("$dir/$file");
                    }
                    return rmdir($dir);
                }
                eliminarDirRecursivo($rutaCarpetaListado);
                $mensaje .= " Carpeta del listado eliminada.";
            }
        }

        $mensaje = "Listado de evaluados eliminado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el listado: " . $e->getMessage();
    }
}

// Editar un listado de evaluados (Permitido para Administrador y Gestor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_listado']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
    $listado_id = intval($_POST['listado_id']);
    $estado = $_POST['estado'];
    $comentarios = trim($_POST['comentarios']);

    try {
        $stmt = $conn->prepare("UPDATE listados_evaluados SET Estado = :Estado, Comentarios = :Comentarios WHERE idExpediente = :id");
        $stmt->bindParam(':Estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':Comentarios', $comentarios, PDO::PARAM_STR);
        $stmt->bindParam(':id', $listado_id, PDO::PARAM_INT);
        $stmt->execute();

        $mensaje = "Listado de evaluados actualizado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar el listado: " . $e->getMessage();
    }
}

// Consultar todos los listados de evaluados
try {
    $stmt = $conn->query("
        SELECT
            le.idExpediente,
            le.NumeroOficio,
            m.Motivo AS MotivoEvaluacion,
            c.NombreCorporacion AS Corporacion,
            cat.Categoria,
            mun.Municipio,
            le.Estado,
            le.Comentarios,
            le.FechaCreacion
        FROM listados_evaluados le
        JOIN motivos_evaluacion m ON le.idMotivoEvaluacion = m.idMotivo
        JOIN corporaciones c ON le.idCorporacion = c.idCorporacion
        JOIN categorias cat ON le.idCategoria = cat.idCategoria
        JOIN municipios mun ON le.idMunicipio = mun.idMunicipio
        ORDER BY le.FechaCreacion DESC
    ");
    $listados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar listados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Listados de Evaluados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container my-5">
        <h1 class="mb-4 text-center">Gestión de Listados de Evaluados</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
        <form method="POST" class="row g-3 mb-5">
            <div class="col-md-6">
                <label for="numero_oficio" class="form-label">Número de Oficio</label>
                <input type="text" class="form-control" id="numero_oficio" name="numero_oficio" placeholder="Ej. OF2025-01" required>
            </div>
            <div class="col-md-6">
                <label for="motivo_evaluacion" class="form-label">Motivo de Evaluación</label>
                <select class="form-select" id="motivo_evaluacion" name="motivo_evaluacion" required>
                    <option value="">Seleccionar Motivo</option>
                    <?php foreach ($motivos as $motivo): ?>
                        <option value="<?php echo $motivo['idMotivo']; ?>"><?php echo htmlspecialchars($motivo['Motivo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="corporacion" class="form-label">Corporación</label>
                <select class="form-select" id="corporacion" name="corporacion" required>
                    <option value="">Seleccionar Corporación</option>
                    <?php foreach ($corporaciones as $corporacion): ?>
                        <option value="<?php echo $corporacion['idCorporacion']; ?>"><?php echo htmlspecialchars($corporacion['NombreCorporacion']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <option value="">Seleccionar Categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['idCategoria']; ?>"><?php echo htmlspecialchars($categoria['Categoria']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="municipio" class="form-label">Municipio</label>
                <select class="form-select" id="municipio" name="municipio" required>
                    <option value="">Seleccionar Municipio</option>
                    <?php foreach ($municipios as $municipio): ?>
                        <option value="<?php echo $municipio['idMunicipio']; ?>"><?php echo htmlspecialchars($municipio['Municipio']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="comentarios" class="form-label">Comentarios</label>
                <input type="text" class="form-control" id="comentarios" name="comentarios" placeholder="Comentarios opcionales">
            </div>
            <div class="col-md-12 text-center">
                <button type="submit" name="crear_listado" class="btn btn-primary">Crear Listado</button>
            </div>
        </form>
        <?php endif; ?>

        <h2 class="mb-4 text-center">Listado de Listados de Evaluados</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Número de Oficio</th>
                        <th>Motivo</th>
                        <th>Corporación</th>
                        <th>Categoría</th>
                        <th>Municipio</th>
                        <th>Estado</th>
                        <th>Comentarios</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listados)): ?>
                        <?php foreach ($listados as $listado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($listado['idExpediente']); ?></td>
                                <td><?php echo htmlspecialchars($listado['NumeroOficio']); ?></td>
                                <td><?php echo htmlspecialchars($listado['MotivoEvaluacion']); ?></td>
                                <td><?php echo htmlspecialchars($listado['Corporacion']); ?></td>
                                <td><?php echo htmlspecialchars($listado['Categoria']); ?></td>
                                <td><?php echo htmlspecialchars($listado['Municipio']); ?></td>
                                <td><?php echo htmlspecialchars($listado['Estado']); ?></td>
                                <td><?php echo htmlspecialchars($listado['Comentarios']); ?></td>
                                <td><?php echo htmlspecialchars($listado['FechaCreacion']); ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                    <a href="programacion.php?idListadoEvaluados=<?php echo $listado['idExpediente']; ?>" class="btn btn-info btn-sm w-100 mb-1">Ver Programación</a>
                                    <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
                                        <form method="POST" class="d-inline w-100 mb-1">
                                            <input type="hidden" name="listado_id" value="<?php echo $listado['idExpediente']; ?>">
                                            <select name="estado" class="form-select form-select-sm d-inline w-auto">
                                                <option value="Abierto" <?php echo $listado['Estado'] === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                                                <option value="En Revisión" <?php echo $listado['Estado'] === 'En Revisión' ? 'selected' : ''; ?>>En Revisión</option>
                                                <option value="Cerrado" <?php echo $listado['Estado'] === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                                            </select>
                                            <input type="text" name="comentarios" class="form-control form-control-sm d-inline w-auto" value="<?php echo htmlspecialchars($listado['Comentarios']); ?>">
                                            <button type="submit" name="editar_listado" class="btn btn-success btn-sm">Actualizar Estado</button>
                                        </form>
                                        <form method="POST" class="d-inline w-100">
                                            <input type="hidden" name="listado_id" value="<?php echo $listado['idExpediente']; ?>">
                                            <button type="submit" name="eliminar_listado" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este listado?')">Eliminar Listado</button>
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
                            <td colspan="10" class="text-center">No hay listados de evaluados registrados.</td>
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