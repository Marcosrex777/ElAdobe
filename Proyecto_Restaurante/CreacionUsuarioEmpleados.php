<?php
session_start();
require_once "conectar_bd.php"; 
$error = "";
$mensaje = "";

// Obtener roles desde la base de datos
$roles = [];
$sql_roles = "SELECT id_rol, nombre_rol FROM roles";
$result_roles = $conn->query($sql_roles);

if ($result_roles && $result_roles->num_rows > 0) {
    while ($row = $result_roles->fetch_assoc()) {
        $roles[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombres = trim($_POST['Nombres']);
    $apellidos = trim($_POST['Apellidos']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $confirmar = trim($_POST['confirmar']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $id_rol = intval($_POST['rol']);

    // Validaciones
    if (empty($nombres) || empty($apellidos) || empty($usuario) || empty($password) || empty($confirmar) || empty($id_rol) || empty($correo) || empty($telefono) || empty($fecha_nacimiento)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif (!preg_match("/^[0-9]{1,8}$/", $telefono)) {
        $error = "El teléfono debe contener solo números (máximo 8 dígitos).";
    } elseif (!preg_match("/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/[0-9]{4}$/", $fecha_nacimiento)) {
        $error = "La fecha de nacimiento debe tener el formato DD/MM/YYYY (ejemplo: 01/11/2025).";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el usuario ya existe
        $sql_check = "SELECT id_usuario FROM Usuarios WHERE nombre_usuario = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $usuario);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();

        if ($resultado->num_rows > 0) {
            $error = "El nombre de usuario ya está en uso.";
        } else {
            // Crear hash seguro
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Convertir fecha a formato MySQL
            $fecha_formateada = DateTime::createFromFormat('d/m/Y', $fecha_nacimiento)->format('Y-m-d');

            $nombre_completo = $nombres . " " . $apellidos;
            $estado = "Activo";
            $identificador = 1;

            $sql_insert = "INSERT INTO Usuarios (nombre_usuario, contrasena, nombre_completo, estado, identificador, id_rol, correo, telefono, fecha_nacimiento)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssiisss", $usuario, $password_hash, $nombre_completo, $estado, $identificador, $id_rol, $correo, $telefono, $fecha_formateada);

            if ($stmt_insert->execute()) {
                $mensaje = "✅ Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
            } else {
                $error = "❌ Error al registrar el usuario: " . $conn->error;
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
            margin-top: 30rem;

        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 2rem;
            font-weight: bold;
        }

        .login-container input, .login-container select {
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

    <form method="post" action="">
        <input type="text" name="Nombres" placeholder="Nombre" required>
        <input type="text" name="Apellidos" placeholder="Apellido" required>
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        
        <!-- Teléfono con restricción de números -->
        <input type="text" id="telefono" name="telefono" placeholder="Teléfono (máx. 8 dígitos)" maxlength="8" required pattern="[0-9]{1,8}" title="Solo números (máximo 8 dígitos)">
        
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" id="password" name="password" placeholder="Contraseña" required>
        <input type="password" id="confirmar" name="confirmar" placeholder="Confirmar Contraseña" required>
        <input type="text" name="fecha_nacimiento" placeholder="Fecha de nacimiento (dd/mm/yyyy)" required
               pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])/[0-9]{4}$"
               title="Formato válido: DD/MM/YYYY">

        <select name="rol" required>
            <option value="">Selecciona un rol...</option>
            <?php foreach ($roles as $rol): ?>
                <option value="<?php echo $rol['id_rol']; ?>">
                    <?php echo htmlspecialchars($rol['nombre_rol']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <small id="mensaje-error" class="small-text">Las contraseñas no coinciden.</small>
        <button type="submit">Registrar</button>
    </form>
</div>

<script>
    // Validación de contraseñas en tiempo real
    const password = document.getElementById('password');
    const confirmar = document.getElementById('confirmar');
    const mensajeError = document.getElementById('mensaje-error');
    const telefono = document.getElementById('telefono');

    confirmar.addEventListener('input', () => {
        mensajeError.style.display = password.value !== confirmar.value ? 'block' : 'none';
    });

    // Permitir solo números en el campo teléfono
    telefono.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, ''); // elimina letras o símbolos
    });
</script>
</body>
</html>
