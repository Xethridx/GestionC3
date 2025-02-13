<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validar permisos
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'gestor')) {
    header("Location: login.php");
    exit();
}

// Validar idExpediente
if (!isset($_GET['idExpediente'])) {
    die("Error: No se proporcionó un expediente válido.");
}

$idExpediente = intval($_GET['idExpediente']);

// Consultar expediente
try {
    $stmtExpediente = $conn->prepare("SELECT * FROM expedientes WHERE idExpediente = :idExpediente");
    $stmtExpediente->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
    $stmtExpediente->execute();
    $expediente = $stmtExpediente->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        die("Error: Expediente no encontrado.");
    }

    $folioExpediente = $expediente['FolioExpediente'];
    $carpetaExpediente = __DIR__ . "/Expedientes/$folioExpediente";

    if (!file_exists($carpetaExpediente)) {
        mkdir($carpetaExpediente, 0777, true);
    }
} catch (PDOException $e) {
    die("Error al obtener el expediente: " . $e->getMessage());
}

// Cargar catálogos
try {
    $catalogoMotivos = $conn->query("SELECT idMotivo, Motivo FROM motivos_evaluacion")->fetchAll(PDO::FETCH_ASSOC);
    $catalogoCategorias = $conn->query("SELECT idCategoria, Categoria FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
    $catalogoMunicipios = $conn->query("SELECT idMunicipio, Municipio FROM municipios")->fetchAll(PDO::FETCH_ASSOC);
    $catalogoCorporaciones = $conn->query("SELECT idCorporacion, NombreCorporacion FROM corporaciones")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar catálogos: " . $e->getMessage());
}

// Procesar edición de evaluado
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_evaluado'])) {
    $idSolicitud = intval($_POST['idSolicitud']);
    $idMotivoEvaluacion = intval($_POST['motivoEvaluacion']);
    $idCategoria = intval($_POST['categoria']);
    $idMunicipio = intval($_POST['municipio']);
    $estadoDocumentacion = $_POST['estadoDocumentacion'];
    $comentarios = trim($_POST['comentarios']);

    try {
        $stmtActualizar = $conn->prepare("
            UPDATE programacion_evaluados
            SET idMotivoEvaluacion = :idMotivoEvaluacion,
                idCategoria = :idCategoria,
                idMunicipio = :idMunicipio,
                EstadoDocumentacion = :estadoDocumentacion,
                Comentarios = :comentarios
            WHERE idSolicitud = :idSolicitud
        ");
        $stmtActualizar->bindParam(':idMotivoEvaluacion', $idMotivoEvaluacion, PDO::PARAM_INT);
        $stmtActualizar->bindParam(':idCategoria', $idCategoria, PDO::PARAM_INT);
        $stmtActualizar->bindParam(':idMunicipio', $idMunicipio, PDO::PARAM_INT);
        $stmtActualizar->bindParam(':estadoDocumentacion', $estadoDocumentacion, PDO::PARAM_STR);
        $stmtActualizar->bindParam(':comentarios', $comentarios, PDO::PARAM_STR);
        $stmtActualizar->bindParam(':idSolicitud', $idSolicitud, PDO::PARAM_INT);
        $stmtActualizar->execute();

        $mensaje = "Evaluado actualizado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar evaluado: " . $e->getMessage();
    }
}

// Procesar eliminación de evaluado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_evaluado'])) {
    $idSolicitud = intval($_POST['idSolicitud']);
    try {
        $stmtEliminar = $conn->prepare("DELETE FROM programacion_evaluados WHERE idSolicitud = :idSolicitud");
        $stmtEliminar->bindParam(':idSolicitud', $idSolicitud, PDO::PARAM_INT);
        $stmtEliminar->execute();

        $mensaje = "Evaluado eliminado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar evaluado: " . $e->getMessage();
    }
}

