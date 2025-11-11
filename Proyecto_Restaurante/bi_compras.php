<?php
// --- START: session + acceso protegido (integrado con la lógica existente) ---
session_start();
// --- START: No redirigir al login; permitir acceso aunque no haya sesión ---
$usuario = $_SESSION['usuario'] ?? null;
$loggedIn = $usuario !== null;
$rol = $_SESSION['nombre_rol'] ?? ''; // ejemplo: "Mesero", "Administrador"

// --- START: Replace DB connect block with sample-mode + read-only option ---
$USE_SAMPLE = (isset($_GET['sample']) && $_GET['sample'] === '1'); // ?sample=1 para modo prueba
$READ_ONLY = false;                 // poner true si quieres forzar user read-only
$DB_USER_READONLY = 'report_user';  // usuario de solo lectura si existe (configurar fuera)

// Añadido: asegurar que $debugMessages y $debugConnected existen para evitar warnings y reportar estado
$debugMessages = $debugMessages ?? [];
$debugConnected = false;

if ($USE_SAMPLE) {
	// Modo muestra: datos de ejemplo centrados en compras/proveedores
	$comprasRecientes = [
		['id'=>101,'fecha'=>'2025-10-01','proveedor'=>'Distribuidora de Alimentos SA','total'=>1200.50,'comprobante'=>'FAC-2025-001'],
		['id'=>102,'fecha'=>'2025-10-03','proveedor'=>'Carnicería El Buen Corte','total'=>850.00,'comprobante'=>'FAC-2025-002'],
		['id'=>103,'fecha'=>'2025-10-05','proveedor'=>'Verdulería Fresca','total'=>420.75,'comprobante'=>'FAC-2025-003'],
	];
	$proveedores = [
		['id'=>1,'nombre'=>'Distribuidora de Alimentos SA','contacto'=>'Carlos Martínez','telefono'=>'2233-4455','correo'=>'ventas@dalimentos.com','estado'=>'Activo'],
		['id'=>2,'nombre'=>'Carnicería El Buen Corte','contacto'=>'Ana López','telefono'=>'5544-6677','correo'=>'pedidos@elbuencorte.com','estado'=>'Activo'],
	];
	$gastosMes = array_sum(array_column($comprasRecientes,'total'));
	$numComprasMes = count($comprasRecientes);
	$debugMessages[] = "Modo prueba activo (sample=1). Usando datos ficticios de compras/proveedores.";
	$mysqli = null;
	$debugConnected = false;
} else {
	// Intentar varias rutas para el archivo de conexión
	$connLoaded = false;
	$possible = [
		__DIR__ . '/conectar_bd.php',
		__DIR__ . '/conectabd.php',
		__DIR__ . '/../conectar_bd.php',
		__DIR__ . '/db/conectar_bd.php',
	];
	foreach ($possible as $p) {
		if (file_exists($p)) {
			@include_once $p;
			if (isset($conn) && $conn instanceof mysqli) {
				$connLoaded = true;
				break;
			}
		}
	}
	if (!$connLoaded) {
		$debugMessages[] = "No se pudo cargar archivo de conexión. Rutas intentadas: " . implode(', ', $possible);
		$mysqli = null;
	} else {
		$mysqli = $conn;
		$debugMessages[] = "Archivo de conexión cargado correctamente.";
	}

	// Fallback si no hay conexión
	if (!($mysqli instanceof mysqli) || ($mysqli instanceof mysqli && $mysqli->connect_errno)) {
		$debugMessages[] = "Error conexión MySQL (archivo de conexión). Usando fallback sin datos.";
		$comprasRecientes = [];
		$proveedores = [];
		$gastosMes = 0.0;
		$numComprasMes = 0;
	} else {
		$debugConnected = true;
		$debugMessages[] = "Conexión MySQL exitosa a través de conectar_bd.php.";

		// Compras recientes (últimas 12)
		$comprasRecientes = [];
		$sql = "SELECT c.id_compra, c.fecha, p.nombre_proveedor, c.total, c.numero_comprobante
		        FROM compras c
		        LEFT JOIN proveedores p ON c.id_proveedor = p.id
		        ORDER BY c.fecha DESC
		        LIMIT 12";
		if ($res = $mysqli->query($sql)) {
			while ($row = $res->fetch_assoc()) {
				$comprasRecientes[] = [
					'id' => (int)$row['id_compra'],
					'fecha' => $row['fecha'],
					'proveedor' => $row['nombre_proveedor'] ?? 'N/A',
					'total' => (float)$row['total'],
					'comprobante' => $row['numero_comprobante'] ?? ''
				];
			}
			$res->free();
		} else {
			$debugMessages[] = "Error consulta compras recientes: " . $mysqli->error;
			error_log("SQL Error compras recientes: ".$mysqli->error);
		}

		// Proveedores
		$proveedores = [];
		$sql = "SELECT id, nombre_proveedor, persona_contacto, telefono, correo, estado
		        FROM proveedores
		        ORDER BY nombre_proveedor";
		if ($res = $mysqli->query($sql)) {
			while ($row = $res->fetch_assoc()) {
				$proveedores[] = [
					'id' => (int)$row['id'],
					'nombre' => $row['nombre_proveedor'],
					'contacto' => $row['persona_contacto'],
					'telefono' => $row['telefono'],
					'correo' => $row['correo'],
					'estado' => $row['estado']
				];
			}
			$res->free();
		} else {
			$debugMessages[] = "Error consulta proveedores: " . $mysqli->error;
			error_log("SQL Error proveedores: ".$mysqli->error);
		}

		// KPI compras mes
		$gastosMes = 0.0;
		$numComprasMes = 0;
		$sql = "SELECT IFNULL(COUNT(*),0) AS cnt, IFNULL(SUM(total),0) AS sum_total
		        FROM compras
		        WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
		if ($res = $mysqli->query($sql)) {
			$row = $res->fetch_assoc();
			$numComprasMes = (int)$row['cnt'];
			$gastosMes = (float)$row['sum_total'];
			$res->free();
		} else {
			$debugMessages[] = "Error consulta KPI compras: " . $mysqli->error;
			error_log("SQL Error KPI compras: ".$mysqli->error);
		}
	}
}

