


<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); 

// Eliminar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión completamente
session_destroy();

// Si venía de sesión cliente, redirige al index público
if (isset($_SESSION['cliente'])) {
    header("Location: index.php");
} else {
    // Por defecto, redirige al login de empleados
    header("Location: index.php");
}
exit();
?>
