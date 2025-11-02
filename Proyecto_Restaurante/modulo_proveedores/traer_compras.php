<?php
include 'conectabd.php';

$sql = "SELECT id_compra, nombre_proveedor, fecha_compra, metodo_pago, total
        FROM compras_proveedores
        ORDER BY fecha_compra DESC, id_compra DESC";

$result = $conexion->query($sql);

$compras = [];

if ($result) {
    while ($fila = $result->fetch_assoc()) {
        $compras[] = $fila;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($compras);
$conexion->close();
?>
