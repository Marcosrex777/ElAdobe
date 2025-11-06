<?php

$host = "localhost";     // Servidor local en Laragon

$user = "u242378161_admin_adobe";          // Usuario por defecto en MySQL

$password = "Admin_Adobe25@";          // En Laragon, root NO tiene contraseña

$database = "u242378161_eladobe"; // Nombre de la base que creaste en Workbench



// Crear conexión

$conn = new mysqli($host, $user, $password, $database);



// Verificar conexión

if ($conn->connect_error) {

    die("Error en la conexión: " . $conn->connect_error);

}

?>