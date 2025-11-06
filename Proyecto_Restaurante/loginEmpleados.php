<?php
session_start();

// Si ya hay sesión activa, redirigir
if (isset($_SESSION['usuario'])) {
    header("Location: menu_modulos.php");
    exit();
}

require_once "conectar_bd.php";
$error = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    // Consulta con JOIN para obtener rol
    $sql = "SELECT U.id_usuario, U.nombre_usuario, U.contrasena, U.identificador, 
                   R.id_rol, R.nombre_rol
            FROM usuarios U
            INNER JOIN roles R ON U.id_rol = R.id_rol
            WHERE U.nombre_usuario = ? AND U.estado = 'Activo'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();

        // Verificar contraseña
        if (password_verify($password, $fila['contrasena'])) {

            if ($fila['identificador'] == 1) { // Solo empleados
                // Guardar datos de sesión
                $_SESSION['usuario'] = $fila['nombre_usuario'];
                $_SESSION['id_usuario'] = $fila['id_usuario'];
                $_SESSION['id_rol'] = $fila['id_rol'];
                $_SESSION['nombre_rol'] = $fila['nombre_rol'];

                header("Location: menu_modulos.php");
                exit();
            } else {
                $error = "Acceso denegado: este usuario no es empleado.";
            }
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado o inactivo.";
    }

    $stmt->close();
}
$conn->close();
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* ocupa toda la altura de la pantalla */
    margin: 0;
}

.login-container {
    width: 520px; /* 🔹 más grande que los 350px */
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.login-container h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 2rem; /* 🔹 más grande */
    font-weight: bold; /* opcional: más grueso */
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

    </style>
</head>
<body>
<div class="container"></div>
<div class="login-container">
    <h2>Iniciar Sesión</h2>
    
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="loginempleados.php">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
</div>




</div>


</body>
</html>
