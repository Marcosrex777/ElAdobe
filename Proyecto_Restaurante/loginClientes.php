<?php
session_start();

// Si ya hay sesión activa, redirigir al menú de clientes
if (isset($_SESSION['cliente'])) {
    header("Location: Index.php");
    exit();
}

// Incluir conexión externa
require_once "conectar_bd.php";

$error = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    // Consulta segura
    $sql = "SELECT id_usuario, nombre_usuario, contrasena, identificador 
            FROM Usuarios 
            WHERE nombre_usuario = ? AND estado = 'Activo'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();

        // Verificación de contraseña (puedes usar password_verify si usas hashes)
        
  if (password_verify($password, $fila['contrasena'])) {
            // Verificar identificador == 0 (clientes)
            if ($fila['identificador'] == 0) {
                $_SESSION['cliente'] = $fila['nombre_usuario'];
                $_SESSION['id_cliente'] = $fila['id_usuario'];
                header("Location: Index.php");
                exit();
            } else {
                $error = "Acceso denegado: este usuario no es cliente (identificador ≠ 0).";
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

.CrearCuenta{
    text-decoration: none;
   margin: 30%;
}


.CrearCuenta:hover{
    
    color: grey;
}

    </style>
</head>
<body>
<div class="container">
<div class="login-container">
    <h2>Iniciar Sesión</h2>
    
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="loginClientes.php">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>


    


        <a href="./RecuperacionContraseña.php" class="CrearCuenta">Has olvidado tu contraseña?</a>


<p></p>

            <a href="./CreacionUusario.php" class="CrearCuenta">¿No tiene Cuenta?, Crea una </a>
    </form>
</div>




</div>
</div>

</body>
</html>
