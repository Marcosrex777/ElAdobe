<?php
// Iniciar sesión
session_start();

// Verificar si ya está logueado
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$error = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    // 🔒 Ejemplo de credenciales (luego se puede conectar a BD)
    $usuario_valido = "admin";
    $password_valida = "1234";

    if ($usuario === $usuario_valido && $password === $password_valida) {
        $_SESSION['usuario'] = $usuario;
        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
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
<div class="container">
<div class="login-container">
    <h2>Creación de Usuario</h2>
    
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        
    <input type="text" name="Nombres" placeholder="Nombre" required>
    <input type="text" name="Apellidos" placeholder="Apellido" required>
    <input type="text" name="usuario" placeholder="Usuario" required>
    
        <input type="password" name="password" placeholder="Contraseña" required>
            <input type="password" name="password" placeholder="Confimación de Contraseña" required>
        <button type="submit">Registrar</button>


    </form>
</div>




</div>
</div>

</body>
</html>
