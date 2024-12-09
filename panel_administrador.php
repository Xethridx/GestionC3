<?php
/* Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión como administrador
if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}*/

// Obtener el nombre del administrador para el saludo
$nombre_admin = htmlspecialchars($_SESSION['usuario']);

// Configuración de la base de datos
$host = "localhost";
$username = "root";
$password = "";
$dbname = "GestionDocumental";

// Conectar a la base de datos para cargar los logs
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    header("Location: error.php?mensaje=Error+de+conexión");
    exit();
}

// Obtener los logs de acceso
$sql_logs = "SELECT usuario, fecha_hora, accion FROM logs_accesos ORDER BY fecha_hora DESC LIMIT 10";
$result_logs = $conn->query($sql_logs);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Administrador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/styles/styles.css">
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Barra lateral -->
            <nav class="col-md-2 d-none d-md-block bg-dark sidebar py-4">
                <div class="text-center text-white mb-3">
                    <h4>Panel Administrador</h4>
                    <p>Bienvenido, <strong><?php echo $nombre_admin; ?></strong></p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="gestion_usuarios.php">Gestión de Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="programacion.php">Programación</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="carga_documentos.php">Carga de Documentos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="validacion_documentos.php">Validación de Documentos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="padron_evaluados.php">Padrón de Evaluados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="ayuda.php">Módulo de Ayuda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="Probarconexion.php">Probar Conexión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-10 ms-sm-auto col-lg-10 px-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2">Dashboard del Administrador</h1>
                </div>

                <!-- Módulos en formato de tarjetas -->
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Gestión de Usuarios</h5>
                                <p class="card-text">Administra usuarios y roles del sistema.</p>
                                <a href="gestion_usuarios.php" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Carga de Documentos</h5>
                                <p class="card-text">Sube documentos al sistema.</p>
                                <a href="carga_documentos.php" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Validación de Documentos</h5>
                                <p class="card-text">Valida la información cargada.</p>
                                <a href="validacion_documentos.php" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Programación</h5>
                                <p class="card-text">Organiza las actividades.</p>
                                <a href="programacion.php" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Módulo de Ayuda</h5>
                                <p class="card-text">Encuentra soporte técnico.</p>
                                <a href="ayuda.php" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs de Acceso -->
                <div class="mt-5">
                    <h2>Logs de Acceso Recientes</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Fecha y Hora</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_logs && $result_logs->num_rows > 0): ?>
                                    <?php while ($row = $result_logs->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                            <td><?php echo htmlspecialchars($row['fecha_hora']); ?></td>
                                            <td><?php echo htmlspecialchars($row['accion']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No hay logs recientes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Cerrar la conexión
if ($conn) {
    $conn->close();
}
?>
