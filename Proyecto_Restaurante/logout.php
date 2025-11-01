<?php
session_start(); 

// Eliminar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión completamente
session_destroy();

// Si venía de sesión cliente, redirige al index público
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'Index.php') !== false) {
    header("Location: Index.php");
} else {
    // Por defecto, redirige al login de empleados
    header("Location: Index.php");
}
exit();
?>
