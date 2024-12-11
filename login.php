<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'conexion.php';  // Incluir la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $correo_electronico = $_POST['correo_electronico'];
    $contrasena = $_POST['contrasena'];

    // Validar si los campos están vacíos
    if (empty($correo_electronico) || empty($contrasena)) {
        $error = "Por favor, ingrese ambos campos (correo electrónico y contraseña).";
    } else {
        try {
            // Buscar el usuario por correo electrónico
            $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo_electronico = :correo_electronico");
            $stmt->bindParam(':correo_electronico', $correo_electronico);
            $stmt->execute();

            // Si no se encuentra un usuario con ese correo
            if ($stmt->rowCount() == 0) {
                $error = "No se encontró una cuenta con ese correo electrónico.";
            } else {
                // Obtener los datos del usuario
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                // Comparar la contraseña en texto plano con la que se encuentra en la base de datos
                if ($contrasena == $usuario['contrasena']) {
                    // Iniciar sesión si las contraseñas coinciden
                    $_SESSION['usuario_id'] = $usuario['IDusuario'];  // Almacenar el ID del usuario en la sesión
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];  // Nombre del usuario
                    $_SESSION['usuario_tipo'] = $usuario['tipo_de_usuario'];  // Tipo de usuario (empleador, candidato, admin)

                    // Debug: Imprimir información de la sesión
                    error_log("Sesión iniciada - ID: " . $_SESSION['usuario_id'] . ", Tipo: " . $_SESSION['usuario_tipo']);

                    // Redirigir según el tipo de usuario
                    if ($_SESSION['usuario_tipo'] == 'empleador' || $_SESSION['usuario_tipo'] == 'candidato') {
                        header("Location: ofertas.php");
                        exit();
                    } else {
                        $error = "Tipo de usuario desconocido: " . $_SESSION['usuario_tipo'];
                    }
                } else {
                    // Si la contraseña es incorrecta
                    $error = "La contraseña es incorrecta.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error en la conexión a la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid #ddd;
        }

        h2 {
            color: #ff6b6b;
            margin-bottom: 20px;
            font-size: 26px;
            font-weight: bold;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        input[type="email"]:focus, input[type="password"]:focus {
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

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .register-link {
            font-size: 14px;
        }

        .register-link a {
            color: #ff6b6b;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Estilos para el footer */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #f4f7fc;
            padding: 10px 0;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <?php
        // Mostrar el mensaje de error si existe
        if (isset($error)) {
            echo "<div class='error-message'>$error</div>";
        }
        ?>

        <form method="POST" action="login.php">
            <input type="email" name="correo_electronico" placeholder="Correo Electrónico" required><br>
            <input type="password" name="contrasena" placeholder="Contraseña" required><br>
            <button type="submit">Iniciar Sesión</button>
        </form>

        <p class="register-link">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
    </div>

    <div class="footer">
        <p>&copy; 2024 Bolsa de Trabajo | Todos los derechos reservados</p>
    </div>

</body>
</html>
