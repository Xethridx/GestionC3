<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener rol y usuario si está autenticado
$usuario = $_SESSION['usuario'] ?? null;
$rol = $_SESSION['rol'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo ($usuario && $rol) ? 'panel_' . $rol . '.php' : 'index.php'; ?>">Sistema de Documentación</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($usuario && $rol): ?>
                        <?php if ($rol === 'administrador'): ?>
                            <li class="nav-item"><a class="nav-link" href="panel_administrador.php">Panel administrador</a></li>
                            <li class="nav-item"><a class="nav-link" href="gestion_usuarios.php">Gestión de Usuarios</a></li>
                            <li class="nav-item"><a class="nav-link" href="gestion_listados.php">Gestion de Listados</a></li>
                            <li class="nav-item"><a class="nav-link" href="tickets_soporte.php">Tickets de Soporte</a></li>
                            <li class="nav-item"><a class="nav-link" href="validacion_documentos.php">Validación de Documentos</a></li>
                            <li class="nav-item"><a class="nav-link" href="padron_evaluados.php">Padrón de Evaluados</a></li>
                        <?php elseif ($rol === 'gestor'): ?>
                            <li class="nav-item"><a class="nav-link" href="panel_gestor.php">Panel Gestor</a></li>
                            <li class="nav-item"><a class="nav-link" href="gestion_listados.php">Gestion de Listados</a></li>
                        <?php elseif ($rol === 'enlace'): ?>
                            <li class="nav-item"><a class="nav-link" href="panel_enlace.php">Panel Enlace</a></li>
                            <li class="nav-item"><a class="nav-link" href="gestion_listados.php">Gestion de Listados</a></li>
                            <li class="nav-item"><a class="nav-link" href="soporte.php">Soporte</a></li>
                        <?php elseif ($rol === 'coordinacion'): ?>
                            <li class="nav-item"><a class="nav-link" href="panel_coordinador.php">Panel Coordinador</a></li>
                            <li class="nav-item"><a class="nav-link" href="validacion_documentos.php">Validación de Documentos</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="ayuda.php">Ayuda</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="login.php">Iniciar Sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="ayuda.php">Ayuda</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>