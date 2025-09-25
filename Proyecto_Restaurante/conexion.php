<?php
$host = "mysql.railway.internal";     // Servidor local en Laragon
$user = "root";          // Usuario por defecto en MySQL
$password = "QBArbtJOFPQaAqQTYhYllrdhjrjYzuqa";          // En Laragon, root NO tiene contraseña
$database = "Adobe_proyect"; // Nombre de la base que creaste en Workbench

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
?>