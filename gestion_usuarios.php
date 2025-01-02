<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Nombre del administrador
$nombre_admin = htmlspecialchars($_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
    <!-- Navbar -->
<?php include 'navbar.php'; ?>


    <!-- Hero Section -->
    <div class="bg-light py-4">
        <div class="container text-center">
            <h1 class="fw-bold">Gestión de Usuarios</h1>
            <p class="lead">Administra los usuarios del sistema: alta, baja, permisos y auditoría.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- User Management Tabs -->
        <ul class="nav nav-tabs" id="userManagementTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="listUsers-tab" data-bs-toggle="tab" data-bs-target="#listUsers" type="button" role="tab" aria-controls="listUsers" aria-selected="true">Listar Usuarios</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="addUser-tab" data-bs-toggle="tab" data-bs-target="#addUser" type="button" role="tab" aria-controls="addUser" aria-selected="false">Agregar Usuario</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="auditUsers-tab" data-bs-toggle="tab" data-bs-target="#auditUsers" type="button" role="tab" aria-controls="auditUsers" aria-selected="false">Auditoría</button>
            </li>
        </ul>

        <div class="tab-content" id="userManagementTabsContent">
            <!-- List Users -->
            <div class="tab-pane fade show active" id="listUsers" role="tabpanel" aria-labelledby="listUsers-tab">
                <h3 class="mt-4">Usuarios Registrados</h3>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Correo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se llenarán dinámicamente los usuarios desde el backend -->
                            <tr>
                                <td>Juan Pérez</td>
                                <td>jperez</td>
                                <td>Gestor</td>
                                <td>jperez@correo.com</td>
                                <td>
                                    <button class="btn btn-warning btn-sm">Editar</button>
                                    <button class="btn btn-danger btn-sm">Eliminar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Ana López</td>
                                <td>alopez</td>
                                <td>Enlace</td>
                                <td>alopez@correo.com</td>
                                <td>
                                    <button class="btn btn-warning btn-sm">Editar</button>
                                    <button class="btn btn-danger btn-sm">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add User -->
            <div class="tab-pane fade" id="addUser" role="tabpanel" aria-labelledby="addUser-tab">
                <h3 class="mt-4">Agregar Nuevo Usuario</h3>
                <form class="mt-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="administrador">Administrador</option>
                                <option value="gestor">Gestor</option>
                                <option value="enlace">Enlace</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar Usuario</button>
                </form>
            </div>

            <!-- Audit Users -->
            <div class="tab-pane fade" id="auditUsers" role="tabpanel" aria-labelledby="auditUsers-tab">
                <h3 class="mt-4">Auditoría de Usuarios</h3>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Fecha</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se llenarán dinámicamente los logs desde el backend -->
                            <tr>
                                <td>jperez</td>
                                <td>Creación de Usuario</td>
                                <td>2024-12-10 14:00:00</td>
                                <td>Nuevo usuario agregado: alopez</td>
                            </tr>
                            <tr>
                                <td>alopez</td>
                                <td>Cambio de Contraseña</td>
                                <td>2024-12-11 09:30:00</td>
                                <td>Actualización de contraseña</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
