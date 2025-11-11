<?php
require 'conectar_bd.php'; // conexión mysqli

// Filtro de sucursal
$sucursal = isset($_GET['sucursal']) ? (int)$_GET['sucursal'] : 0;

// Fecha actual
$hoy = date('Y-m-d');

// --- Reservaciones de hoy (en espera) ---
$sqlHoy = "
SELECT r.id_reservacion, u.nombre_usuario, m.numero AS mesa, h.nombre AS horario, s.nombre AS sucursal
FROM Hreservaciones r
JOIN usuarios u ON r.id_usuario = u.id_usuario
JOIN mesas m ON r.id_mesa = m.id_mesa
JOIN salones sa ON m.id_salon = sa.id_salon
JOIN sucursales s ON sa.id_sucursal = s.id_sucursal
JOIN horarios h ON r.id_horario = h.id_horario
WHERE r.fecha = ? AND r.estado = 'en_espera'
";

if ($sucursal > 0) {
    $sqlHoy .= " AND s.id_sucursal = ?";
}

$stmtHoy = $conn->prepare($sqlHoy);
if ($sucursal > 0) {
    $stmtHoy->bind_param("si", $hoy, $sucursal);
} else {
    $stmtHoy->bind_param("s", $hoy);
}
$stmtHoy->execute();
$resHoy = $stmtHoy->get_result();
$reservasHoy = $resHoy ? $resHoy->fetch_all(MYSQLI_ASSOC) : [];

// --- Reservaciones futuras (activas) ---
$sqlFut = "
SELECT r.id_reservacion, u.nombre_usuario, m.numero AS mesa, h.nombre AS horario, r.fecha, s.nombre AS sucursal
FROM Hreservaciones r
JOIN usuarios u ON r.id_usuario = u.id_usuario
JOIN mesas m ON r.id_mesa = m.id_mesa
JOIN salones sa ON m.id_salon = sa.id_salon
JOIN sucursales s ON sa.id_sucursal = s.id_sucursal
JOIN horarios h ON r.id_horario = h.id_horario
WHERE r.fecha > ? AND r.estado = 'activa'
";

if ($sucursal > 0) {
    $sqlFut .= " AND s.id_sucursal = ?";
}
$sqlFut .= " ORDER BY r.fecha ASC";

$stmtFut = $conn->prepare($sqlFut);
if ($sucursal > 0) {
    $stmtFut->bind_param("si", $hoy, $sucursal);
} else {
    $stmtFut->bind_param("s", $hoy);
}
$stmtFut->execute();
$resFut = $stmtFut->get_result();
$reservasFut = $resFut ? $resFut->fetch_all(MYSQLI_ASSOC) : [];

// --- Obtener sucursales ---
$resSuc = $conn->query("SELECT id_sucursal, nombre FROM sucursales");
$sucursales = $resSuc ? $resSuc->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Vista Reservaciones (Empleado)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Aquí enlazas tu CSS principal -->
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="wrap">
    <h1>Reservaciones – Hoy (<?= $hoy ?>)</h1>

    <div class="panel">
      <form method="get">
        <label>Sucursal:</label>
        <select name="sucursal" onchange="this.form.submit()">
          <option value="0">Todas</option>
          <?php foreach($sucursales as $s): ?>
            <option value="<?= $s['id_sucursal'] ?>" <?= $sucursal==$s['id_sucursal']?'selected':'' ?>>
              <?= htmlspecialchars($s['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <div class="panel">
      <h2>Reservaciones en espera</h2>
      <div class="list">
        <?php if(!$reservasHoy): ?>
          <p>No hay reservaciones en espera para hoy.</p>
        <?php else: foreach($reservasHoy as $r): ?>
          <div class="item">
            <div>
              <strong><?= htmlspecialchars($r['nombre_usuario']) ?></strong> reservó 
              <strong>Mesa <?= $r['mesa'] ?></strong> en <em><?= $r['sucursal'] ?></em> 
              para <?= $r['horario'] ?>.
            </div>
            <div class="tag">Estado: en espera</div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="panel">
      <h2>Futuras reservaciones</h2>
      <div class="list">
        <?php if(!$reservasFut): ?>
          <p>No hay reservaciones futuras.</p>
        <?php else: 
          $mesActual = '';
          foreach($reservasFut as $r):
            $mes = date('F', strtotime($r['fecha']));
            if($mes !== $mesActual){
              echo "<h3>$mes</h3>";
              $mesActual = $mes;
            }
        ?>
          <div class="item">
            <div>
              <strong><?= htmlspecialchars($r['nombre_usuario']) ?></strong> reservó 
              <strong>Mesa <?= $r['mesa'] ?></strong> en <em><?= $r['sucursal'] ?></em> 
              para el día <?= $r['fecha'] ?> (<?= $r['horario'] ?>).
            </div>
            <div class="tag">Estado: activa</div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </main>
</body>
</html>

