<?php
// --- START: Replace DB connect block with sample-mode + read-only option ---
$USE_SAMPLE = (isset($_GET['sample']) && $_GET['sample'] === '1'); // ?sample=1 para modo prueba
$READ_ONLY = false;                 // poner true si quieres forzar user read-only
$DB_USER_READONLY = 'report_user';  // usuario de solo lectura si existe (configurar fuera)

// Añadido: asegurar que $debugMessages y $debugConnected existen para evitar warnings y reportar estado
$debugMessages = $debugMessages ?? [];
$debugConnected = false;

// credenciales por defecto (ajustar)
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = 'Acces0F3R';
$DB_NAME = 'eladobe';

if ($USE_SAMPLE) {
	// modo muestra: no tocar BD, usar datos de ejemplo
	$meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
	$datosVentas = [
		['mes'=>1,'total'=>1200],['mes'=>2,'total'=>1500],['mes'=>3,'total'=>900],
		['mes'=>4,'total'=>2000],['mes'=>5,'total'=>1800],['mes'=>6,'total'=>0],
		['mes'=>7,'total'=>400],['mes'=>8,'total'=>0],['mes'=>9,'total'=>600],
		['mes'=>10,'total'=>1200],['mes'=>11,'total'=>2300],['mes'=>12,'total'=>500],
	];
	$datosTop = [
		['nombre'=>'Carne Adobada','total'=>900],
		['nombre'=>'Jocón de Pollo','total'=>700],
		['nombre'=>'Enchilada','total'=>400],
		['nombre'=>'Ceviche de Camarón','total'=>350],
		['nombre'=>'Hamburguesa','total'=>300],
	];
	$datosSucursales = [
		['sucursal'=>'Mesa 5','total'=>2500],
		['sucursal'=>'Mesa 1','total'=>1200],
		['sucursal'=>'Mesa 2','total'=>900],
	];
	$productosCriticos = [
		['nombre'=>'Tomate','stock'=>2,'minimo'=>5],
		['nombre'=>'Lechuga','stock'=>1,'minimo'=>4],
		['nombre'=>'Arroz','stock'=>8,'minimo'=>10],
	];
	$productosSinVenta = ['Refresco Natural','Panqueques','Jugo de Mango'];
	$totalVentas = array_sum(array_column($datosVentas,'total'));
	$ticketPromedio = $totalVentas > 0 ? $totalVentas / max(1,count($datosVentas)) : 0;
	$porcentajeBajoStock = round((count($productosCriticos) / 8) * 100, 1); // ejemplo
	$debugMessages[] = "Modo prueba activo (sample=1). No se realizó conexión a la BD.";
	$mysqli = null;
	$debugConnected = false;
} else {
	// modo normal: conectar a BD (opcionalmente con usuario read-only)
	if ($READ_ONLY && !empty($DB_USER_READONLY)) {
		$DB_USER = $DB_USER_READONLY;
	}

	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
	if ($mysqli->connect_errno) {
		$debugMessages[] = "Error conexión MySQL: " . $mysqli->connect_error;
		// fallback a estructuras vacías (ya estaba)
		$meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
		$datosVentas = [];
		for ($i=1;$i<=12;$i++) $datosVentas[] = ['mes' => $i, 'total' => 0.0];
		$datosTop = [];
		$datosSucursales = [];
		$productosCriticos = [];
		$productosSinVenta = [];
		$totalVentas = 0.0;
		$ticketPromedio = 0.0;
		$porcentajeBajoStock = 0.0;
	} else {
		// conexión exitosa: marcar debug y notificar
		$debugConnected = true;
		$debugMessages[] = "Conexión MySQL exitosa a {$DB_HOST} como usuario {$DB_USER}.";

		// preparar meses
		$meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
		// Inicializar 12 meses con total 0
		$datosVentas = [];
		for ($i=1;$i<=12;$i++) $datosVentas[] = ['mes' => $i, 'total' => 0.0];

		// 1) Ventas por mes (año actual) -> sobreescribir meses iniciales
		$sql = "SELECT MONTH(fecha) AS mes, IFNULL(SUM(total),0) AS total
		        FROM ventas
		        WHERE YEAR(fecha) = YEAR(CURDATE())
		        GROUP BY MONTH(fecha)
		        ORDER BY MONTH(fecha)";
		if ($res = $mysqli->query($sql)) {
			while ($row = $res->fetch_assoc()) {
				$idx = (int)$row['mes'] - 1;
				if ($idx >= 0 && $idx < 12) $datosVentas[$idx]['total'] = (float)$row['total'];
			}
			$res->free();
		} else {
			$debugMessages[] = "Error consulta ventas por mes: " . $mysqli->error;
			error_log("SQL Error ventas por mes: ".$mysqli->error);
		}

		// 2) Top 5 productos por monto vendido
		$datosTop = [];
		$sql = "SELECT m.nombre, IFNULL(SUM(dv.subtotal),0) AS total_amount
		        FROM detalle_venta dv
		        JOIN menu m ON dv.id_menu = m.id_menu
		        GROUP BY m.id_menu
		        ORDER BY total_amount DESC
		        LIMIT 5";
		if ($res = $mysqli->query($sql)) {
			while ($row = $res->fetch_assoc()) {
				$datosTop[] = ['nombre' => $row['nombre'], 'total' => (float)$row['total_amount']];
			}
			$res->free();
		} else {
			$debugMessages[] = "Error consulta top productos: " . $mysqli->error;
			error_log("SQL Error top productos: ".$mysqli->error);
		}

		// 3) Ventas por "sucursal" -> usar número de mesa como proxy
		$datosSucursales = [];
		// Agrupar por el número de mesa (m.numero) para cumplir con ONLY_FULL_GROUP_BY
		// y mostrar 'Sin Mesa' cuando no exista número (m.numero IS NULL)
		$sql = "SELECT CASE WHEN m.numero IS NULL THEN 'Sin Mesa' ELSE CONCAT('Mesa ', m.numero) END AS sucursal,
		               IFNULL(SUM(v.total),0) AS total
		        FROM ventas v
		        LEFT JOIN mesas m ON v.id_mesa = m.id_mesa
		        GROUP BY m.numero
		        ORDER BY total DESC
		        LIMIT 10";
		if ($res = $mysqli->query($sql)) {
			while ($row = $res->fetch_assoc()) {
				$datosSucursales[] = ['sucursal' => $row['sucursal'] ?: 'Sin Mesa', 'total' => (float)$row['total']];
			}
			$res->free();
		} else {
			$debugMessages[] = "Error consulta ventas por sucursal: " . $mysqli->error;
			error_log("SQL Error ventas por sucursal: ".$mysqli->error);
		}

		// 4) Productos críticos (agregar comestibles + mobiliario) -> unificar resultados y ordenar
		$productosCriticos = [];
		// Solo retornar productos cuyo stock está por debajo o igual al mínimo
		$sql = "
		  SELECT nombre, stock, stock_minimo FROM (
		    SELECT p.nombre AS nombre, c.stock AS stock, c.stock_minimo AS stock_minimo
		      FROM productos p
		      JOIN comestibles c ON p.id_producto = c.id_producto
		    UNION ALL
		    SELECT p.nombre AS nombre, m.stock AS stock, m.stock_minimo AS stock_minimo
		      FROM productos p
		      JOIN mobiliario_equipo m ON p.id_producto = m.id_producto
		  ) AS combined
		  WHERE stock IS NOT NULL AND stock <= stock_minimo
		  ORDER BY stock ASC
		  LIMIT 20
		";
		if ($res = $mysqli->query($sql)) {
			while ($row = $res->fetch_assoc()) {
				// Normalizar clave 'minimo' usada en la UI
				$productosCriticos[] = ['nombre' => $row['nombre'], 'stock' => (float)$row['stock'], 'minimo' => (float)$row['stock_minimo']];
			}
			$res->free();
		} else {
			$debugMessages[] = "Error consulta productos críticos: " . $mysqli->error;
			error_log("SQL Error productos críticos: ".$mysqli->error);
		}

		// 5) Productos sin venta en los últimos 30 días
		$productosSinVenta = [];
		$sql = "SELECT m.nombre
		        FROM menu m
		        WHERE m.id_menu NOT IN (
		          SELECT dv.id_menu
		          FROM detalle_venta dv
		          JOIN ventas v ON dv.id_venta = v.id_venta
		          WHERE v.fecha >= NOW() - INTERVAL 30 DAY
		        )";
		if ($res = $mysqli->query($sql)) {
			while ($row = $res->fetch_assoc()) {
				$productosSinVenta[] = $row['nombre'];
			}
			$res->free();
		} else {
			$debugMessages[] = "Error consulta productos sin venta: " . $mysqli->error;
			error_log("SQL Error productos sin venta: ".$mysqli->error);
		}

		// 6) KPI: total ventas y ticket promedio para el mes actual
		$totalVentas = 0.0;
		$ticketPromedio = 0.0;
		$sql = "SELECT IFNULL(SUM(total),0) AS total_mes, IFNULL(AVG(total),0) AS ticket_promedio
		        FROM ventas
		        WHERE MONTH(fecha) = MONTH(CURDATE())
		          AND YEAR(fecha) = YEAR(CURDATE())";
		if ($res = $mysqli->query($sql)) {
			$row = $res->fetch_assoc();
			$totalVentas = (float)$row['total_mes'];
			$ticketPromedio = (float)$row['ticket_promedio'];
			$res->free();
		} else {
			$debugMessages[] = "Error consulta KPI ventas: " . $mysqli->error;
			error_log("SQL Error KPI ventas: ".$mysqli->error);
		}

		// 7) Porcentaje de comestibles bajo stock
		$porcentajeBajoStock = 0.0;
		$sql = "SELECT
		          (SELECT COUNT(*) FROM comestibles WHERE stock <= stock_minimo) AS bajo,
		          (SELECT COUNT(*) FROM comestibles) AS total
		        ";
		if ($res = $mysqli->query($sql)) {
			$row = $res->fetch_assoc();
			$bajo = (int)$row['bajo'];
			$total = (int)$row['total'];
			if ($total > 0) $porcentajeBajoStock = round(($bajo / $total) * 100, 1);
			$res->free();
		} else {
			$debugMessages[] = "Error consulta porcentaje bajo stock: " . $mysqli->error;
			error_log("SQL Error porcentaje bajo stock: ".$mysqli->error);
		}

		$mysqli->close();
	}
}

