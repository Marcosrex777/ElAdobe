<?php
require_once "conectar_bd.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);

    // Buscar usuario activo con correo
    $sql = "SELECT id_usuario, nombre_usuario FROM Usuarios WHERE nombre_usuario = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        $id_usuario = $fila['id_usuario'];
        $nombre_usuario = $fila['nombre_usuario'];
        $token = bin2hex(random_bytes(16)); // generar token seguro

        // Guardar token y expiración
        $update = $conn->prepare(
            "UPDATE Usuarios SET token_recuperacion = ?, token_expira = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id_usuario = ?"
        );
        $update->bind_param("si", $token, $id_usuario);
        $update->execute();
        $update->close();

        // Mostrar enlace directo para restablecer contraseña
        $mensaje = "Se ha generado un enlace de recuperación. Haz clic aquí para cambiar tu contraseña:<br>";
        $mensaje .= "<a href='RestablecerContraseñaEmpleados.php?token=$token'>Restablecer contraseña</a>";

    } else {
        $mensaje = "Usuario no encontrado o inactivo.";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar Contraseña</title>
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
    color: green;
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
    <h2>Recuperar Contraseña</h2>
    <form method="post" action="">
        <input type="text" name="usuario" placeholder="Ingrese su usuario" required>
        <button type="submit">Generar enlace</button>
    </form>
    <?php if ($mensaje): ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>
</div>
</body>
</html>
