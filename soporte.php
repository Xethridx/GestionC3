<?php
session_start();

// Verificar permisos: Solo administrador y enlace
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['administrador', 'enlace'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte Técnico - Sistema de Carga de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="text-center mb-4"><i class="fas fa-question-circle me-2"></i> Módulo de Soporte Técnico</h1>

        <section class="mb-5">
            <h2><i class="fas fa-book me-2"></i> Manual de Usuario del Sistema</h2>
            <p>Descarga el manual de usuario para obtener una guía completa sobre cómo utilizar el sistema de carga de documentos. Este manual cubre todas las funcionalidades disponibles y está diseñado para ayudarte a sacar el máximo provecho del sistema.</p>
            <a href="manuales/manual_usuario_sistema.pdf" class="btn btn-primary" target="_blank"><i class="fas fa-download me-2"></i> Descargar Manual de Usuario (PDF)</a>
            <p class="mt-3"><small>Si no tienes el manual, contacta al administrador del sistema.</small></p>
        </section>

        <section class="mb-5">
            <h2><i class="fas fa-video me-2"></i> Videos de Ayuda - Escaneo a PDF</h2>
            <p>Mira estos videos tutoriales para aprender cómo escanear documentos correctamente en formato PDF. Estos videos te guiarán paso a paso a través del proceso de escaneo, asegurando que tus documentos cumplan con los requisitos del sistema.</p>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Video 1: Escanear con Dispositivo Móvil (Android/iOS)</h5>
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/VIDEO_ID_1" title="Video Tutorial 1" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Video 2: Escanear con Escáner de Escritorio</h5>
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/VIDEO_ID_2" title="Video Tutorial 2" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h2><i class="fas fa-file-alt me-2"></i> Manual Técnico - Carga de Documentos (Usuarios Enlace)</h2>
            <p>Este manual técnico está dirigido específicamente a los usuarios con rol de "Enlace". Contiene instrucciones detalladas sobre el proceso de carga de documentos en el sistema, mejores prácticas y solución de problemas comunes.</p>
            <a href="manuales/manual_tecnico_carga_documentos_enlace.pdf" class="btn btn-info" target="_blank"><i class="fas fa-download me-2"></i> Descargar Manual Técnico Enlace (PDF)</a>
             <p class="mt-3"><small>Si no tienes el manual técnico para enlaces, contacta al administrador del sistema.</small></p>
        </section>

        <section class="mb-5">
            <h2><i class="fas fa-headset me-2"></i> Soporte Técnico y Contacto</h2>
            <p>Si tienes preguntas adicionales, necesitas asistencia técnica o experimentas problemas con el sistema, por favor, utiliza la siguiente información de contacto para solicitar soporte:</p>
            <ul class="list-unstyled">
                <li><i class="fas fa-envelope me-2"></i> <strong>Email de Soporte:</strong> <a href="mailto:soporte@example.com">soporte@example.com</a></li>
                <li><i class="fas fa-phone me-2"></i> <strong>Teléfono de Soporte:</strong> +1-555-123-4567 (Horario de atención: Lunes a Viernes, 9am - 5pm)</li>
                <li><i class="fab fa-whatsapp me-2"></i> <strong>WhatsApp Soporte:</strong> <a href="https://wa.me/NUMERO_DE_TELEFONO_WHATSAPP" target="_blank">+1-555-987-6543</a> (Solo mensajes)</li>
            </ul>
        </section>

        <section>
            <h2><i class="fas fa-external-link-alt me-2"></i> Otros Recursos de Ayuda</h2>
            <p>Aquí encontrarás enlaces a recursos adicionales que pueden ser de utilidad:</p>
            <ul class="list-unstyled">
                <li><i class="fas fa-link me-2"></i> <a href="https://www.example.com/faq" target="_blank">Preguntas Frecuentes (FAQ)</a></li>
                <li><i class="fas fa-link me-2"></i> <a href="https://www.example.com/guias" target="_blank">Guías Paso a Paso Adicionales</a></li>
                <li><i class="fas fa-link me-2"></i> <a href="https://status.example.com" target="_blank">Página de Estado del Sistema</a> (Para verificar si hay interrupciones o mantenimiento)</li>
            </ul>
        </section>

    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>