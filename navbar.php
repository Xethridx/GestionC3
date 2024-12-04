<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">C3 Gestión</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Inicio</a>
                </li>
                <?php if ($_SESSION['tipo_usuario'] == 'administrador'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">Panel Administrador</a>
                </li>
                <?php endif; ?>
                <?php if (in_array($_SESSION['tipo_usuario'], ['gestor', 'enlace', 'administrador'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="gestor_dashboard.php">Panel Gestor</a>
                </li>
                <?php endif; ?>
                <?php if ($_SESSION['tipo_usuario'] == 'enlace'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="enlace_dashboard.php">Panel Enlace</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
