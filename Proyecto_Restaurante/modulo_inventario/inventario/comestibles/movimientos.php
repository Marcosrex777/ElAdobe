<?php
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo   = $_POST['codigo'] ?? '';
    $nombre   = $_POST['nombre'] ?? '';
    $tipo_mov = $_POST['tipo_mov'] ?? 'SALIDA'; // ENTRADA / SALIDA
    $cantidad = $_POST['cantidad'] ?? 0;
    $motivo   = $_POST['motivo'] ?? '';

    // en este caso solo consumibles
    $tabla = "movimientos_consumibles";

    $stmt = $conn->prepare("INSERT INTO $tabla (codigo, nombre, tipo_movimiento, cantidad, motivo, fecha_movimiento) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssiss", $codigo, $nombre, $tipo_mov, $cantidad, $motivo);

    if ($stmt->execute()) {
        header("Location: listar_movimientos.php?exito=1");
        exit;
    } else {
        $error = "No se pudo registrar el movimiento.";
    }
}
?>
