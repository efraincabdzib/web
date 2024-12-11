<?php
include 'conexion.php';

$sql = "SELECT * FROM postulaciones";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$postulaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($postulaciones) {
    echo "<ul>";
    foreach ($postulaciones as $postulacion) {
        echo "<li>ID: " . htmlspecialchars($postulacion['id']) . " - ID Candidato: " . htmlspecialchars($postulacion['id_candidato']) . " - ID Oferta: " . htmlspecialchars($postulacion['id_oferta']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No hay postulaciones disponibles.</p>";
}
?>
