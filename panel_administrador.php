<?php
// panel_administrador.php
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
    <title>Panel del Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/styles/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <style>
        /* Estilos personalizados para el sidebar y el layout */
        #sidebar {
            background-color: var(--color-fondo); /* Fondo claro para el sidebar */
            padding: 20px;
            border-right: 1px solid #dee2e6; /* Separador visual */
            height: calc(100vh - 70px); /* Ajusta la altura restando la altura del navbar (aprox) */
            overflow-y: auto; /* Añade scroll si el contenido del sidebar excede la altura */
            position: sticky; /* Fija el sidebar */
            top: 70px; /* Ajusta según la altura del navbar */
        }
        #contenido-principal {
            padding: 20px;
        }
        .admin-card {
            margin-bottom: 20px;
            border-radius: 10px;
        }
        .admin-card img {
            border-radius: 50%; /* Para hacer la foto redonda */
            width: 80px;
            height: 80px;
            object-fit: cover; /* Asegura que la imagen se ajuste al círculo */
            margin-bottom: 10px;
        }
        .logs-section {
            border-top: 1px solid #dee2e6; /* Separador superior para los logs */
            padding-top: 20px;
        }
        .log-item {
            font-size: 0.9em;
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #eee; /* Separador entre logs */
        }
        .log-item:last-child {
            border-bottom: none; /* Elimina el separador del último log */
        }
        .log-fecha-hora {
            font-style: italic;
            color: #777;
            font-size: 0.85em;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block">
                <div class="admin-card card shadow-sm">
                    <div class="card-body text-center">
                        <img src="assets/images/default-admin-avatar.png" alt="Foto del Administrador" class="img-fluid">  <h5 class="card-title">Bienvenido, <?php echo htmlspecialchars($nombre_admin); ?></h5>
                        </div>
                </div>

                <div class="logs-section">
                    <h5>Logs de Acceso Recientes</h5>
                    <div id="logs-container">
                        <p>Cargando logs...</p> </div>
                </div>
            </nav>

            <main id="contenido-principal" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="bg-light py-4">
                    <div class="container text-center">
                        <h1 class="fw-bold">Panel del Administrador</h1>
                        <p class="lead">Accede a todos los módulos del sistema y gestiona usuarios, documentos y más.</p>
                    </div>
                </div>

                <div class="container my-3">
                    <div class="row g-4" data-aos="fade-up">
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Gestión de Usuarios</h5>
                                    <p class="card-text">Administra usuarios y roles del sistema.</p>
                                    <a href="gestion_usuarios.php" class="btn btn-primary">Acceder</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <i class="fas fa-folder-open fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Gestión de Listados</h5>
                                    <p class="card-text">Crea y administra listados de elementos, y sus documentos.</p>
                                    <a href="gestion_listados.php" class="btn btn-primary">Acceder</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Validación de Documentos</h5>
                                    <p class="card-text">Valida los documentos cargados.</p>
                                    <a href="validacion_documentos.php" class="btn btn-info">Acceder</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <i class="fas fa-user-check fa-3x text-secondary mb-3"></i>
                                    <h5 class="card-title">Padrón de Evaluados</h5>
                                    <p class="card-text">Consulta la información de los evaluados.</p>
                                    <a href="padron_evaluados.php" class="btn btn-secondary">Acceder</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-ticket-alt"></i> Gestión de Tickets</h5>
                                    <p class="card-text">Visualiza y responde a las solicitudes de soporte enviadas por los usuarios.</p>
                                    <a href="tickets_soporte.php" class="btn btn-primary">Acceder</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card text-center shadow-sm">
                                <div class="card-body">
                                    <i class="fas fa-life-ring fa-3x text-danger mb-3"></i>
                                    <h5 class="card-title">Ayuda Técnica</h5>
                                    <p class="card-text">Encuentra soporte y manuales.</p>
                                    <a href="soporte.php" class="btn btn-danger">Acceder</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();

        $(document).ready(function() {
            function cargarLogs() {
                $.ajax({
                    url: 'obtener_logs_admin.php', // URL del script PHP que devuelve los logs en JSON
                    type: 'GET',
                    dataType: 'json', // Esperamos una respuesta en formato JSON
                    success: function(data) {
                        let logsHTML = '';
                        if (data && !data.error) { // Verificar si data existe y no hay error
                            if (data.length > 0) {
                                $.each(data, function(index, log) {
                                    logsHTML += `
                                        <div class="log-item">
                                            <strong>${log.usuario}</strong> - ${log.accion}
                                            <p class="log-fecha-hora">${log.fecha_hora}</p>
                                        </div>
                                    `;
                                });
                            } else {
                                logsHTML = '<p><small>No hay logs de acceso recientes.</small></p>'; // Mensaje si no hay logs
                            }
                        } else {
                            logsHTML = '<p class="text-danger"><small>Error al cargar logs.</small></p>'; // Mensaje de error
                            if (data.error) {
                                logsHTML += `<p class="text-danger"><small>${data.error}</small></p>`; // Mostrar mensaje de error detallado si existe
                            }
                        }
                        $('#logs-container').html(logsHTML); // Actualizar el contenedor de logs con el HTML generado
                    },
                    error: function() {
                        $('#logs-container').html('<p class="text-danger"><small>Error al conectar con el servidor para obtener logs.</small></p>'); // Mensaje de error de conexión AJAX
                    }
                });
            }

            cargarLogs(); // Cargar logs inicialmente al cargar la página
            setInterval(cargarLogs, 10000); // Actualizar logs cada 10 segundos (ajusta el intervalo según desees)
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>