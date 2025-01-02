<?php
session_start();
include 'conexion.php';

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
    $catalogoCorporaciones = $conn->query("SELECT idCorporacion, NombreCorporacion FROM corporaciones")->fetchAll(PDO::FETCH_ASSOC);
    $catalogoMotivos = $conn->query("SELECT idMotivo, Motivo FROM motivos_evaluacion")->fetchAll(PDO::FETCH_ASSOC);
    $catalogoCategorias = $conn->query("SELECT idCategoria, Categoria FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
    $catalogoMunicipios = $conn->query("SELECT idMunicipio, Municipio FROM municipios")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar catálogos: " . $e->getMessage());
}

// Agregar evaluado
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_evaluado'])) {
    $curp = trim($_POST['curp']);
    $nombre = trim($_POST['nombre']);
    $apellidoP = trim($_POST['apellidoP']);
    $apellidoM = trim($_POST['apellidoM']);
    $motivoEvaluacion = intval($_POST['motivoEvaluacion']);
    $corporacion = intval($_POST['corporacion']);
    $categoria = intval($_POST['categoria']);
    $municipio = intval($_POST['municipio']);

    try {
        $stmtAgregar = $conn->prepare("
            INSERT INTO programacion_evaluados 
            (idExpediente, Nombre, ApellidoP, ApellidoM, CURP, MotivoEvaluacion, idCorporacion, Categoria, Municipio) 
            VALUES (:idExpediente, :nombre, :apellidoP, :apellidoM, :curp, :motivoEvaluacion, :corporacion, :categoria, :municipio)
        ");
        $stmtAgregar->bindParam(':idExpediente', $idExpediente, PDO::PARAM_INT);
        $stmtAgregar->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtAgregar->bindParam(':apellidoP', $apellidoP, PDO::PARAM_STR);
        $stmtAgregar->bindParam(':apellidoM', $apellidoM, PDO::PARAM_STR);
        $stmtAgregar->bindParam(':curp', $curp, PDO::PARAM_STR);
        $stmtAgregar->bindParam(':motivoEvaluacion', $motivoEvaluacion, PDO::PARAM_INT);
        $stmtAgregar->bindParam(':corporacion', $corporacion, PDO::PARAM_INT);
        $stmtAgregar->bindParam(':categoria', $categoria, PDO::PARAM_INT);
        $stmtAgregar->bindParam(':municipio', $municipio, PDO::PARAM_INT);
        $stmtAgregar->execute();

        $carpetaElemento = "$carpetaExpediente/$curp";
        if (!file_exists($carpetaElemento)) {
            mkdir($carpetaElemento, 0777, true);
        }

        $mensaje = "Evaluado agregado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al agregar evaluado: " . $e->getMessage();
    }
}

// Consultar evaluados
try {
    $stmtEvaluados = $conn->prepare("SELECT * FROM programacion_evaluados WHERE idExpediente = :idExpediente");
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
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4">Programación de Evaluados - Expediente <?php echo htmlspecialchars($folioExpediente); ?></h1>

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
                <label for="corporacion" class="form-label">Corporación</label>
                <select class="form-control" id="corporacion" name="corporacion" required>
                    <?php foreach ($catalogoCorporaciones as $corporacion): ?>
                        <option value="<?php echo htmlspecialchars($corporacion['idCorporacion']); ?>"><?php echo htmlspecialchars($corporacion['NombreCorporacion']); ?></option>
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
            <div class="col-md-12 text-center mt-4">
                <button type="submit" name="agregar_evaluado" class="btn btn-primary">Agregar Evaluado</button>
            </div>
        </form>

        <div class="mt-5">
            <h3>Evaluados del Expediente</h3>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>CURP</th>
                        <th>Nombre</th>
                        <th>Motivo</th>
                        <th>Corporación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($evaluados)): ?>
                        <?php foreach ($evaluados as $evaluado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluado['CURP']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['Nombre'] . ' ' . $evaluado['ApellidoP'] . ' ' . $evaluado['ApellidoM']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['MotivoEvaluacion']); ?></td>
                                <td><?php echo htmlspecialchars($evaluado['idCorporacion']); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm">Editar</button>
                                    <button class="btn btn-danger btn-sm">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay evaluados registrados en este expediente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
        <!-- Footer -->
    <?php include 'footer.php'; ?>
</body>
</html>
