<?php
// compras.php
session_start();

// Proteger acceso
if (!isset($_SESSION['usuario'])) {
    header("Location: ../loginEmpleados.php");
    exit();
}

$rol = $_SESSION['nombre_rol']; // Por ejemplo: "Mesero", "Administrador"
require_once '../conectar_bd.php';
$conexion = $conn;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Módulo de Compras</title>
  <link rel="stylesheet" href="style.css">

  <style>
  /* === Encabezado igual al de proveedores === */
  body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
  }

  header {
      background-color: #333;
      color: white;
      padding: 1rem 4rem; /* mismo que en proveedores */
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      box-sizing: border-box;
  }

  .company-name {
      font-size: 1.5rem;
  }

  .cerrarSesion {
      color: white;
      text-decoration: none;
      margin-left: 1.2rem;
  }

  .cerrarSesion:hover {
      color: #d4b28c;
  }

  /* 🟢 Margen para que el header no tape el contenido */
  .contenedor {
      margin-top: 7rem;
  }
  </style>
</head>
<body>

<header>
  <div class="company-name">El Adobe</div>
  <div>
      <a href="../menu_modulos.php" class="cerrarSesion">Inicio</a>
      <a href="../logout.php" class="cerrarSesion">Cerrar sesión</a>
  </div>
</header>

<div class="contenedor">
  <!-- Formulario de Compras -->
  <div class="formulario">
    <h2>Formulario de Compras</h2>

    <label>Proveedor</label>
    <div style="position: relative;">
      <input type="text" id="proveedor_buscar" placeholder="Buscar proveedor..." autocomplete="off">
      <input type="hidden" id="id_proveedor">
    </div>

    <div class="fila-2">
      <div>
        <label>Fecha</label>
        <input type="date" id="fecha" value="<?php echo date('Y-m-d'); ?>">
      </div>
      <div>
        <label>Método de Pago</label>
        <select id="metodo_pago">
          <option>Efectivo</option>
          <option>Transferencia</option>
          <option>Tarjeta</option>
          <option>Cheque</option>
        </select>
      </div>
    </div>

    <label>N° Comprobante</label>
    <input type="text" id="numero_comprobante" placeholder="Ej. FACT-00123">

    <hr>

    <h3>Detalle de Productos</h3>
    <div class="detalle-add">
      <input type="text" id="producto_buscar" placeholder="Buscar producto..." autocomplete="off">
      <input type="number" id="cantidad_add" min="1" value="1">
      <input type="text" id="precio_add" placeholder="Precio" readonly>
      <button id="btn_agregar">Agregar</button>
    </div>

    <div class="tabla-wrapper">
      <table id="tabla_detalle">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Subtotal</th>
            <th>Quitar</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="text-align:right;"><strong>Total</strong></td>
            <td id="total_general">0.00</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <button class="btn-primario" id="btn_guardar">Registrar Compra</button>
    <div id="msg_form" class="msg"></div>
  </div>

  <!-- Reporte -->
  <div class="lista">
    <h2>Reporte de Compras</h2>
    <button class="btn-exito" id="btn_toggle_reporte">Generar Reporte</button>

    <div id="panel_filtros" class="panel-filtros oculto">
      <div class="grid-filtros">
        <div>
          <label>Proveedor</label>
          <div style="position: relative;">
            <input type="text" id="rep_proveedor" placeholder="Buscar proveedor..." autocomplete="off">
            <input type="hidden" id="rep_proveedor_id">
          </div>
        </div>
        <div>
          <label>Fecha Inicio</label>
          <input type="date" id="rep_inicio">
        </div>
        <div>
          <label>Fecha Fin</label>
          <input type="date" id="rep_fin">
        </div>
        <div>
          <label>Producto</label>
          <input type="text" id="rep_producto" placeholder="Buscar producto..." autocomplete="off">
          <input type="hidden" id="rep_producto_id">
        </div>
        <div>
          <label>Categoría</label>
          <input type="text" id="rep_categoria" placeholder="Buscar categoría..." autocomplete="off">
        </div>
        <div>
          <label>N° Comprobante</label>
          <input type="text" id="rep_comprobante" placeholder="Ej. FACT-00123">
        </div>
      </div>
      <button id="btn_buscar_reporte" class="btn-primario">Buscar</button>
      <button id="btn_limpiar_reporte" class="btn-secundario">Limpiar</button>
    </div>

    <div class="tabla-wrapper">
      <table id="tabla_reporte">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Proveedor</th>
            <th>Comprobante</th>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Subtotal</th>
            <th>Total Compra</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <td colspan="8" style="text-align:right;"><strong>Total Reporte</strong></td>
            <td id="rep_total">0.00</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div style="text-align:right; margin-top:10px;">
      <form action="generar_pdf_compras.php" method="GET" target="_blank" id="form_pdf">
        <input type="hidden" name="proveedor_id" id="pdf_proveedor_id">
        <input type="hidden" name="producto_id" id="pdf_producto_id">
        <input type="hidden" name="categoria" id="pdf_categoria">
        <input type="hidden" name="comprobante" id="pdf_comprobante">
        <input type="hidden" name="inicio" id="pdf_inicio">
        <input type="hidden" name="fin" id="pdf_fin">
        <button type="submit" class="btn-exito">🖨️ Imprimir PDF</button>
      </form>
    </div>

    <div id="msg_reporte" class="msg"></div>
  </div>
</div>

<script src="script_compras.js"></script>
</body>
</html>
