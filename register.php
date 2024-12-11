<?php
require_once 'conexion.php';  // Incluir la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo_electronico = $_POST['correo_electronico'];
    $contrasena = $_POST['contrasena'];
    $tipo_de_usuario = $_POST['tipo_de_usuario'];

    // Insertar los datos en la base de datos
    $sql = "INSERT INTO usuarios (nombre, apellido, correo_electronico, contrasena, tipo_de_usuario, fecha_de_registro) 
            VALUES (:nombre, :apellido, :correo_electronico, :contrasena, :tipo_de_usuario, NOW())";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo_electronico', $correo_electronico);
    $stmt->bindParam(':contrasena', $contrasena);  // Guardar la contraseña tal cual como texto plano
    $stmt->bindParam(':tipo_de_usuario', $tipo_de_usuario);
    $stmt->execute();

    // Mostrar mensaje de éxito y redirigir
    echo "<div style='color: green; text-align: center;'>Registro exitoso. Redirigiendo...</div>";
    header("refresh:3;url=login.php");  // Redirigir después de 3 segundos
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            color: #ff6b6b;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, select:focus {
            border-color: #ff6b6b;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #ff6b6b;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #ff5252;
        }

        .message {
            font-size: 14px;
            color: red;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>Registro de Usuario</h2>
        <form method="POST" action="register.php">
            <input type="text" name="nombre" placeholder="Nombre" required><br>
            <input type="text" name="apellido" placeholder="Apellido" required><br>
            <input type="email" name="correo_electronico" placeholder="Correo Electrónico" required><br>
            <input type="password" name="contrasena" placeholder="Contraseña" required><br>
            <select name="tipo_de_usuario">
                <option value="empleador">Empleador</option>
                <option value="candidato">Candidato</option>
                <option value="admin">Admin</option>
            </select><br>
            <button type="submit">Registrar</button>
        </form>
        <p class="message">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>

</body>
</html>