// Asegurar variables para la UI
$comprasRecientes = $comprasRecientes ?? [];
$proveedores = $proveedores ?? [];
$gastosMes = $gastosMes ?? 0.0;
$numComprasMes = $numComprasMes ?? 0;

// Flags para habilitar botones
$hasCompras = count($comprasRecientes) > 0;
$hasProveedores = count($proveedores) > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Módulo Compras — El Adobe</title>
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
  padding: 0; 
  color: #111827;
    }

    header {
  background-color: #333;
  color: white;
  padding: 2rem 2rem; /* ← antes era 1rem, ahora más alto */
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  position: fixed;
  width: 100%;
  top: 0;
  z-index: 1000;
  box-sizing: border-box;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}


.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.company-name {
  font-size: 1.4rem;
  font-weight: 700;
  color: white;
  margin: 0;
  line-height: 1.2;
}

.header-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.3rem;
  font-size: 0.95rem;
  color: #e6e6e6;
}

.header-links {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.cerrarSesion {
  color: white;
  text-decoration: none;
  padding: 0.4rem 0.7rem;
  border-radius: 6px;
  font-size: 0.9rem;
  background: rgba(255,255,255,0.03);
}

.cerrarSesion:hover {
  color: #d4b28c;
  background: rgba(255,255,255,0.08);
}

.contenedor {
  margin-top: 140px; /* ← compensación real por el nuevo alto del header */
  padding: 2rem;
}

/* Responsive */
@media (max-width: 900px) {
  header {
    flex-direction: column;
    align-items: flex-start;
    padding: 1rem 1.2rem;
  }

  .company-name {
    font-size: 1.2rem;
  }

  .header-meta {
    align-items: flex-start;
    font-size: 0.9rem;
  }

  .contenedor {
  margin-top: 120px; /* ← espacio real para evitar solapamiento */
  padding: 2rem;     /* ← aire interno para los cuadros */
}

}

    /* KPIs: ahora en grid con iconos */
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

  <!-- Header simple -->
  <header>
    <div class="brand">
      <div class="company-name">El Adobe</div>
    </div>
    <div class="header-meta">
      <div>Actualizado: <span id="serverTime"><?= date('d/m/Y H:i:s') ?></span></div>
      <div class="header-links">
        <a href="menu_modulos.php" class="cerrarSesion">Inicio</a>
        <?php if ($loggedIn): ?>
          <a href="logout.php" class="cerrarSesion">Cerrar sesión</a>
        <?php else: ?>
          <a href="loginEmpleados.php" class="cerrarSesion">Iniciar sesión</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Ajuste: contenedor principal para evitar solapamiento por header fijo -->
  <div class="contenedor">
    <!-- KPIs: adaptados a Compras -->
    <div class="kpi-grid">
      <div class="kpi card">
        <div class="icon">🧾</div>
        <div>
          <div class="meta">Gasto Total (mes)</div>
          <div class="value">Q<?= number_format($gastosMes, 2) ?></div>
        </div>
      </div>

      <div class="kpi card">
        <div class="icon">📥</div>
        <div>
          <div class="meta">Compras (mes)</div>
          <div class="value"><?= $numComprasMes ?></div>
        </div>
      </div>

      <div class="kpi card">
        <div class="icon">📇</div>
        <div>
          <div class="meta">Proveedores</div>
          <div class="value"><?= count($proveedores) ?></div>
        </div>
      </div>
    </div>

    <!-- Contenido principal: Compras recientes y Proveedores -->
    <div class="dashboard-grid">
      <div class="card">
        <h3>Compras Recientes</h3>
        <div class="small">Últimas compras registradas</div>
        <?php if (empty($comprasRecientes)): ?>
          <p class="muted">No se encontraron compras recientes.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>ID</th><th>Fecha</th><th>Proveedor</th><th style="text-align:right">Total</th><th>Comprobante</th></tr></thead>
            <tbody>
              <?php foreach ($comprasRecientes as $c): ?>
                <tr>
                  <td><?= $c['id'] ?></td>
                  <td><?= htmlspecialchars($c['fecha']) ?></td>
                  <td><?= htmlspecialchars($c['proveedor']) ?></td>
                  <td style="text-align:right">Q<?= number_format($c['total'],2) ?></td>
                  <td><?= htmlspecialchars($c['comprobante']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3>Proveedores</h3>
        <div class="small">Contactos y estado</div>
        <?php if (empty($proveedores)): ?>
          <p class="muted">No hay proveedores registrados.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Proveedor</th><th>Contacto</th><th>Teléfono</th><th>Correo</th><th>Estado</th></tr></thead>
            <tbody>
              <?php foreach ($proveedores as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['nombre']) ?></td>
                  <td><?= htmlspecialchars($p['contacto']) ?></td>
                  <td><?= htmlspecialchars($p['telefono']) ?></td>
                  <td><?= htmlspecialchars($p['correo']) ?></td>
                  <td><?= htmlspecialchars($p['estado']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Exportación: Compras (PDF) y Proveedores (Excel) -->
    <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:0.5rem;">
      <button id="btnPdf" class="btn" onclick="exportarComprasPDF()" <?= $hasCompras ? '' : 'disabled' ?>>📄 Exportar Compras (PDF)</button>
      <button id="btnExcel" class="btn secondary" onclick="exportarProveedoresExcel()" <?= $hasProveedores ? '' : 'disabled' ?>>📊 Exportar Proveedores (Excel)</button>
    </div>

    <!-- JS: export y reloj -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
      // Exportar compras a PDF (simple listado)
      async function exportarComprasPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({unit:'pt'});
        doc.setFontSize(14);
        doc.text("Reporte de Compras - Últimas", 40, 40);
        doc.setFontSize(10);
        let y = 70;
        const rows = [];
        const table = document.querySelectorAll('table')[0];
        if (!table) return alert('No hay compras para exportar');
        table.querySelectorAll('tbody tr').forEach(tr => {
          const cols = Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim());
          rows.push(cols);
        });
        rows.forEach(r => {
          doc.text(r.join('  |  '), 40, y);
          y += 18;
          if (y > 740) { doc.addPage(); y = 40; }
        });
        doc.save('compras_recientes.pdf');
      }

      function exportarProveedoresExcel() {
        const datos = [
          ["Proveedor","Contacto","Teléfono","Correo","Estado"],
          <?php foreach ($proveedores as $p): ?>
            ["<?= addslashes($p['nombre']) ?>","<?= addslashes($p['contacto']) ?>","<?= addslashes($p['telefono']) ?>","<?= addslashes($p['correo']) ?>","<?= addslashes($p['estado']) ?>"],
          <?php endforeach; ?>
        ];
        const hoja = XLSX.utils.aoa_to_sheet(datos);
        const libro = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(libro, hoja, "Proveedores");
        XLSX.writeFile(libro, "proveedores.xlsx");
      }

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
      document.addEventListener('DOMContentLoaded', startClock);
    </script>

  </div>

</body>
</html>
