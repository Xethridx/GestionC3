<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">C3 Gestión</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Enlace a la página de inicio -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Inicio</a>
                </li>

                <?php 
                // Verificar si el usuario está autenticado
                if (isset($_SESSION['tipo_usuario'])):
                    // Redirigir a las páginas correspondientes según el tipo de usuario
                    if ($_SESSION['tipo_usuario'] == 'administrador'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="panel_admin.php">Panel Administrador</a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array($_SESSION['tipo_usuario'], ['gestor', 'enlace', 'administrador'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="panel_gestor.php">Panel Gestor</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($_SESSION['tipo_usuario'] == 'enlace'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="panel_enlace.php">Panel Enlace</a>
                        </li>
                    <?php endif; ?>

                    <!-- Cerrar sesión -->
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                <?php else: ?>
                    <!-- Enlace visible solo si no hay sesión activa -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar Sesión</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
