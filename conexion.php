<?php
$host = 'localhost';
$db = 'chupa111';  // Cambia aquí a tu base de datos
$user = 'root';  // Cambia aquí si tienes otro usuario
$password = '';  // Cambia aquí si tienes una contraseña
$port = '3307';

try {
    $conexion = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Conexión exitosa a la base de datos '$db'";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
