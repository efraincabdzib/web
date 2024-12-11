<?php
// Incluir la conexión
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'conexion.php';

// Debug: Imprimir información de la sesión
error_log("Ofertas.php - Session Info: " . print_r($_SESSION, true));

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    error_log("Usuario no logueado - redirigiendo a login.php");
    header("Location: login.php");
    exit();
}

// Verificar el tipo de usuario
$es_empleador = ($_SESSION['usuario_tipo'] == 'empleador');
$es_candidato = ($_SESSION['usuario_tipo'] == 'candidato');

error_log("Tipo de usuario: " . $_SESSION['usuario_tipo']);
error_log("Es empleador: " . ($es_empleador ? "Sí" : "No"));
error_log("Es candidato: " . ($es_candidato ? "Sí" : "No"));

// Si es candidato, verificar si tiene perfil
if ($es_candidato) {
    $check_profile_sql = "SELECT IDcandidato FROM candidatos WHERE IDcandidato = :usuario_id";
    $check_profile_stmt = $conexion->prepare($check_profile_sql);
    $check_profile_stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $check_profile_stmt->execute();
    
    if ($check_profile_stmt->rowCount() == 0) {
        // No tiene perfil, crear uno básico
        try {
            $insert_profile_sql = "INSERT INTO candidatos (IDcandidato, nombre, correo_electronico) 
                                 SELECT IDusuario, nombre, correo_electronico 
                                 FROM usuarios 
                                 WHERE IDusuario = :usuario_id";
            $insert_profile_stmt = $conexion->prepare($insert_profile_sql);
            $insert_profile_stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $insert_profile_stmt->execute();
        } catch (PDOException $e) {
            $mensaje_error = "Error al crear el perfil de candidato: " . $e->getMessage();
        }
    }
}

// Procesar postulación
if ($es_candidato && isset($_POST['postular'])) {
    $oferta_id = $_POST['oferta_id'];
    
    try {
        // Verificar si ya existe una postulación
        $check_sql = "SELECT COUNT(*) FROM postulaciones WHERE IDcandidato = :candidato_id AND IDoferta = :oferta_id";
        $check_stmt = $conexion->prepare($check_sql);
        $check_stmt->bindParam(':candidato_id', $_SESSION['usuario_id']);
        $check_stmt->bindParam(':oferta_id', $oferta_id);
        $check_stmt->execute();
        
        if ($check_stmt->fetchColumn() == 0) {
            // Verificar que existe el perfil del candidato
            $check_profile_sql = "SELECT IDcandidato FROM candidatos WHERE IDcandidato = :usuario_id";
            $check_profile_stmt = $conexion->prepare($check_profile_sql);
            $check_profile_stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $check_profile_stmt->execute();
            
            if ($check_profile_stmt->rowCount() > 0) {
                // Insertar nueva postulación con fecha actual
                $sql = "INSERT INTO postulaciones (IDcandidato, IDoferta, estado, fecha_postulacion) 
                        VALUES (:candidato_id, :oferta_id, 'pendiente', NOW())";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':candidato_id', $_SESSION['usuario_id']);
                $stmt->bindParam(':oferta_id', $oferta_id);
                
                if ($stmt->execute()) {
                    $mensaje_postulacion = "¡Postulación exitosa! Tu solicitud ha sido enviada y está en revisión.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje_postulacion = "Hubo un error al procesar tu postulación. Por favor, intenta nuevamente.";
                    $tipo_mensaje = "error";
                }
            } else {
                $mensaje_postulacion = "Es necesario completar tu perfil de candidato antes de postular.";
                $tipo_mensaje = "warning";
            }
        } else {
            $mensaje_postulacion = "Ya te has postulado a esta oferta anteriormente.";
            $tipo_mensaje = "warning";
        }
    } catch (PDOException $e) {
        $mensaje_postulacion = "Error al procesar la postulación: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Procesar nueva oferta de trabajo (para empleadores)
if ($es_empleador && $_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['postular'])) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $ubicacion = $_POST['ubicacion'];
    $salario = $_POST['salario'];
    $fecha_publicacion = $_POST['fecha_publicacion'];

    if (empty($titulo) || empty($descripcion) || empty($ubicacion) || empty($salario) || empty($fecha_publicacion)) {
        $mensaje_error = "Todos los campos son obligatorios.";
    } else {
        try {
            $sql = "INSERT INTO Ofertas (titulo, descripcion, ubicacion, salario, fecha_publicacion, IDempleador) 
                    VALUES (:titulo, :descripcion, :ubicacion, :salario, :fecha_publicacion, :empleador_id)";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':salario', $salario);
            $stmt->bindParam(':fecha_publicacion', $fecha_publicacion);
            $stmt->bindParam(':empleador_id', $_SESSION['usuario_id']);
            $stmt->execute();
            $mensaje_exito = "Oferta de trabajo añadida exitosamente.";
        } catch (PDOException $e) {
            $mensaje_error = "Error al insertar oferta: " . $e->getMessage();
        }
    }
}

