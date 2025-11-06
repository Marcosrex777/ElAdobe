<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../conectar_bd.php';
$conexion = $conn;

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'JSON inválido']);
  exit;
}

$id_proveedor = intval($data['id_proveedor'] ?? 0);
$fecha = $data['fecha'] ?? date('Y-m-d');
$metodo = $data['metodo_pago'] ?? 'Efectivo';
$comprobante = $data['numero_comprobante'] ?? null;
$items = $data['items'] ?? [];

if ($id_proveedor <= 0 || empty($items)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>'Proveedor e items son obligatorios']);
  exit;
}

$total = 0.0;
foreach ($items as $it) {
  $cant = max(0, intval($it['cantidad']));
  $precio = floatval($it['precio_unitario']);
  $total += $cant * $precio;
}

$conexion->begin_transaction();
try {
  $stmt = $conexion->prepare("INSERT INTO compras (id_proveedor, fecha, metodo_pago, numero_comprobante, total)
                              VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("isssd", $id_proveedor, $fecha, $metodo, $comprobante, $total);
  $stmt->execute();
  $id_compra = $stmt->insert_id;
  $stmt->close();

  $stmtD = $conexion->prepare("INSERT INTO detalle_compras (id_compra, id_producto, cantidad, subtotal)
                               VALUES (?, ?, ?, ?)");
  foreach ($items as $it) {
    $id_producto = intval($it['id_producto']);
    $cantidad = intval($it['cantidad']);
    $precio = floatval($it['precio_unitario']);
    $subtotal = $cantidad * $precio;
    $stmtD->bind_param("iiid", $id_compra, $id_producto, $cantidad, $subtotal);
    $stmtD->execute();
  }
  $stmtD->close();

  $conexion->commit();
  echo json_encode(['ok'=>true,'msg'=>'Compra registrada','id_compra'=>$id_compra,'total'=>number_format($total,2,'.','')]);
} catch (Throwable $e) {
  $conexion->rollback();
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'Error al registrar compra','error'=>$e->getMessage()]);
}
