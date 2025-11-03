<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../conectar_bd.php';
$conexion = $conn;

$accion = $_GET['action'] ?? $_POST['action'] ?? '';
$q = trim($_GET['q'] ?? $_POST['q'] ?? '');
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

function out($arr){ 
  echo json_encode($arr, JSON_UNESCAPED_UNICODE); 
  exit; 
}

switch ($accion) {
  // === Buscar proveedores ===
  case 'proveedores':
    $qLike = "%$q%";
    $stmt = $conexion->prepare("SELECT id, nombre_proveedor FROM proveedores WHERE nombre_proveedor LIKE ? ORDER BY nombre_proveedor LIMIT 20");
    $stmt->bind_param("s", $qLike);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while($r = $res->fetch_assoc()){
      $data[] = ['id'=>$r['id'],'label'=>$r['nombre_proveedor']];
    }
    out($data);
    break;

  // === Buscar productos ===
  case 'productos':
    $qLike = "%$q%";
    $stmt = $conexion->prepare("SELECT id_producto, nombre, precio_unitario FROM productos WHERE nombre LIKE ? ORDER BY nombre LIMIT 20");
    $stmt->bind_param("s", $qLike);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while($r = $res->fetch_assoc()){
      $data[] = ['id'=>$r['id_producto'],'label'=>$r['nombre'],'precio'=>$r['precio_unitario']];
    }
    out($data);
    break;

  // === Buscar categorías ===
  case 'categorias':
    $qLike = "%$q%";
    $data = [];

    $stmt = $conexion->prepare("SELECT DISTINCT categoria FROM comestibles WHERE categoria LIKE ? LIMIT 20");
    $stmt->bind_param("s", $qLike);
    $stmt->execute();
    $res = $stmt->get_result();
    while($r = $res->fetch_row()){ $data[] = ['label'=>$r[0]]; }
    $stmt->close();

    $stmt = $conexion->prepare("SELECT DISTINCT categoria FROM mobiliario_equipo WHERE categoria LIKE ? LIMIT 20");
    $stmt->bind_param("s", $qLike);
    $stmt->execute();
    $res = $stmt->get_result();
    while($r = $res->fetch_row()){ $data[] = ['label'=>$r[0]]; }
    $stmt->close();

    // eliminar duplicados
    $uniq = [];
    $outArr = [];
    foreach($data as $d){
      $key = mb_strtolower($d['label']);
      if(!isset($uniq[$key])){ $uniq[$key]=1; $outArr[]=$d; }
    }
    out($outArr);
    break;

  // === Buscar comprobantes ===
  case 'comprobantes':
    $qLike = "%$q%";
    $stmt = $conexion->prepare("SELECT DISTINCT numero_comprobante FROM compras 
                                WHERE numero_comprobante LIKE ? 
                                AND numero_comprobante IS NOT NULL 
                                AND numero_comprobante <> '' 
                                ORDER BY numero_comprobante LIMIT 20");
    $stmt->bind_param("s", $qLike);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while($r = $res->fetch_assoc()){ 
      $data[] = ['label'=>$r['numero_comprobante']]; 
    }
    out($data);
    break;

  default:
    out([]);
}
