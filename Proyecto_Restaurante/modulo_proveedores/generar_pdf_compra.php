<?php
ob_start(); // Evita que cualquier salida previa dañe el PDF

require('fpdf/fpdf.php');
include 'conectabd.php'; // conexión MySQLi

if (!isset($_GET['id'])) {
    die('Error: No se proporcionó ID de compra.');
}

$id_compra = intval($_GET['id']);

// Obtener datos de la compra
$stmt = $conexion->prepare("SELECT * FROM compras_proveedores WHERE id_compra = ?");
$stmt->bind_param("i", $id_compra);
$stmt->execute();
$result = $stmt->get_result();
$compra = $result->fetch_assoc();

if (!$compra) {
    die('Error: No se encontró la compra.');
}

// Obtener detalle de productos
$stmt_detalle = $conexion->prepare("SELECT * FROM detalle_compra WHERE id_compra = ?");
$stmt_detalle->bind_param("i", $id_compra);
$stmt_detalle->execute();
$detalles = $stmt_detalle->get_result();

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Factura de Compra', 0, 1, 'C');

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 8, 'Proveedor: ' . $compra['nombre_proveedor'], 0, 1);
$pdf->Cell(100, 8, 'Fecha: ' . $compra['fecha_compra'], 0, 1);
$pdf->Cell(100, 8, 'Metodo de Pago: ' . $compra['metodo_pago'], 0, 1);
$pdf->Cell(100, 8, 'Numero de Comprobante: ' . $compra['numero_comprobante'], 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 8, 'Producto', 1, 0, 'C');
$pdf->Cell(30, 8, 'Cantidad', 1, 0, 'C');
$pdf->Cell(40, 8, 'Precio Unitario', 1, 0, 'C');
$pdf->Cell(40, 8, 'Subtotal', 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
while ($fila = $detalles->fetch_assoc()) {
    // Elimina utf8_decode (no se necesita en PHP 8)
    $pdf->Cell(70, 8, $fila['producto'], 1);
    $pdf->Cell(30, 8, $fila['cantidad'], 1, 0, 'C');
    $pdf->Cell(40, 8, 'Q ' . number_format($fila['precio_unitario'], 2), 1, 0, 'C');
    $pdf->Cell(40, 8, 'Q ' . number_format($fila['subtotal'], 2), 1, 1, 'C');
}

$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 8, 'Subtotal:', 0, 0, 'R');
$pdf->Cell(40, 8, 'Q ' . number_format($compra['subtotal'], 2), 0, 1, 'R');
$pdf->Cell(140, 8, 'Total:', 0, 0, 'R');
$pdf->Cell(40, 8, 'Q ' . number_format($compra['total'], 2), 0, 1, 'R');

ob_end_clean(); // Limpia cualquier salida previa antes de generar el PDF
$pdf->Output('I', 'factura_compra.pdf');
exit;
?>