// Agregar evaluado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_evaluado'])) {
    $curp = trim($_POST['curp']);
    $nombre = trim($_POST['nombre']);
    $apellidoP = trim($_POST['apellidoP']);
    $apellidoM = trim($_POST['apellidoM']);
    $idMotivoEvaluacion = intval($_POST['motivoEvaluacion']);
    $idCategoria = intval($_POST['categoria']);
    $idMunicipio = intval($_POST['municipio']);
    $idCorporacion = intval($_POST['corporacion']);

    try {
        // Validar duplicados
        $stmtVerificar = $conn->prepare("
            SELECT * 
            FROM programacion_evaluados 
            WHERE CURP = :curp AND idExpediente = :idExpediente
        ");
        $stmtVerificar->bindParam(':curp', $curp, PDO::PARAM_STR);
        $stmtVerificar->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
        $stmtVerificar->execute();

        if ($stmtVerificar->rowCount() > 0) {
            $mensaje = "Error: El evaluado con CURP $curp ya está registrado en este expediente.";
        } else {
            // Insertar evaluado
            $stmtAgregar = $conn->prepare("
                INSERT INTO programacion_evaluados 
                (idExpediente, Nombre, ApellidoP, ApellidoM, CURP, idMotivoEvaluacion, idCategoria, idMunicipio, idCorporacion, EstadoDocumentacion) 
                VALUES (:idExpediente, :nombre, :apellidoP, :apellidoM, :curp, :idMotivoEvaluacion, :idCategoria, :idMunicipio, :idCorporacion, 'Pendiente')
            ");
            $stmtAgregar->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
            $stmtAgregar->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmtAgregar->bindParam(':apellidoP', $apellidoP, PDO::PARAM_STR);
            $stmtAgregar->bindParam(':apellidoM', $apellidoM, PDO::PARAM_STR);
            $stmtAgregar->bindParam(':curp', $curp, PDO::PARAM_STR);
            $stmtAgregar->bindParam(':idMotivoEvaluacion', $idMotivoEvaluacion, PDO::PARAM_INT);
            $stmtAgregar->bindParam(':idCategoria', $idCategoria, PDO::PARAM_INT);
            $stmtAgregar->bindParam(':idMunicipio', $idMunicipio, PDO::PARAM_INT);
            $stmtAgregar->bindParam(':idCorporacion', $idCorporacion, PDO::PARAM_INT);
            $stmtAgregar->execute();

            $mensaje = "Evaluado agregado correctamente.";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al agregar evaluado: " . $e->getMessage();
    }
}

// Consultar evaluados
try {
    $stmtEvaluados = $conn->prepare("
        SELECT e.*, m.Motivo, cat.Categoria, mun.Municipio, cor.NombreCorporacion
        FROM programacion_evaluados e
        JOIN motivos_evaluacion m ON e.idMotivoEvaluacion = m.idMotivo
        JOIN categorias cat ON e.idCategoria = cat.idCategoria
        JOIN municipios mun ON e.idMunicipio = mun.idMunicipio
        JOIN corporaciones cor ON e.idCorporacion = cor.idCorporacion
        WHERE e.idExpediente = :idExpediente
    ");
    $stmtEvaluados->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
    $stmtEvaluados->execute();
    $evaluados = $stmtEvaluados->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar evaluados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programación de Evaluados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
         <h1 class="mb-4 text-center">Programación de Evaluados - Expediente <?php echo htmlspecialchars($folioExpediente); ?></h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <h3 class="mb-4">Nuevo Evaluado</h3>
        <form method="POST" class="row g-3">
            <div class="col-md-3">
                <label for="curp" class="form-label">CURP</label>
                <input type="text" class="form-control" id="curp" name="curp" required>
            </div>
            <div class="col-md-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="col-md-3">
                <label for="apellidoP" class="form-label">Apellido Paterno</label>
                <input type="text" class="form-control" id="apellidoP" name="apellidoP" required>
            </div>
            <div class="col-md-3">
                <label for="apellidoM" class="form-label">Apellido Materno</label>
                <input type="text" class="form-control" id="apellidoM" name="apellidoM">
            </div>
            <div class="col-md-3">
                <label for="motivoEvaluacion" class="form-label">Motivo de Evaluación</label>
                <select class="form-control" id="motivoEvaluacion" name="motivoEvaluacion" required>
                    <?php foreach ($catalogoMotivos as $motivo): ?>
                        <option value="<?php echo htmlspecialchars($motivo['idMotivo']); ?>"><?php echo htmlspecialchars($motivo['Motivo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="categoria" class="form-label">Categoría</label>
                <select class="form-control" id="categoria" name="categoria" required>
                    <?php foreach ($catalogoCategorias as $categoria): ?>
                        <option value="<?php echo htmlspecialchars($categoria['idCategoria']); ?>"><?php echo htmlspecialchars($categoria['Categoria']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="municipio" class="form-label">Municipio</label>
                <select class="form-control" id="municipio" name="municipio" required>
                    <?php foreach ($catalogoMunicipios as $municipio): ?>
                        <option value="<?php echo htmlspecialchars($municipio['idMunicipio']); ?>"><?php echo htmlspecialchars($municipio['Municipio']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="corporacion" class="form-label">Corporación</label>
                <select class="form-control" id="corporacion" name="corporacion" required>
                    <?php foreach ($catalogoCorporaciones as $corporacion): ?>
                        <option value="<?php echo htmlspecialchars($corporacion['idCorporacion']); ?>"><?php echo htmlspecialchars($corporacion['NombreCorporacion']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 text-center mt-4">
                <button type="submit" name="agregar_evaluado" class="btn btn-primary">Agregar Evaluado</button>
            </div>
        </form>

        <div class="mt-5">
        <h3 class="mt-5">Evaluados del Expediente</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>CURP</th>
                        <th>Nombre</th>
                        <th>Motivo</th>
                        <th>Categoría</th>
                        <th>Municipio</th>
                        <th>Corporación</th>
                        <th>Estado Documentación</th>
                        <th>Comentarios</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($evaluados)): ?>
                        <?php foreach ($evaluados as $evaluado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluado['CURP']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['Nombre'] . ' ' . $evaluado['ApellidoP'] . ' ' . $evaluado['ApellidoM']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['Motivo']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['Categoria']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['Municipio']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['NombreCorporacion']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['EstadoDocumentacion']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['Comentarios'] ?? ''); ?></td>
                                <td>
                                    <!-- Botón Cargar Documentos -->
                                    <a href="carga_documentos.php?expediente=<?php echo urlencode($idExpediente); ?>&curp=<?php echo urlencode($evaluado['CURP']); ?>&tipoEvaluacion=<?php echo urlencode($evaluado['idMotivoEvaluacion']); ?>" 
                                       class="btn btn-primary btn-sm">Cargar Documentos</a>
                                    <!-- Botón Editar -->
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $evaluado['idSolicitud']; ?>">Editar</button>
                                    <!-- Botón Eliminar -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="idSolicitud" value="<?php echo $evaluado['idSolicitud']; ?>">
                                        <button type="submit" name="eliminar_evaluado" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este evaluado?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>

  <!-- Modal para Editar -->
<div class="modal fade" id="editarModal<?php echo $evaluado['idSolicitud']; ?>" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <input type="hidden" name="idSolicitud" value="<?php echo $evaluado['idSolicitud']; ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarModalLabel">Editar Evaluado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="motivoEvaluacion" class="form-label">Motivo de Evaluación</label>
                        <select class="form-control" name="motivoEvaluacion" required>
                            <?php foreach ($catalogoMotivos as $motivo): ?>
                                <option value="<?php echo $motivo['idMotivo']; ?>" <?php echo $evaluado['idMotivoEvaluacion'] == $motivo['idMotivo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($motivo['Motivo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select class="form-control" name="categoria" required>
                            <?php foreach ($catalogoCategorias as $categoria): ?>
                                <option value="<?php echo $categoria['idCategoria']; ?>" <?php echo $evaluado['idCategoria'] == $categoria['idCategoria'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['Categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="municipio" class="form-label">Municipio</label>
                        <select class="form-control" name="municipio" required>
                            <?php foreach ($catalogoMunicipios as $municipio): ?>
                                <option value="<?php echo $municipio['idMunicipio']; ?>" <?php echo $evaluado['idMunicipio'] == $municipio['idMunicipio'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($municipio['Municipio']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="estadoDocumentacion" class="form-label">Estado Documentación</label>
                        <select class="form-control" name="estadoDocumentacion" required>
                            <option value="Completo" <?php echo $evaluado['EstadoDocumentacion'] == 'Completo' ? 'selected' : ''; ?>>Completo</option>
                            <option value="Pendiente" <?php echo $evaluado['EstadoDocumentacion'] == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Observaciones" <?php echo $evaluado['EstadoDocumentacion'] == 'Observaciones' ? 'selected' : ''; ?>>Observaciones</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comentarios" class="form-label">Comentarios</label>
                        <textarea class="form-control" name="comentarios" rows="3"><?php echo htmlspecialchars($evaluado['Comentarios'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar_evaluado" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay evaluados registrados en este expediente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      <?php include 'footer.php'; ?>
</body>
 
</html>
