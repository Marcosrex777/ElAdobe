<?php
include_once("../conectar_bd.php");

$id = $_GET['id'];

$sql = "DELETE FROM proveedores WHERE id_proveedor = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

header("Location: listar_proveedores.php");
?>
