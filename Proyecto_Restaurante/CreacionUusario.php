<?php
session_start();
require_once "conectar_bd.php"; // conexión a la base de datos

$error = "";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombres = trim($_POST['Nombres']);
    $apellidos = trim($_POST['Apellidos']);
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $password = trim($_POST['password']);
    $confirmar = trim($_POST['confirmar']);

    // Validar campos
    if (empty($nombres) || empty($apellidos) || empty($usuario) || empty($correo) || empty($password) || empty($confirmar) || empty($telefono) || empty($fecha_nacimiento)) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Ingresa un correo electrónico válido.";
    } elseif (!preg_match("/^[0-9]{1,8}$/", $telefono)) {
        $error = "❌ El teléfono debe contener solo números (máximo 8 dígitos).";
    } elseif (!preg_match("/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[0-9]{4}$/", $fecha_nacimiento)) {
        $error = "❌ La fecha debe tener el formato DD/MM/YYYY (por ejemplo: 01/11/2025).";
    } elseif ($password !== $confirmar) {
        $error = "❌ Las contraseñas no coinciden.";
    } else {
        // Verificar si el usuario o correo ya existen
        $sql_check = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? OR correo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ss", $usuario, $correo);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();

        if ($resultado->num_rows > 0) {
            $error = "⚠️ El nombre de usuario o el correo ya están en uso.";
        } else {
            // Crear hash seguro de la contraseña
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Convertir fecha a formato MySQL
            $fecha_mysql = DateTime::createFromFormat('d/m/Y', $fecha_nacimiento)->format('Y-m-d');

            // Datos adicionales
            $nombre_completo = $nombres . " " . $apellidos;
            $estado = "Activo";
            $identificador = 0; // Cliente
            $id_rol = 2; // Cambia según tu sistema

            // Insertar el usuario con los nuevos campos
            $sql_insert = "INSERT INTO usuarios (nombre_usuario, contrasena, nombre_completo, correo, telefono, fecha_nacimiento, estado, identificador, id_rol)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssssssi", $usuario, $password_hash, $nombre_completo, $correo, $telefono, $fecha_mysql, $estado, $identificador, $id_rol);

            if ($stmt_insert->execute()) {
                $mensaje = "✅ Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
            } else {
                $error = "❌ Error al registrar el usuario.";
                if (isset($conn) && $conn instanceof mysqli) {
                    $error .= " " . htmlspecialchars($conn->error);
                }
            }

            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            width: 520px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            margin-top: 20rem;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 2rem;
            font-weight: bold;
        }

        .login-container input {
            width: 100%;
            padding: 14px 3px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        .login-container button {
            width: 100%;
            padding: 14px;
            background: #8C6A5A;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }

        .login-container button:hover {
            background: #6d5043;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 12px;
        }

        .mensaje {
            color: green;
            text-align: center;
            margin-bottom: 12px;
        }

        .small-text {
            color: red;
            font-size: 0.9rem;
            display: none;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Creación de Usuario</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="post" action="" onsubmit="return validarContrasenas()">
        <input type="text" name="Nombres" placeholder="Nombre" required>
        <input type="text" name="Apellidos" placeholder="Apellido" required>
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="text" id="telefono" name="telefono" placeholder="Teléfono (máx. 8 dígitos)" maxlength="8" required>
        <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de nacimiento (dd/mm/yyyy)" required
               pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])/[0-9]{4}$"
               title="Formato válido: DD/MM/YYYY">
        <input type="password" id="password" name="password" placeholder="Contraseña" required>
        <input type="password" id="confirmar" name="confirmar" placeholder="Confirmar Contraseña" required>
        <small id="mensaje-error" class="small-text">Las contraseñas no coinciden.</small>
        <button type="submit">Registrar</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
        ¿Ya tienes cuenta? <a href="loginClientes.php">Inicia sesión aquí</a>
    </p>
</div>

<script>
    const password = document.getElementById('password');
    const confirmar = document.getElementById('confirmar');
    const mensajeError = document.getElementById('mensaje-error');
    const telefono = document.getElementById('telefono');

    // Validar contraseñas
    function validarContrasenas() {
        if (password.value !== confirmar.value) {
            mensajeError.style.display = 'block';
            return false;
        }
        return true;
    }

    confirmar.addEventListener('input', () => {
        mensajeError.style.display = (password.value !== confirmar.value) ? 'block' : 'none';
    });

    // Permitir solo números en teléfono
    telefono.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
</body>
</html>
