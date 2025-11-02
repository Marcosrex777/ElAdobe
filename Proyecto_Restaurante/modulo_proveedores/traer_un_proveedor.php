<?php
include 'conectabd.php';

$id = $_GET['id'];

$sql = "SELECT * FROM proveedores WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($fila = $result->fetch_assoc()) {
    echo json_encode($fila);
}

$stmt->close();
$conexion->close();
?>
