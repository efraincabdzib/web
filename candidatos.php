<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $id_usuario = $_SESSION['usuario_id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $correo = $_POST['correo_electronico'];
        $telefono = $_POST['telefono'];
        $nivel_escolaridad = $_POST['nivel_de_escolaridad'];
        $habilidades = $_POST['habilidades'];

        $sql = "INSERT INTO candidatos (IDusuario, nombre, apellido, correo_electronico, telefono, nivel_de_escolaridad, habilidades) 
                VALUES (:id_usuario, :nombre, :apellido, :correo, :telefono, :nivel_escolaridad, :habilidades)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':nivel_escolaridad', $nivel_escolaridad);
        $stmt->bindParam(':habilidades', $habilidades);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success'>¡Perfil guardado exitosamente!</div>";
        }
    } catch(PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error al guardar el perfil: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro de Candidato - Bolsa de Trabajo</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .page-title {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 5px;
            padding: 10px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-submit:hover {
            background-color: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .candidates-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .candidate-item {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #4CAF50;
        }
        .candidate-item:hover {
            transform: translateX(5px);
            transition: all 0.3s ease;
        }
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4CAF50;
        }
        .alert {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .form-select {
            height: 45px;
            border: 2px solid #e9ecef;
        }
        .icon-info {
            color: #4CAF50;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-container">
            <h1 class="page-title">
                <i class="fas fa-user-plus"></i> Registro de Perfil de Candidato
            </h1>
            
            <?php if(isset($mensaje)) echo $mensaje; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="nombre">
                                <i class="fas fa-user icon-info"></i>Nombre
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="apellido">
                                <i class="fas fa-user icon-info"></i>Apellido
                            </label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="correo_electronico">
                                <i class="fas fa-envelope icon-info"></i>Correo Electrónico
                            </label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="telefono">
                                <i class="fas fa-phone icon-info"></i>Teléfono
                            </label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nivel_de_escolaridad">
                        <i class="fas fa-graduation-cap icon-info"></i>Nivel de Escolaridad
                    </label>
                    <select class="form-select" id="nivel_de_escolaridad" name="nivel_de_escolaridad" required>
                        <option value="">Seleccione un nivel</option>
                        <option value="primaria">Primaria</option>
                        <option value="secundaria">Secundaria</option>
                        <option value="universitario">Universitario</option>
                        <option value="postgrado">Postgrado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="habilidades">
                        <i class="fas fa-star icon-info"></i>Habilidades
                    </label>
                    <textarea class="form-control" id="habilidades" name="habilidades" rows="4" 
                              placeholder="Describe tus habilidades principales, experiencia y conocimientos..."></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Perfil
                </button>
            </form>
        </div>

        <div class="candidates-list">
            <h3 class="section-title">
                <i class="fas fa-list"></i> Perfiles Registrados
            </h3>
            <?php
            $sql = "SELECT * FROM candidatos WHERE IDusuario = :id_usuario";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $_SESSION['usuario_id']);
            $stmt->execute();
            $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($candidatos) {
                foreach ($candidatos as $candidato) {
                    echo '<div class="candidate-item">';
                    echo '<div class="row">';
                    echo '<div class="col-md-6">';
                    echo '<i class="fas fa-user-circle"></i> <strong>' . 
                         htmlspecialchars($candidato['nombre']) . ' ' . 
                         htmlspecialchars($candidato['apellido']) . '</strong>';
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    echo '<i class="fas fa-envelope"></i> ' . 
                         htmlspecialchars($candidato['correo_electronico']);
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="mt-2">';
                    echo '<i class="fas fa-graduation-cap"></i> Nivel: ' . 
                         htmlspecialchars($candidato['nivel_de_escolaridad']);
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="alert alert-info">';
                echo '<i class="fas fa-info-circle"></i> No hay perfiles registrados aún.';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de formulario
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
