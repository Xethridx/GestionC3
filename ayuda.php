<?php
session_start();
$usuario = $_SESSION['usuario'] ?? 'Invitado';

// Manejo de mensajes
$mensaje = null;

// Procesar formulario de tickets
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asunto = htmlspecialchars($_POST['asunto']);
    $descripcion = htmlspecialchars($_POST['descripcion']);

    // Incluir conexión a la base de datos
    include 'conexion.php';

    try {
        // Insertar ticket con PDO
        $query = "INSERT INTO tickets (usuario, asunto, descripcion) VALUES (:usuario, :asunto, :descripcion)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':asunto', $asunto, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $mensaje = "¡Tu solicitud ha sido enviada con éxito!";
        } else {
            $mensaje = "Error al enviar la solicitud. Inténtalo de nuevo.";
        }
    } catch (PDOException $e) {
        $mensaje = "Error en la base de datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayuda y Soporte</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section" style="background-color: #343a40; color: #fff; padding: 50px 0;">
        <div class="container text-center">
            <h1>Módulo de Ayuda</h1>
            <p>Encuentra respuestas a tus preguntas y solicita soporte técnico.</p>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Preguntas Frecuentes (FAQ)</h2>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        ¿Cómo inicio sesión en el sistema?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Para iniciar sesión, ingresa tu nombre de usuario y contraseña en la página principal. Si olvidaste tu contraseña, contacta al administrador del sistema.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        ¿Qué documentos debo subir?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Debes subir tu identificación oficial, comprobante de domicilio, CURP y acta de nacimiento. Asegúrate de que estén en formato PDF.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Form -->
    <div class="container my-5">
        <h2 class="text-center mb-4">¿Necesitas ayuda? Envía una solicitud</h2>
        <?php if ($mensaje): ?>
            <div class="alert alert-success text-center"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <form action="ayuda.php" method="POST" class="row g-3">
            <div class="col-md-6">
                <label for="asunto" class="form-label">Asunto</label>
                <input type="text" class="form-control" id="asunto" name="asunto" placeholder="Escribe el asunto" required>
            </div>
            <div class="col-md-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe el problema" required></textarea>
            </div>
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
