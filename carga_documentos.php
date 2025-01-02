<?php
session_start();
include 'conexion.php';

// Validar permisos
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'gestor')) {
    header("Location: login.php");
    exit();
}

// Validar parámetros GET
if (!isset($_GET['expediente']) || !isset($_GET['curp']) || !isset($_GET['tipoEvaluacion'])) {
    die("Error: Parámetros insuficientes para cargar documentos.");
}

$folioExpediente = htmlspecialchars($_GET['expediente']);
$curp = htmlspecialchars($_GET['curp']);
$tipoEvaluacion = htmlspecialchars($_GET['tipoEvaluacion']);

// Crear carpeta del evaluado si no existe
$carpetaEvaluado = __DIR__ . "/Expedientes/$folioExpediente/$curp";
if (!file_exists($carpetaEvaluado)) {
    mkdir($carpetaEvaluado, 0777, true);
}

// Definir documentos requeridos
$documentosRequeridos = [];
if ($tipoEvaluacion === 'Nuevo Ingreso') {
    $documentosRequeridos = [
        "Formato 'Historia de vida firmado.'",
        "Acta de nacimiento formato nuevo",
        "Cartilla liberada del Servicio Militar Nacional u oficio vigente de exceptuación",
        "Comprobante de estudios de acuerdo al perfil de puesto, por ambos lados",
        "Credencial de elector completa",
        "Acta de Matrimonio o divorcio",
        "Constancia de situación fiscal actualizada",
        "Declaración patrimonial en caso de que realice",
        "CURP actual",
        "Solicitud de empleo",
        "Comprobante de domicilio reciente",
        "Curriculum vitae debidamente estructurado y firmado",
        "Tres últimos comprobantes de ingresos económicos",
        "Documentos que avalen la posesión de bienes",
        "Oficio de baja de alguna institución de seguridad pública",
        "Resolución de averiguación previa o proceso penal",
        "Buro de crédito",
        "Círculo de crédito"
    ];
} elseif ($tipoEvaluacion === 'Permanencia/Promoción') {
    $documentosRequeridos = [
        "Formato 'Historia de vida firmado.'",
        "Acta de nacimiento formato nuevo",
        "Cartilla liberada del Servicio Militar Nacional u oficio vigente de exceptuación",
        "Comprobante de estudios de acuerdo al perfil de puesto, por ambos lados",
        "Credencial de elector completa",
        "Acta de Matrimonio o divorcio",
        "Constancia de situación fiscal actualizada",
        "Declaración patrimonial en caso de que realice",
        "CURP actual",
        "CUIP",
        "Comprobante de domicilio reciente",
        "Curriculum vitae debidamente estructurado y firmado",
        "Tres últimos comprobantes de ingresos económicos",
        "Documentos que avalen la posesión de bienes",
        "Oficio de baja de alguna institución de seguridad pública",
        "Resolución de averiguación previa o proceso penal",
        "Buro de crédito",
        "Círculo de crédito",
        "Historia laboral",
        "Tres últimos estados de cuenta",
        "Constancia de contraloría interna",
        "Expediente Institucional"
    ];
}

// Manejar la carga de documentos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_FILES['documentos']['name'] as $index => $nombreDocumento) {
        if ($_FILES['documentos']['error'][$index] === UPLOAD_ERR_OK) {
            $nombreRenombrado = $curp . '_' . str_replace(' ', '', $documentosRequeridos[$index]) . '.pdf';
            $rutaDestino = "$carpetaEvaluado/$nombreRenombrado";

            if (move_uploaded_file($_FILES['documentos']['tmp_name'][$index], $rutaDestino)) {
                echo "<div class='alert alert-success'>Documento '{$documentosRequeridos[$index]}' subido exitosamente.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error al subir el documento '{$documentosRequeridos[$index]}'.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4">Cargar Documentos - <?php echo $tipoEvaluacion; ?></h1>
        <p><strong>Expediente:</strong> <?php echo $folioExpediente; ?></p>
        <p><strong>CURP:</strong> <?php echo $curp; ?></p>

        <!-- Formulario para carga de documentos -->
        <form action="" method="POST" enctype="multipart/form-data">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Documento</th>
                        <th>Cargar PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentosRequeridos as $index => $documento): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($documento); ?></td>
                            <td>
                                <input type="file" name="documentos[]" accept="application/pdf" class="form-control" required>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Subir Documentos</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
