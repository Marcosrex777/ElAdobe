<?php
$host = "localhost";
$user = "root";
$pass = "luiscarlos2004";
$db = "gestion_proveedores";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
