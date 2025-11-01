<?php
require_once "conectar_bd.php";

$mensaje = "";
$token_valido = false;

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $sql = "SELECT id_usuario, nombre_usuario FROM Usuarios WHERE token_recuperacion = ? AND token_expira > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        $id_usuario = $usuario['id_usuario'];
        $token_valido = true;
    } else {
        $mensaje = "El enlace ha expirado o no es válido.";
    }
    $stmt->close();
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_contrasena'])) {
    $id_usuario = $_POST['id_usuario'];
    $nueva_contrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);

    $sql = "UPDATE Usuarios SET contrasena = ?, token_recuperacion = NULL, token_expira = NULL WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nueva_contrasena, $id_usuario);
    if ($stmt->execute()) {
        $mensaje = "Contraseña restablecida correctamente. Ahora puedes iniciar sesión.";
        $token_valido = false;
    } else {
        $mensaje = "Error al restablecer la contraseña. Intenta de nuevo.";
    }
    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: white;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            width: 400px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #8C6A5A;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #6d5043;
        }
        p {
            text-align: center;
            margin-top: 15px;
        }
        a {
            color: #8C6A5A;
            text-decoration: none;
        }
        a:hover {
            color: #6d5043;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Restablecer Contraseña</h2>

    <?php if ($token_valido): ?>
        <form method="post" action="">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
            <input type="password" name="nueva_contrasena" placeholder="Nueva contraseña" required>
            <button type="submit">Guardar contraseña</button>
        </form>
    <?php else: ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
        <?php if ($mensaje): ?>
            <p><a href="menu_modulos.php">Volver al inicio de sesión</a></p>
        <?php endif; ?>
    <?php endif; ?>

</div>
</body>
</html>
