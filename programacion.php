<?php
session_start();
include 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validar permisos: Administrador, Gestor y Enlace pueden acceder
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'enlace')) {
    header("Location: login.php");
    exit();
}

// Validar idListadoEvaluados
if (!isset($_GET['idListadoEvaluados'])) {
    die("Error: No se proporcionó un listado de evaluados válido.");
}

$idListadoEvaluados = intval($_GET['idListadoEvaluados']);

// Ruta base para los listados (debería ser la misma que en gestion_listados.php)
define('LISTADOS_PATH', __DIR__ . '/Listados');

// Consultar listado de evaluados
try {
    $stmtListado = $conn->prepare("SELECT * FROM listados_evaluados WHERE idExpediente = :idListadoEvaluados");
    $stmtListado->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
    $stmtListado->execute();
    $listado = $stmtListado->fetch(PDO::FETCH_ASSOC);

    if (!$listado) {
        die("Error: Listado de evaluados no encontrado.");
    }

    $numeroOficio = $listado['NumeroOficio'];
    $carpetaListado = LISTADOS_PATH . '/' . $numeroOficio; // Ruta de la carpeta del listado
    if (!is_dir($carpetaListado)) {
        // Esto debería haber sido creado en gestion_listados.php, pero por si acaso
        mkdir($carpetaListado, 0777, true);
    }
} catch (PDOException $e) {
    die("Error al obtener el listado de evaluados: " . $e->getMessage());
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

// Procesar edición de evaluado (SOLO PARA ADMINISTRADOR Y GESTOR)
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_evaluado']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
    $idSolicitud = intval($_POST['idSolicitud']);
    $estadoDocumentacion = $_POST['estadoDocumentacion'];
    $comentarios = trim($_POST['comentarios']);

    try {
        $stmtActualizar = $conn->prepare("
            UPDATE programacion_evaluados
            SET EstadoDocumentacion = :estadoDocumentacion,
                Comentarios = :comentarios
            WHERE idSolicitud = :idSolicitud
        ");
        $stmtActualizar->bindParam(':estadoDocumentacion', $estadoDocumentacion, PDO::PARAM_STR);
        $stmtActualizar->bindParam(':comentarios', $comentarios, PDO::PARAM_STR);
        $stmtActualizar->bindParam(':idSolicitud', $idSolicitud, PDO::PARAM_INT);
        $stmtActualizar->execute();

        $mensaje = "Evaluado actualizado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar evaluado: " . $e->getMessage();
    }
}

// Procesar eliminación de evaluado (SOLO PARA ADMINISTRADOR Y GESTOR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_evaluado']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
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

// Agregar evaluado (SOLO PARA ADMINISTRADOR Y GESTOR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_evaluado']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor')) {
    $curp = trim($_POST['curp']);
    $nombre = trim($_POST['nombre']);
    $apellidoP = trim($_POST['apellidoP']);
    $apellidoM = trim($_POST['apellidoM']);
    // Se heredan del listado
    $idMotivoEvaluacion = $listado['idMotivoEvaluacion'];
    $idCategoria = $listado['idCategoria'];
    $idMunicipio = $listado['idMunicipio'];
    $idCorporacion = $listado['idCorporacion'];

    try {
        // Validar duplicados
        $stmtVerificar = $conn->prepare("
            SELECT *
            FROM programacion_evaluados
            WHERE CURP = :curp AND idListadoEvaluados = :idListadoEvaluados
        ");
        $stmtVerificar->bindParam(':curp', $curp, PDO::PARAM_STR);
        $stmtVerificar->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
        $stmtVerificar->execute();

        if ($stmtVerificar->rowCount() > 0) {
            $mensaje = "Error: El evaluado con CURP $curp ya está registrado en este listado.";
        } else {
            // Insertar evaluado
            $stmtAgregar = $conn->prepare("
                INSERT INTO programacion_evaluados
                (idListadoEvaluados, Nombre, ApellidoP, ApellidoM, CURP, idMotivoEvaluacion, idCategoria, idMunicipio, idCorporacion, EstadoDocumentacion)
                VALUES (:idListadoEvaluados, :nombre, :apellidoP, :apellidoM, :curp, :idMotivoEvaluacion, :idCategoria, :idMunicipio, :idCorporacion, 'Pendiente')
            ");
            $stmtAgregar->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
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

            // Crear la carpeta del evaluado dentro del listado
            $carpetaEvaluado = $carpetaListado . '/' . $curp;
            if (!is_dir($carpetaEvaluado)) {
                if (mkdir($carpetaEvaluado, 0777, true)) {
                    $mensaje .= " Carpeta del evaluado creada.";
                } else {
                    $mensaje .= " Error al crear la carpeta del evaluado.";
                }
            } else {
                $mensaje .= " La carpeta del evaluado ya existía.";
            }
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
        WHERE e.idListadoEvaluados = :idListadoEvaluados
    ");
    $stmtEvaluados->bindParam(':idListadoEvaluados', $idListadoEvaluados, PDO::PARAM_INT);
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
         <h1 class="mb-4 text-center">Programación de Evaluados - Listado No. <?php echo htmlspecialchars($numeroOficio); ?></h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
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
            <input type="hidden" name="motivoEvaluacion" value="<?php echo htmlspecialchars($listado['idMotivoEvaluacion']); ?>">
            <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($listado['idCategoria']); ?>">
            <input type="hidden" name="municipio" value="<?php echo htmlspecialchars($listado['idMunicipio']); ?>">
            <input type="hidden" name="corporacion" value="<?php echo htmlspecialchars($listado['idCorporacion']); ?>">

            <div class="col-md-12 text-center mt-4">
                <button type="submit" name="agregar_evaluado" class="btn btn-primary">Agregar Evaluado</button>
            </div>
        </form>
        <?php endif; ?>


        <div class="mt-5">
        <h3 class="mt-5">Evaluados del Listado</h3>
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
    <div class="d-flex flex-column gap-1">
        <?php if($_SESSION['rol'] === 'enlace'): ?>
            <a href="carga_documentos.php?idListadoEvaluados=<?php echo urlencode($idListadoEvaluados); ?>&curp=<?php echo urlencode($evaluado['CURP']); ?>&tipoEvaluacion=<?php echo urlencode($evaluado['idMotivoEvaluacion']); ?>"
               class="btn btn-primary btn-sm w-100 mb-1">Cargar Documentos</a>
        <?php endif; ?>

        <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
            <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
                <button class="btn btn-info btn-sm w-100 mb-1" data-bs-toggle="modal" data-bs-target="#editarModal<?php echo $evaluado['idSolicitud']; ?>">Editar</button>
                <form method="POST" class="d-inline w-100 mb-1">
                    <input type="hidden" name="idSolicitud" value="<?php echo $evaluado['idSolicitud']; ?>">
                    <button type="submit" name="eliminar_evaluado" class="btn btn-danger btn-sm w-100" onclick="return confirm('¿Está seguro de eliminar este evaluado?')">Eliminar</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($_SESSION['rol'] === 'administrador'): ?>
             <a href="carga_documentos.php?idListadoEvaluados=<?php echo urlencode($idListadoEvaluados); ?>&curp=<?php echo urlencode($evaluado['CURP']); ?>&tipoEvaluacion=<?php echo urlencode($evaluado['idMotivoEvaluacion']); ?>"
               class="btn btn-warning btn-sm w-100">Cargar Documentos</a>
        <?php endif; ?>
    </div>
</td>
                            </tr>

                            <?php if($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'gestor'): ?>
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
                            <?php endif; ?>


                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay evaluados registrados en este listado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      <?php include 'footer.php'; ?>
</body>

</html>