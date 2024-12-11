<?php
// Iniciar la sesión
session_start();

// Incluir archivo de conexión
include 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo "<p>Debes iniciar sesión para acceder a esta página.</p>";
    exit;
}

try {
    // Preparar la consulta SQL para obtener todos los usuarios
    $sql = "SELECT * FROM usuarios";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si hay resultados
    if ($usuarios) {
        echo "<h1>Lista de Usuarios</h1>";
        echo "<ul>";
        foreach ($usuarios as $usuario) {
            echo "<li>ID: " . htmlspecialchars($usuario['id']) . 
                " - Nombre: " . htmlspecialchars($usuario['nombre']) . 
                " - Email: " . htmlspecialchars($usuario['email']) . 
                "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No hay usuarios disponibles.</p>";
    }
} catch (PDOException $e) {
    // Manejo de errores de la base de datos
    echo "<p>Error al obtener usuarios: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
