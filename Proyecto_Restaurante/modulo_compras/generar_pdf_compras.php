<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ob_start();
require_once 'conectabd.php';
require_once 'fpdf/fpdf.php';
date_default_timezone_set('America/Guatemala');


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

class PDF extends FPDF {
    function Header(){
        // === CAJETÍN PRINCIPAL ===
        $this->SetDrawColor(140,106,90);
        $this->SetLineWidth(0.6);
        $this->SetFillColor(249,249,249);
        $this->Rect(10,10,190,42,'D'); // Marco general

        // === LOGO (más grande) ===
        if (file_exists('logo.png')) {
            // X=14, Y=12, tamaño=35 (ajusta este último valor si quieres más grande)
            $this->Image('logo.png', 14, 12, 45);
        }

        // === DATOS DE LA EMPRESA (alineados correctamente a la derecha) ===
$this->SetXY(115, 13); // posición inicial del bloque
$this->SetFont('Arial','B',10);
$this->Cell(80,6,'Restaurante El Adobe',0,1,'R');

$this->SetFont('Arial','',9);
$this->SetXY(115, 19);
$this->Cell(80,6,'NIT: 1234567-8',0,1,'R');

$this->SetXY(115, 25);
$this->Cell(80,6,'Direccion: Zona 1, Ciudad de Guatemala',0,1,'R');

$this->SetXY(115, 31);
$this->Cell(80,6,'Tel: (502) 5555-5555',0,1,'R');

$this->SetXY(115, 37);
$this->Cell(80,6,'Correo: contacto@eladobe.com',0,1,'R');


        // === TÍTULO PRINCIPAL (más grande y más arriba) ===
        $this->Ln(-2); // subir ligeramente el espaciado
        $this->SetFont('Arial','B',16); // tamaño mayor
        $this->SetTextColor(92,61,46);
        $this->Cell(0,9,'REPORTE DE COMPRAS',0,1,'C');
        $this->SetTextColor(0,0,0);
        $this->Ln(2); // espaciado menor antes de la tabla

        // === DATOS ADMINISTRATIVOS DEL REPORTE ===
        $this->SetFont('Arial','',9);
        $fecha = date('d/m/Y');
        $hora = date('H:i:s');
        $codigo = 'RCP-'.date('Y-m-d');
        global $ini, $fin;
        $periodo = ($ini && $fin) ? date('d/m/Y',strtotime($ini)).' - '.date('d/m/Y',strtotime($fin)) : date('d/m/Y',strtotime('-30 days')).' - '.date('d/m/Y');

        // Color de fondo igual que encabezados de tablas
        $this->SetFillColor(241,230,225);
        $this->SetLineWidth(0.4);
        $this->SetX(10);
        $this->SetFont('Arial','B',9);
        $this->Cell(40,7,'Codigo del Reporte',1,0,'L',true);
        $this->SetFont('Arial','',9);
        $this->Cell(60,7,$codigo,1,0,'L');
        $this->SetFont('Arial','B',9);
        $this->Cell(40,7,'Fecha de Emision',1,0,'L',true);
        $this->SetFont('Arial','',9);
        $this->Cell(50,7,$fecha,1,1,'L');

        $this->SetX(10);
        $this->SetFont('Arial','B',9);
        $this->Cell(40,7,'Periodo',1,0,'L',true);
        $this->SetFont('Arial','',9);
        $this->Cell(60,7,$periodo,1,0,'L');
        $this->SetFont('Arial','B',9);
        $this->Cell(40,7,'Hora',1,0,'L',true);
        $this->SetFont('Arial','',9);
        $this->Cell(50,7,$hora,1,1,'L');

        $this->Ln(6);

        // === ENCABEZADO DE LA TABLA PRINCIPAL ===
        $this->SetFont('Arial','B',9);
        $this->SetFillColor(241,230,225);
        $this->SetTextColor(74,46,34);
        $this->Cell(20,7,'Fecha',1,0,'C',true);
        $this->Cell(35,7,'Proveedor',1,0,'C',true);
        $this->Cell(25,7,'Comprobante',1,0,'C',true);
        $this->Cell(30,7,'Producto',1,0,'C',true);
        $this->Cell(25,7,'Categoria',1,0,'C',true);
        $this->Cell(15,7,'Cant',1,0,'C',true);
        $this->Cell(20,7,'Precio',1,0,'C',true);
        $this->Cell(20,7,'Subtotal',1,1,'C',true);
        $this->SetTextColor(0,0,0);
    }

    function Footer(){
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'El Adobe — Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}


// === Crear PDF ===
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

$total_general = 0;
while($r = $res->fetch_assoc()){
    $pdf->Cell(20,7,$r['fecha'],1,0,'C');
    $pdf->Cell(35,7,$r['proveedor'],1,0,'L');
    $pdf->Cell(25,7,$r['numero_comprobante'],1,0,'C');
    $pdf->Cell(30,7,$r['producto'],1,0,'L');
    $pdf->Cell(25,7,$r['categoria'],1,0,'L');
    $pdf->Cell(15,7,$r['cantidad'],1,0,'C');
    $pdf->Cell(20,7,number_format($r['precio_unitario'],2),1,0,'R');
    $pdf->Cell(20,7,number_format($r['subtotal'],2),1,1,'R');
    $total_general += (float)$r['subtotal'];
}

$pdf->Ln(4);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(190,8,'TOTAL GENERAL: Q '.number_format($total_general,2),0,1,'R');

ob_end_clean();
$pdf->Output('I','reporte_compras.pdf');
exit;
