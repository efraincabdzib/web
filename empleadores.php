<?php
include 'conexion.php';

$sql = "SELECT * FROM empleadores";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$empleadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($empleadores) {
    echo "<ul>";
    foreach ($empleadores as $empleador) {
        echo "<li>ID: " . htmlspecialchars($empleador['id']) . " - Empresa: " . htmlspecialchars($empleador['empresa']) . " - Email: " . htmlspecialchars($empleador['email']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No hay empleadores disponibles.</p>";
}
?>
