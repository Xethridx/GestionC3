<?php
session_start();
require 'conexion.php';
/*
// Verificar si el usuario tiene permisos de acceso
if (!isset($_SESSION['TipoUsuario']) || $_SESSION['TipoUsuario'] !== 'Administrador') {
    header("Location: index.php");
    exit;
}

// Procesar acciones del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        if ($accion === 'agregar') {
            $numero_oficio = $_POST['numero_oficio'];
            $nombre = $_POST['nombre'];
            $apellidos = $_POST['apellidos'];
            $curp = $_POST['curp'];
            $motivo = $_POST['motivo'];
            $corporacion = $_POST['corporacion'];
            $categoria = $_POST['categoria'];
            $municipio = $_POST['municipio'];
            $estado = $_POST['estado'];

            $stmt = $conexion->prepare("INSERT INTO solicitudes (numero_oficio, nombre, apellidos, curp, motivo, corporacion, categoria, municipio, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $numero_oficio, $nombre, $apellidos, $curp, $motivo, $corporacion, $categoria, $municipio, $estado);
            $stmt->execute();
        } elseif ($accion === 'editar') {
            $id = $_POST['id'];
            $numero_oficio = $_POST['numero_oficio'];
            $nombre = $_POST['nombre'];
            $apellidos = $_POST['apellidos'];
            $curp = $_POST['curp'];
            $motivo = $_POST['motivo'];
            $corporacion = $_POST['corporacion'];
            $categoria = $_POST['categoria'];
            $municipio = $_POST['municipio'];
            $estado = $_POST['estado'];

            $stmt = $conexion->prepare("UPDATE solicitudes SET numero_oficio = ?, nombre = ?, apellidos = ?, curp = ?, motivo = ?, corporacion = ?, categoria = ?, municipio = ?, estado = ? WHERE id = ?");
            $stmt->bind_param("sssssssssi", $numero_oficio, $nombre, $apellidos, $curp, $motivo, $corporacion, $categoria, $municipio, $estado, $id);
            $stmt->execute();
        } elseif ($accion === 'eliminar') {
            $id = $_POST['id'];
            $stmt = $conexion->prepare("DELETE FROM solicitudes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
}

// Obtener la lista de solicitudes
$solicitudes = $conexion->query("SELECT * FROM solicitudes");*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Programación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <div class="container my-5">
        <h1>Módulo de Programación</h1>

        <!-- Botón para agregar solicitud -->
        <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#modalAgregar">Agregar Solicitud</button>

        <!-- Tabla de solicitudes -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Número de Oficio</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>CURP</th>
                    <th>Motivo</th>
                    <th>Corporación</th>
                    <th>Categoría</th>
                    <th>Municipio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($solicitud = $solicitudes->fetch_assoc()): ?>
                    <tr>
                        <td><?= $solicitud['numero_oficio'] ?></td>
                        <td><?= $solicitud['nombre'] ?></td>
                        <td><?= $solicitud['apellidos'] ?></td>
                        <td><?= $solicitud['curp'] ?></td>
                        <td><?= $solicitud['motivo'] ?></td>
                        <td><?= $solicitud['corporacion'] ?></td>
                        <td><?= $solicitud['categoria'] ?></td>
                        <td><?= $solicitud['municipio'] ?></td>
                        <td><?= $solicitud['estado'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar" data-id="<?= $solicitud['id'] ?>" data-numero_oficio="<?= $solicitud['numero_oficio'] ?>" data-nombre="<?= $solicitud['nombre'] ?>" data-apellidos="<?= $solicitud['apellidos'] ?>" data-curp="<?= $solicitud['curp'] ?>" data-motivo="<?= $solicitud['motivo'] ?>" data-corporacion="<?= $solicitud['corporacion'] ?>" data-categoria="<?= $solicitud['categoria'] ?>" data-municipio="<?= $solicitud['municipio'] ?>" data-estado="<?= $solicitud['estado'] ?>">Editar</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $solicitud['id'] ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <button class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para agregar solicitud -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="accion" value="agregar">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarLabel">Agregar Solicitud</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="numero_oficio" class="form-label">Número de Oficio</label>
                            <input type="text" class="form-control" name="numero_oficio" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label for="curp" class="form-label">CURP</label>
                            <input type="text" class="form-control" name="curp" required>
                        </div>
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo de Evaluación</label>
                            <input type="text" class="form-control" name="motivo" required>
                        </div>
                        <div class="mb-3">
                            <label for="corporacion" class="form-label">Corporación</label>
                            <input type="text" class="form-control" name="corporacion" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <input type="text" class="form-control" name="categoria" required>
                        </div>
                        <div class="mb-3">
                            <label for="municipio" class="form-label">Municipio</label>
                            <input type="text" class="form-control" name="municipio" required>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-control" name="estado" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Documentación Completa">Documentación Completa</option>
                                <option value="Con Observaciones">Con Observaciones</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
