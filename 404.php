<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404 - Página no encontrada</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/styles/styles.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .error-container {
            text-align: center;
        }
        .error-container h1 {
            font-size: 8rem;
            font-weight: bold;
            color: #dc3545;
        }
        .error-container h2 {
            font-size: 2rem;
            color: #343a40;
        }
        .error-container p {
            font-size: 1.2rem;
            color: #6c757d;
        }
        .error-container .btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>Lo sentimos, pero la página que buscas no existe, fue movida o su enlace es incorrecto.</p>
        <a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Volver al Inicio</a>
        <a href="ayuda.php" class="btn btn-secondary"><i class="fas fa-life-ring"></i> Obtener Ayuda</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
