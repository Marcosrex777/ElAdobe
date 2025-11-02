<?php
include 'conectabd.php';

$sql = "SELECT id, nombre_proveedor, persona_contacto, telefono, correo, direccion, categoria, estado FROM proveedores";
$result = $conexion->query($sql);

$proveedores = [];

while ($fila = $result->fetch_assoc()) {
    $proveedores[] = $fila;
}

echo json_encode($proveedores);
?>
