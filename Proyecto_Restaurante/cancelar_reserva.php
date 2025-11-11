<?php
require 'conectar_bd.php';
session_start();

// Verificar sesión activa
if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginClientes.php");
    exit();
}

// Verificar que venga el ID de la reservación
if (!isset($_POST['id_reservacion'])) {
    header("Location: reservaciones.php");
    exit();
}

$id_reservacion = $_POST['id_reservacion'];

// Eliminar directamente la reservación
$sqlDelete = "DELETE FROM Hreservaciones WHERE id_reservacion = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param("i", $id_reservacion);

if ($stmtDelete->execute()) {
    $_SESSION['mensaje'] = "✅ La reservación fue eliminada correctamente.";
} else {
    $_SESSION['mensaje'] = "❌ Error al eliminar la reservación.";
}

header("Location: reservaciones.php");
exit();
?>