// --- Added: asegurar variables y calcular totales para evitar "Undefined variable" ---
$datosVentas = $datosVentas ?? [];
$datosTop = $datosTop ?? [];
$datosSucursales = $datosSucursales ?? [];

// Sumas seguras (devuelven 0 si no hay datos)
$sumVentas = (float) array_sum(array_column($datosVentas, 'total'));
$sumTop    = (float) array_sum(array_column($datosTop, 'total'));
$sumSuc    = (float) array_sum(array_column($datosSucursales, 'total'));

// --- Added: flags para UI (habilitar botones, etc.) ---
$hasVentas = $sumVentas > 0;
$hasInventario = !empty($productosCriticos);
$hasNoSales = !empty($productosSinVenta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard BI (Prueba)</title>
  <style>
    /* --- Mejoras de estilo: layout, tarjetas, tablas, responsivo --- */
    :root{
      --bg:#f5f7fb;
      --card:#ffffff;
      --muted:#6b7280;
      --accent:#2563eb;
      --success:#16a34a;
      --danger:#ef4444;
      --radius:12px;
      --shadow: 0 6px 18px rgba(20,20,40,0.06);
    }
    *{box-sizing:border-box}
    body {
      font-family: Inter, 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial;
      background: var(--bg);
      margin: 0;
      padding: 2rem;
      color: #111827;
    }
    .page-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.25rem; }
    h1 { font-size:1.5rem; margin:0; color:#0f172a; }
    .kpi-grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap:1rem;
      margin-bottom:1.25rem;
    }
    .kpi {
      background: var(--card);
      padding:1rem;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      display:flex;
      align-items:center;
      gap:0.75rem;
      border-left:4px solid rgba(37,99,235,0.08);
    }
    .kpi .meta { font-size:0.85rem; color:var(--muted) }
    .kpi .value { font-size:1.25rem; font-weight:700; color:var(--accent) }
    .kpi .icon {
      width:44px;height:44px;border-radius:10px;
      background:linear-gradient(135deg, rgba(37,99,235,0.08), rgba(37,99,235,0.02));
      display:flex;align-items:center;justify-content:center;font-size:1.15rem;color:var(--accent)
    }

    .dashboard-grid {
      display:grid;
      grid-template-columns: 2fr 1fr;
      gap:1rem;
      align-items:start;
      margin-bottom:1.25rem;
    }

    .card {
      background:var(--card);
      padding:1rem;
      border-radius:var(--radius);
      box-shadow: var(--shadow);
    }
    .card h3 { margin:0 0 0.75rem 0; font-size:1.02rem; color:#0f172a }
    .card .small { color:var(--muted); font-size:0.9rem; margin-bottom:0.5rem }

    /* Charts container auto-resize */
    .chart-wrap { width:100%; height:320px; display:flex; align-items:center; justify-content:center; }
    canvas { max-height:100% !important; }

    /* Right column stacks */
    .stack { display:flex; flex-direction:column; gap:1rem; }
    .mini-chart { height:180px; }

    /* Tables */
    table { width:100%; border-collapse:collapse; font-size:0.92rem; }
    th, td { text-align:left; padding:0.5rem 0.6rem; border-bottom:1px solid #eef2f7; }
    th { font-weight:600; color:var(--muted); font-size:0.85rem; }
    tbody tr:hover { background: linear-gradient(90deg, rgba(37,99,235,0.02), transparent); }

    .badge { display:inline-block; padding:0.18rem 0.45rem; border-radius:999px; font-size:0.78rem; color:white; background:var(--accent) }
    .muted { color:var(--muted); font-size:0.9rem; }

    /* Inventario crítico */
    .critico { color: var(--danger); font-weight:700; }
    .ok { color:var(--success); font-weight:700; }

    /* INVENTARIO CRÍTICO: cards + progress */
    .inventario-grid {
      display:grid;
      grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
      gap:1rem;
      margin-top:0.75rem;
    }
    .inv-card {
      background: linear-gradient(180deg, #ffffff, #fbfdff);
      border-radius:10px;
      padding:0.8rem;
      box-shadow: 0 6px 18px rgba(15,23,42,0.06);
      display:flex;
      flex-direction:column;
      gap:0.5rem;
      border-left:4px solid rgba(239,68,68,0.08);
    }
    .inv-head { display:flex; justify-content:space-between; align-items:center; gap:0.5rem; }
    .inv-name { font-weight:700; color:#0f172a; }
    .inv-meta { font-size:0.85rem; color:var(--muted); }

    .progress-wrap { background:#f1f5f9; height:10px; border-radius:999px; overflow:hidden; width:100%; }
    .progress-fill { height:100%; background:linear-gradient(90deg,#ef4444,#f97316); width:0%; transition:width .6s ease; }

    /* Pills productos sin venta */
    .no-sales-wrap { display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.5rem; }
    .pill {
      display:inline-flex; align-items:center; gap:0.5rem;
      padding:0.35rem 0.6rem; border-radius:999px; background:#eef2ff; color:#0f172a;
      font-size:0.9rem; box-shadow: inset 0 -1px 0 rgba(0,0,0,0.03);
    }
    .pill .dot { width:8px; height:8px; border-radius:50%; background:#f59e0b; display:inline-block; }

    /* Botones export: estilos más grandes y estado disabled */
    .actions { display:flex; gap:0.5rem; align-items:center; }
    .btn {
      border:0; padding:0.6rem 0.9rem; border-radius:8px; cursor:pointer; font-weight:600;
      display:inline-flex; align-items:center; gap:0.5rem; background:var(--accent); color:#fff; box-shadow: 0 6px 18px rgba(37,99,235,0.12);
    }
    .btn.secondary { background:#10b981; box-shadow: 0 6px 18px rgba(16,185,129,0.08); }
    .btn.ghost { background:transparent;color:var(--muted); border:1px solid #e6eefc; box-shadow:none; }
    .btn[disabled] { opacity:0.55; cursor:not-allowed; box-shadow:none; filter:grayscale(.05); }

    /* Small helper */
    .muted-sm { color:var(--muted); font-size:0.9rem; }

    /* Responsive */
    @media (max-width:900px){
      .dashboard-grid { grid-template-columns: 1fr; }
      .chart-wrap { height:260px; }
      .mini-chart { height:160px; }
    }
  </style>
</head>
<body>

  <div class="page-header">
    <h1>Dashboard BI — Resumen</h1>
    <!-- pequeño subtítulo con fecha (ahora dinámico) -->
    <div>
      <div class="muted">Actualizado: <span id="serverTime"><?= date('d/m/Y H:i:s') ?></span></div>
      <!-- Indicador de estado de conexión -->
      <div class="muted" style="margin-top:6px;">
        Estado BD:
        <strong style="color:<?= $debugConnected ? '#16a34a' : ($USE_SAMPLE ? '#f59e0b' : '#ef4444') ?>;">
          <?= $debugConnected ? 'Conectado' : ($USE_SAMPLE ? 'Modo muestra' : 'No conectado') ?>
        </strong>
        <?php if (!empty($debugMessages)): ?>
          <details style="display:inline-block;margin-left:8px;">
            <summary style="cursor:pointer;font-size:0.9rem;color:var(--muted)">Detalles</summary>
            <ul style="margin:6px 0 0 18px;padding:0;color:var(--muted)">
              <?php foreach ($debugMessages as $m): ?>
                <li><?= htmlspecialchars($m) ?></li>
              <?php endforeach; ?>
            </ul>
          </details>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- KPIs: ahora en grid con iconos -->
  <div class="kpi-grid">
    <div id="kpiTotalVentas" class="kpi card" role="button" title="Ver detalle ventas" onclick="focusSection('graficoVentas')">
      <div class="icon">💰</div>
      <div>
        <div class="meta">Total Ventas (mes)</div>
        <div class="value"><span id="kpiTotalValor">Q<?= number_format($totalVentas, 2) ?></span></div>
      </div>
    </div>

    <div id="kpiTicket" class="kpi card" role="button" title="Ver detalle ticket" onclick="focusSection('graficoTop')">
      <div class="icon">🧾</div>
      <div>
        <div class="meta">Ticket Promedio</div>
        <div class="value"><span id="kpiTicketValor">Q<?= number_format($ticketPromedio, 2) ?></span></div>
      </div>
    </div>

    <div id="kpiBajoStock" class="kpi card" role="button" title="Ir a inventario crítico" onclick="focusSection('inventarioGrid')">
      <div class="icon">📦</div>
      <div>
        <div class="meta">% Productos bajo stock</div>
        <div class="value"><span id="kpiBajoStockValor"><?= round($porcentajeBajoStock,1) ?>%</span></div>
      </div>
    </div>
  </div>

  <!-- Gráficos y tablas resumen -->
  <div class="dashboard-grid">
    <!-- Izquierda: gráfico principal Ventas por mes -->
    <div class="card">
      <h3>Ventas por Mes</h3>
      <div class="small">Vista anual — los meses sin ventas están en cero</div>
      <?php if ($sumVentas == 0): ?>
        <p class="muted">No hay ventas registradas en el año actual.</p>
      <?php endif; ?>
      <div class="chart-wrap">
        <canvas id="graficoVentas"></canvas>
      </div>
    </div>

    <!-- Derecha: Top productos + Ventas por sucursal (cada uno con mini tabla) -->
    <div class="stack">
      <div class="card">
        <h3>Top 5 Productos</h3>
        <div class="small">Monto total vendido</div>
        <div class="mini-chart chart-wrap"><canvas id="graficoTop"></canvas></div>
        <?php if (!empty($datosTop)): ?>
          <table>
            <thead><tr><th>Producto</th><th style="text-align:right">Total</th></tr></thead>
            <tbody>
              <?php foreach ($datosTop as $t): ?>
                <tr>
                  <td><?= htmlspecialchars($t['nombre']) ?></td>
                  <td style="text-align:right">Q<?= number_format($t['total'],2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
      <?php else: ?>
        <p class="muted">No hay datos de productos para mostrar.</p>
      <?php endif; ?>
      </div>

      <div class="card">
        <h3>Ventas por Sucursal</h3>
        <div class="small">Por mesa (ordenadas por monto)</div>
        <div class="mini-chart chart-wrap"><canvas id="graficoSucursales"></canvas></div>
        <?php if (!empty($datosSucursales)): ?>
          <table>
            <thead><tr><th>Sucursal</th><th style="text-align:right">Total</th></tr></thead>
            <tbody>
              <?php foreach ($datosSucursales as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s['sucursal']) ?></td>
                  <td style="text-align:right">Q<?= number_format($s['total'],2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="muted">No hay ventas por sucursal para mostrar.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Inventario crítico: tarjetas con barra de progreso -->
  <div class="card">
    <h3>Inventario Crítico</h3>
    <div class="small">Productos cercanos o por debajo del stock mínimo</div>

    <!-- Siempre renderizar el contenedor para que JS lo pueda poblar -->
    <div id="inventarioGrid" class="inventario-grid">
      <?php if (empty($productosCriticos)): ?>
        <div class="muted">No hay productos críticos en inventario.</div>
      <?php else: ?>
        <?php foreach ($productosCriticos as $p):
          $ratio = ($p['minimo'] > 0) ? min(1, $p['stock'] / $p['minimo']) : 0;
          $percent = round($ratio * 100);
          $state = ($p['stock'] <= $p['minimo']) ? 'Crítico' : 'Bajo';
        ?>
          <div class="inv-card" data-name="<?= htmlspecialchars($p['nombre']) ?>" data-stock="<?= $p['stock'] ?>" data-min="<?= $p['minimo'] ?>">
            <div class="inv-head">
              <div>
                <div class="inv-name"><?= htmlspecialchars($p['nombre']) ?></div>
                <div class="inv-meta"><?= $state ?> • Stock: <?= number_format($p['stock'], ($p['stock'] == (int)$p['stock'] ? 0 : 2)) ?> / Mín: <?= number_format($p['minimo'], ($p['minimo'] == (int)$p['minimo'] ? 0 : 2)) ?></div>
              </div>
              <div class="muted-sm"><?= $percent ?>%</div>
            </div>
            <div class="progress-wrap" aria-hidden="true">
              <div class="progress-fill" style="width:<?= $percent ?>%;"></div>
            </div>
            <div class="muted-sm">Recomendación: <?php echo ($p['stock'] <= $p['minimo']) ? 'Solicitar pedido al proveedor' : 'Monitorear consumo'; ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Productos sin venta: pills + acciones -->
  <div class="card">
    <h3>Productos sin venta (30+ días)</h3>
    <div class="small">Items que no han registrado venta recientemente</div>

    <?php if (empty($productosSinVenta)): ?>
      <p class="muted">No se detectaron productos sin venta en los últimos 30 días.</p>
    <?php else: ?>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem;">
        <div id="noSalesCount" class="muted-sm"><?= count($productosSinVenta) ?> productos</div>
        <div class="actions">
          <button class="btn ghost" onclick="copyNoSales()">📋 Copiar lista</button>
          <button class="btn secondary" onclick="markReviewedNoSales()">✔️ Marcar revisado</button>
        </div>
      </div>

      <div id="noSalesList" class="no-sales-wrap" style="margin-top:0.75rem;">
        <?php foreach ($productosSinVenta as $nombre): ?>
          <span class="pill"><span class="dot"></span><?= htmlspecialchars($nombre) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Modo cliente deshabilitado: la página usa exclusivamente datos provenientes del servidor/BD -->

  <!-- Exportación: botones mejorados -->
  <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:0.5rem;">
    <button id="btnPdf" class="btn" onclick="exportarVentasPDF()" <?= $hasVentas ? '' : 'disabled' ?>>📄 Exportar PDF Ventas</button>
    <button id="btnExcel" class="btn secondary" onclick="exportarInventarioExcel()" <?= $hasInventario ? '' : 'disabled' ?>>📊 Exportar Inventario</button>
  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script>
    const opcionesGrafico = {
      responsive: true,
      plugins: {
        legend: { labels: { color: '#333', font: { size: 14 } } },
        title: { display: false }
      },
      scales: {
        x: { ticks: { color: '#555', font: { size: 12 } }, grid: { color: '#eee' } },
        y: { ticks: { color: '#555', font: { size: 12 } }, grid: { color: '#eee' } }
      }
    };

    // create and keep references to charts
    window.chartVentas = new Chart(document.getElementById('graficoVentas'), {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_map(fn($d) => $meses[$d['mes']-1], $datosVentas)) ?>,
        datasets: [{ label: 'Ventas por mes', data: <?= json_encode(array_column($datosVentas, 'total')) ?>, backgroundColor: '#4CAF50' }]
      },
      options: opcionesGrafico
    });

    window.chartTop = new Chart(document.getElementById('graficoTop'), {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($datosTop, 'nombre')) ?>,
        datasets: [{ label: 'Top productos', data: <?= json_encode(array_column($datosTop, 'total')) ?>, backgroundColor: '#FF9800' }]
      },
      options: opcionesGrafico
    });

    window.chartSuc = new Chart(document.getElementById('graficoSucursales'), {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($datosSucursales, 'sucursal')) ?>,
        datasets: [{ label: 'Ventas por sucursal', data: <?= json_encode(array_column($datosSucursales, 'total')) ?>, backgroundColor: '#3498db' }]
      },
      options: opcionesGrafico
    });

    // Exportar ventas a PDF
    async function exportarVentasPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.text("Reporte de Ventas del Mes", 10, 10);
      <?php foreach ($datosVentas as $i => $v): ?>
        doc.text("Mes <?= $v['mes'] ?>: Q<?= number_format($v['total'], 2) ?>", 10, <?= 20 + $i * 10 ?>);
      <?php endforeach; ?>
      doc.save("ventas_mes.pdf");
    }

    // Exportar inventario a Excel
    function exportarInventarioExcel() {
      const datos = [
        ["Producto", "Stock", "Mínimo"],
        <?php foreach ($productosCriticos as $p): ?>
          ["<?= $p['nombre'] ?>", <?= $p['stock'] ?>, <?= $p['minimo'] ?>],
        <?php endforeach; ?>
      ];
      const hoja = XLSX.utils.aoa_to_sheet(datos);
      const libro = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(libro, hoja, "Inventario Crítico");
      XLSX.writeFile(libro, "inventario_critico.xlsx");
    }

    // Eliminado: código de "sample" cliente. Los charts y exportaciones usan únicamente los arrays generados por PHP/MySQL.
 
    // Guardar referencias a charts
    const chartVentas = Chart.getChart('graficoVentas');
    const chartTop = Chart.getChart('graficoTop');
    const chartSuc = Chart.getChart('graficoSucursales');

    // Ajustes a export functions: verificar deshabilitado
    const originalExportPdf = exportarVentasPDF;
    exportarVentasPDF = function() {
      if (document.getElementById('btnPdf').disabled) return alert('No hay datos de ventas para exportar');
      originalExportPdf();
    };

    const originalExportExcel = exportarInventarioExcel;
    exportarInventarioExcel = function() {
      if (document.getElementById('btnExcel').disabled) return alert('No hay inventario crítico para exportar');
      originalExportExcel();
    };

    // Valores iniciales desde servidor (PHP -> JS)
    const serverData = {
      totalVentas: <?= json_encode((float)$totalVentas) ?>,
      ticketPromedio: <?= json_encode((float)$ticketPromedio) ?>,
      porcentajeBajoStock: <?= json_encode((float)$porcentajeBajoStock) ?>,
      productosCriticosCount: <?= json_encode(count($productosCriticos)) ?>,
      productosSinVentaCount: <?= json_encode(count($productosSinVenta)) ?>
    };

    // Actualiza la visualización de KPIs
    function updateKPIs(data = {}) {
      const total = (typeof data.totalVentas !== 'undefined') ? data.totalVentas : serverData.totalVentas;
      const ticket = (typeof data.ticketPromedio !== 'undefined') ? data.ticketPromedio : serverData.ticketPromedio;
      const pct = (typeof data.porcentajeBajoStock !== 'undefined') ? data.porcentajeBajoStock : serverData.porcentajeBajoStock;

      document.getElementById('kpiTotalValor').textContent = 'Q' + Number(total).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
      document.getElementById('kpiTicketValor').textContent = 'Q' + Number(ticket).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
      document.getElementById('kpiBajoStockValor').textContent = Number(pct).toFixed(1) + '%';

      // actualizar habilitación de botones según datos
      const btnPdf = document.getElementById('btnPdf');
      const btnExcel = document.getElementById('btnExcel');
      if (btnPdf) btnPdf.disabled = !(Number(total) > 0);
      if (btnExcel) btnExcel.disabled = !( (data.productosCriticosCount ?? serverData.productosCriticosCount) > 0 );
    }

    // Actualiza contador de "Productos sin venta"
    function updateNoSalesCount(count) {
      const el = document.getElementById('noSalesCount');
      if (el) el.textContent = (Number(count) || 0) + ' productos';
    }

    // Función para resaltar / desplazar a una sección desde KPI
    function focusSection(elementId) {
      const el = document.getElementById(elementId);
      if (!el) return;
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      // animación rápida de resaltado
      el.style.transition = 'box-shadow 0.4s ease';
      const prev = el.style.boxShadow;
      el.style.boxShadow = '0 0 0 4px rgba(59,130,246,0.15)';
      setTimeout(()=> el.style.boxShadow = prev || 'none', 900);
    }

    // Actualizar reloj en tiempo real (cliente)
    function startClock() {
      const el = document.getElementById('serverTime');
      if (!el) return;
      function tick() {
        const now = new Date();
        const dd = String(now.getDate()).padStart(2,'0');
        const mm = String(now.getMonth()+1).padStart(2,'0');
        const yy = now.getFullYear();
        const hh = String(now.getHours()).padStart(2,'0');
        const mi = String(now.getMinutes()).padStart(2,'0');
        const ss = String(now.getSeconds()).padStart(2,'0');
        el.textContent = `${dd}/${mm}/${yy} ${hh}:${mi}:${ss}`;
      }
      tick();
      setInterval(tick, 1000);
    }

    // Hook para que al cargar sample data la UI se actualice
    function afterDataChange(newData = {}) {
      // newData puede contener datos: datosVentas (array), productosCriticos (array), productosSinVenta (array), ticketPromedio
      if (newData.datosVentas) {
        const total = newData.datosVentas.reduce((s,v)=>s + (v.total||0), 0);
        // actualizar KPIs usando total y posible ticket
        updateKPIs({
          totalVentas: total,
          ticketPromedio: newData.ticketPromedio ?? serverData.ticketPromedio,
          porcentajeBajoStock: newData.porcentajeBajoStock ?? serverData.porcentajeBajoStock,
          productosCriticosCount: (newData.productosCriticos ? newData.productosCriticos.length : serverData.productosCriticosCount)
        });
      }
      if (newData.productosSinVenta) {
        updateNoSalesCount(newData.productosSinVenta.length);
      } else {
        updateNoSalesCount(serverData.productosSinVentaCount);
      }
      // tambien actualizar botones de export si existen
      const btnPdf = document.getElementById('btnPdf');
      const btnExcel = document.getElementById('btnExcel');
      if (btnPdf) btnPdf.disabled = !( (newData.datosVentas ? newData.datosVentas.reduce((s,v)=>s+v.total,0) : serverData.totalVentas) > 0 );
      if (btnExcel) btnExcel.disabled = !( (newData.productosCriticos ? newData.productosCriticos.length : serverData.productosCriticosCount) > 0 );
    }

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', function(){
      startClock();
      updateKPIs(); // usar datos del servidor
      updateNoSalesCount(serverData.productosSinVentaCount);
    });
  </script>

</body>
</html>
