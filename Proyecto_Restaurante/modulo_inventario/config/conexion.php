<?php
// ======================================================
// CONEXIÓN A LA BASE DE DATOS EL_ADOBE
// ======================================================

$host = "localhost:3307";
$user = "root";
$pass = "";
$db   = "el_adobe";

$conn = new mysqli($host, $user, $pass, $db);

// alias para código viejo que usa $conexion
$conexion = $conn;

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
