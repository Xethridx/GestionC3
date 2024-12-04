<?php
session_start();
require 'conexion.php';

// Verificar si el usuario tiene permisos de acceso
if (!isset($_SESSION['TipoUsuario']) || $_SESSION['TipoUsuario'] !== 'Administrador') {
    header("Location: index.php");
    exit;
}

// Procesar la carga de documentos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $evaluado_id = $_POST['evaluado_id'];
    $tipo_documento = $_POST['tipo_documento'];
    $documento = $_FILES['documento'];

    // Validar que el archivo sea un PDF
    if ($documento['type'] === 'application/pdf') {
        $ruta_carpeta = "documentos/$evaluado_id";
        if (!is_dir($ruta_carpeta)) {
            mkdir($ruta_carpeta, 0777, true);
        }

        $ruta_archivo = "$ruta_carpeta/$tipo_documento.pdf";
        if (move_uploaded_file($documento['tmp_name'], $ruta_archivo)) {
            $stmt = $conexion->prepare("INSERT INTO documentos (evaluado_id, tipo_documento, ruta_archivo) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE ruta_archivo = ?");
            $stmt->bind_param("isss", $evaluado_id, $tipo_documento, $ruta_archivo, $ruta_archivo);
            $stmt->execute();
            $mensaje = "Documento cargado exitosamente.";
        } else {
            $error = "Error al subir el documento.";
        }
    } else {
        $error = "Solo se permiten archivos en formato PDF.";
    }
}

// Obtener la lista de evaluados
$evaluados = $conexion->query("SELECT * FROM evaluados");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Carga de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <div class="container my-5">
        <h1>Módulo de Carga de Documentos</h1>

        <!-- Mensajes -->
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Tabla de evaluados -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Número de Solicitud</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>CURP</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($evaluado = $evaluados->fetch_assoc()): ?>
                    <tr>
                        <td><?= $evaluado['numero_solicitud'] ?></td>
                        <td><?= $evaluado['nombre'] ?></td>
                        <td><?= $evaluado['apellidos'] ?></td>
                        <td><?= $evaluado['curp'] ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCargar" data-id="<?= $evaluado['id'] ?>">Cargar Documentos</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para cargar documentos -->
    <div class="modal fade" id="modalCargar" tabindex="-1" aria-labelledby="modalCargarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCargarLabel">Cargar Documento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="evaluado_id" id="evaluado_id">
                        <div class="mb-3">
                            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                            <select class="form-control" name="tipo_documento" id="tipo_documento" required>
                                <option value="historia_de_vida">Formato "Historia de vida firmado"</option>
                                <option value="acta_nacimiento">Acta de nacimiento formato nuevo</option>
                                <option value="cartilla_militar">Cartilla liberada del SMN</option>
                                <option value="comprobante_estudios">Comprobante de estudios</option>
                                <option value="credencial_elector">Credencial de elector completa</option>
                                <option value="acta_matrimonio">Acta de matrimonio o divorcio</option>
                                <option value="constancia_fiscal">Constancia de situación fiscal actualizada</option>
                                <option value="declaracion_patrimonial">Declaración patrimonial</option>
                                <option value="curp_actual">CURP actual</option>
                                <option value="solicitud_empleo">Solicitud de empleo</option>
                                <option value="comprobante_domicilio">Comprobante de domicilio reciente</option>
                                <option value="curriculum_vitae">Currículum vitae firmado</option>
                                <option value="comprobantes_ingresos">Tres últimos comprobantes de ingresos</option>
                                <option value="documentos_bienes">Documentos que avalen bienes</option>
                                <option value="oficio_baja">Oficio de baja de instituciones</option>
                                <option value="averiguaciones">Resoluciones de averiguaciones previas</option>
                                <option value="buro_credito">Buró de crédito</option>
                                <option value="circulo_credito">Círculo de crédito</option>
                                <!-- Añadir más opciones según necesidad -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="documento" class="form-label">Documento (PDF)</label>
                            <input type="file" class="form-control" name="documento" id="documento" accept="application/pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Subir</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-bs-target="#modalCargar"]').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('evaluado_id').value = button.getAttribute('data-id');
            });
        });
    </script>
</body>
</html>
