<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es candidato
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'candidato') {
    echo "No tienes permiso para ver esta página.";
    exit();
}

// Obtener el ID del candidato actual
$sql_candidato = "SELECT IDcandidato FROM Candidatos WHERE IDusuario = ?";
$stmt_candidato = $conexion->prepare($sql_candidato);
$stmt_candidato->execute([$_SESSION['usuario_id']]);
$candidato = $stmt_candidato->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM HistorialLaboral WHERE IDcandidato = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$candidato['IDcandidato']]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .btn {
            display: inline-block;
            background-color: #ec4899;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .btn:hover {
            background-color: #be185d;
        }
    </style>
</head>
<body>
    <a href="agregar_historial.php" class="btn">+ Agregar Historial Laboral</a>

    <?php if ($historial): ?>
        <ul>
            <?php foreach ($historial as $trabajo): ?>
                <li>
                    ID: <?= htmlspecialchars($trabajo['IDhistorial']) ?> 
                    - Empresa: <?= htmlspecialchars($trabajo['empresa']) ?> 
                    - Puesto: <?= htmlspecialchars($trabajo['puesto']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay historial laboral disponible.</p>
    <?php endif; ?>
</body>
</html>