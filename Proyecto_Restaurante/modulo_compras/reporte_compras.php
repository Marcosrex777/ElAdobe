<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../conectar_bd.php';
$conexion = $conn;

$prov_id = intval($_GET['proveedor_id'] ?? 0);
$prod_id = intval($_GET['producto_id'] ?? 0);
$categoria = trim($_GET['categoria'] ?? '');
$comprobante = trim($_GET['comprobante'] ?? '');
$ini = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';

$where = [];
$params = [];
$types = "";

if ($prov_id > 0) { $where[] = "c.id_proveedor = ?"; $types.="i"; $params[] = $prov_id; }
if ($prod_id > 0) { $where[] = "d.id_producto = ?";  $types.="i"; $params[] = $prod_id; }
if ($categoria !== '') { $where[] = "(COALESCE(co.categoria, me.categoria) LIKE ?)"; $types.="s"; $params[] = "%$categoria%"; }
if ($comprobante !== '') { $where[] = "c.numero_comprobante LIKE ?"; $types.="s"; $params[] = "%$comprobante%"; }
if ($ini !== '') { $where[] = "c.fecha >= ?"; $types.="s"; $params[] = $ini; }
if ($fin !== '') { $where[] = "c.fecha <= ?"; $types.="s"; $params[] = $fin; }

$sql = "
SELECT 
  c.fecha,
  p.nombre_proveedor AS proveedor,
  c.numero_comprobante,
  pr.nombre AS producto,
  COALESCE(co.categoria, me.categoria) AS categoria,
  d.cantidad,
  (d.subtotal / NULLIF(d.cantidad,0)) AS precio_unitario,
  d.subtotal,
  c.total AS total_compra
FROM compras c
JOIN proveedores p ON p.id = c.id_proveedor
JOIN detalle_compras d ON d.id_compra = c.id_compra
JOIN productos pr ON pr.id_producto = d.id_producto
LEFT JOIN comestibles co ON co.id_producto = pr.id_producto
LEFT JOIN mobiliario_equipo me ON me.id_producto = pr.id_producto
";

if (!empty($where)) { $sql .= " WHERE ".implode(" AND ", $where); }
$sql .= " ORDER BY c.fecha DESC, c.id_compra DESC";

$stmt = $conexion->prepare($sql);
if(!empty($params)){ $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
$totRep = 0.0;
while($r = $res->fetch_assoc()){
  $rows[] = [
    'fecha'=>$r['fecha'],
    'proveedor'=>$r['proveedor'],
    'comprobante'=>$r['numero_comprobante'],
    'producto'=>$r['producto'],
    'categoria'=>$r['categoria'],
    'cantidad'=>(int)$r['cantidad'],
    'precio'=>number_format((float)$r['precio_unitario'],2,'.',''),
    'subtotal'=>number_format((float)$r['subtotal'],2,'.',''),
    'total_compra'=>number_format((float)$r['total_compra'],2,'.','')
  ];
  $totRep += (float)$r['subtotal'];
}

echo json_encode(['ok'=>true,'rows'=>$rows,'total_reporte'=>number_format($totRep,2,'.','')], JSON_UNESCAPED_UNICODE);
