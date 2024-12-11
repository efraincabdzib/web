<?php
// Include the database connection
include 'conexion.php';
session_start();

// Verify if the user is logged in
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Check if an offer ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ofertas.php");
    exit();
}

$oferta_id = $_GET['id'];

try {
    // Prepare SQL query to get full details of the specific offer
    $sql = "SELECT * FROM Ofertas WHERE IDoferta = :oferta_id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':oferta_id', $oferta_id);
    $stmt->execute();
    
    $oferta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no offer found, redirect back to ofertas.php
    if (!$oferta) {
        header("Location: ofertas.php");
        exit();
    }
} catch (PDOException $e) {
    // Handle database errors
    die("Error al recuperar la oferta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Oferta - <?php echo htmlspecialchars($oferta['titulo']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-6 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($oferta['titulo']); ?></h1>
                
                <div class="mb-4 flex space-x-4 text-gray-600">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <?php echo htmlspecialchars($oferta['ubicacion']); ?>
                    </div>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        $<?php echo number_format($oferta['salario']); ?> al mes
                    </div>
                </div>

                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Descripción</h2>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($oferta['descripcion'])); ?></p>
                </div>

                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Fecha de Publicación</h2>
                    <p class="text-gray-600"><?php echo date('d/m/Y', strtotime($oferta['fecha_publicacion'])); ?></p>
                </div>

                <div class="mt-6">
                    <a href="ofertas.php" class="inline-block bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 transition duration-300">
                        Volver a Ofertas
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>