// Ver detalles de oferta
if (isset($_POST['ver_detalles'])) {
    $id_oferta = $_POST['id_oferta'];
    $query = "SELECT o.*, e.nombre_empresa, e.correo_contacto, e.telefono 
              FROM ofertas_trabajo o 
              JOIN empleadores e ON o.id_empleador = e.id 
              WHERE o.id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $id_oferta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $oferta_detallada = $resultado->fetch_assoc();
}

// Consultar ofertas y sus postulaciones
if ($es_empleador) {
    // Para empleadores, mostrar solo sus ofertas
    $sql = "SELECT o.*, 
            (SELECT COUNT(*) FROM postulaciones p WHERE p.IDoferta = o.IDoferta) as total_postulaciones
            FROM Ofertas o 
            WHERE o.IDempleador = :usuario_id
            ORDER BY o.fecha_publicacion DESC";
} else {
    // Para candidatos, mostrar todas las ofertas disponibles
    $sql = "SELECT o.*, 
            (SELECT COUNT(*) FROM postulaciones p WHERE p.IDoferta = o.IDoferta) as total_postulaciones,
            (SELECT COUNT(*) FROM postulaciones p2 WHERE p2.IDoferta = o.IDoferta AND p2.IDcandidato = :usuario_id) as ya_postulado
            FROM Ofertas o
            ORDER BY o.fecha_publicacion DESC";
}
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bolsa de Trabajo - Conectando Talentos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <?php if (isset($mensaje_postulacion)): ?>
    <div class="fixed top-4 right-4 z-50">
        <div class="<?php echo $tipo_mensaje == 'success' ? 'bg-green-100 border-green-400 text-green-700' : 
                         ($tipo_mensaje == 'warning' ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 
                         'bg-red-100 border-red-400 text-red-700'); ?> 
                    border px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $mensaje_postulacion; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="relative">
        <header class="bg-white shadow-lg">
            <nav class="container mx-auto px-6 py-3">
                <div class="flex justify-between items-center">
                    <div class="text-xl font-semibold text-gray-700">
                        <a href="index.html" class="flex items-center">
                            <i class="fas fa-briefcase text-primary-600 mr-2"></i>
                            Bolsa de Trabajo
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="index.html" class="text-gray-600 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-home mr-1"></i> Inicio
                        </a>
                        <?php if ($es_candidato): ?>
                        <a href="candidatos.php" class="text-gray-600 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-user mr-1"></i> Mi Perfil
                        </a>
                        <?php endif; ?>
                        <a href="logout.php" class="text-gray-600 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-1"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </nav>
        </header>

        <main class="container mx-auto px-6 py-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">
                <?php echo $es_empleador ? '¡Gestiona tus Ofertas de Trabajo!' : '¡Encuentra tu Próxima Oportunidad!'; ?>
            </h1>
            <p class="text-xl text-gray-600 mb-8">
                <?php echo $es_empleador ? 'Publica y administra tus ofertas de trabajo' : 'Explora las ofertas disponibles y postúlate'; ?>
            </p>

            <?php if ($es_empleador): ?>
                <div class="bg-white shadow-md rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        <i class="fas fa-plus-circle mr-2 text-primary-600"></i>
                        Agregar Nueva Oferta
                    </h2>
                    <form method="POST" action="" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="titulo" class="block text-sm font-medium text-gray-700">Título de la Oferta</label>
                                <input type="text" id="titulo" name="titulo" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="ubicacion" class="block text-sm font-medium text-gray-700">Ubicación</label>
                                <input type="text" id="ubicacion" name="ubicacion" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="5" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="salario" class="block text-sm font-medium text-gray-700">Salario</label>
                                <input type="text" id="salario" name="salario" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="fecha_publicacion" class="block text-sm font-medium text-gray-700">Fecha de Publicación</label>
                                <input type="date" id="fecha_publicacion" name="fecha_publicacion" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Publicar Oferta
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($ofertas as $oferta): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                <i class="fas fa-briefcase text-primary-600 mr-2"></i>
                                <?php echo htmlspecialchars($oferta['titulo']); ?>
                            </h3>
                            <div class="text-gray-600 mb-4">
                                <p class="mb-2">
                                    <i class="fas fa-map-marker-alt text-primary-600 mr-2"></i>
                                    <?php echo htmlspecialchars($oferta['ubicacion']); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-money-bill-wave text-primary-600 mr-2"></i>
                                    <?php echo htmlspecialchars($oferta['salario']); ?>
                                </p>
                                <p class="mb-4">
                                    <i class="fas fa-calendar text-primary-600 mr-2"></i>
                                    <?php echo date('d/m/Y', strtotime($oferta['fecha_publicacion'])); ?>
                                </p>
                            </div>
                            <div class="flex justify-between items-center">
                                <?php if (!$es_empleador): ?>
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="id_oferta" value="<?php echo $oferta['IDoferta']; ?>">
                                        <button type="submit" name="ver_detalles" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300 mr-2">
                                            Ver Detalles
                                        </button>
                                        <button type="submit" name="postular" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition duration-300">
                                            Postular
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Modal de Detalles -->
            <?php if (isset($oferta_detallada)): ?>
            <div id="modalDetalles" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
                <div class="bg-white p-8 rounded-lg shadow-xl max-w-2xl w-full mx-4">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($oferta_detallada['titulo']); ?></h2>
                        <form method="POST">
                            <button type="submit" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="border-b pb-4">
                            <h3 class="font-semibold text-lg mb-2">Información de la Empresa</h3>
                            <p><i class="fas fa-building mr-2"></i><?php echo htmlspecialchars($oferta_detallada['nombre_empresa']); ?></p>
                            <p><i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($oferta_detallada['correo_contacto']); ?></p>
                            <p><i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($oferta_detallada['telefono']); ?></p>
                        </div>
                        
                        <div class="border-b pb-4">
                            <h3 class="font-semibold text-lg mb-2">Detalles del Puesto</h3>
                            <p><i class="fas fa-map-marker-alt mr-2"></i>Ubicación: <?php echo htmlspecialchars($oferta_detallada['ubicacion']); ?></p>
                            <p><i class="fas fa-money-bill-wave mr-2"></i>Salario: <?php echo htmlspecialchars($oferta_detallada['salario']); ?></p>
                            <p><i class="fas fa-calendar mr-2"></i>Fecha de publicación: <?php echo date('d/m/Y', strtotime($oferta_detallada['fecha_publicacion'])); ?></p>
                        </div>
                        
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Descripción del Puesto</h3>
                            <p class="text-gray-700 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($oferta_detallada['descripcion'])); ?></p>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <form method="POST">
                                <input type="hidden" name="id_oferta" value="<?php echo $oferta_detallada['id']; ?>">
                                <button type="submit" name="postular" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition duration-300">
                                    Postular a esta oferta
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>

        <footer class="bg-gray-800 text-white py-8 mt-12">
            <div class="container mx-auto px-6">
                <div class="flex flex-wrap justify-between items-center">
                    <div class="w-full md:w-1/3 text-center md:text-left">
                        <h3 class="text-lg font-semibold mb-2">Bolsa de Trabajo</h3>
                        <p class="text-gray-400">Conectando talentos con oportunidades</p>
                    </div>
                    <div class="w-full md:w-1/3 text-center mt-4 md:mt-0">
                        <div class="flex justify-center space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 text-center md:text-right mt-4 md:mt-0">
                        <p class="text-gray-400">&copy; 2024 Todos los derechos reservados</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>