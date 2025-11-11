<?php
require 'conectar_bd.php';
session_start();

// --- Verificar sesión de cliente ---
if (!isset($_SESSION['cliente_id'])) {
    header("Location: loginClientes.php");
    exit();
}

$id_usuario = $_SESSION['cliente_id'];
$nombre_cliente = $_SESSION['cliente_nombre'] ?? 'Cliente';

// --- Obtener sucursales ---
$sqlSucursales = "SELECT id_sucursal, nombre FROM sucursales";
$resSucursales = $conn->query($sqlSucursales);
$sucursales = $resSucursales->fetch_all(MYSQLI_ASSOC);

// --- Obtener sucursal seleccionada ---
$sucursal_id = $_GET['sucursal'] ?? ($sucursales[0]['id_sucursal'] ?? null);

// --- Obtener salones y mesas de la sucursal seleccionada ---
$salones = [];
if ($sucursal_id) {
    $sqlSalones = "SELECT id_salon, nombre FROM salones WHERE id_sucursal = ?";
    $stmtSalones = $conn->prepare($sqlSalones);
    $stmtSalones->bind_param("i", $sucursal_id);
    $stmtSalones->execute();
    $resSalones = $stmtSalones->get_result();

    while ($salon = $resSalones->fetch_assoc()) {
        $sqlMesas = "SELECT id_mesa, numero FROM mesas WHERE id_salon = ?";
        $stmtMesas = $conn->prepare($sqlMesas);
        $stmtMesas->bind_param("i", $salon['id_salon']);
        $stmtMesas->execute();
        $resMesas = $stmtMesas->get_result();
        $salon['mesas'] = $resMesas->fetch_all(MYSQLI_ASSOC);
        $salones[] = $salon;
    }
}

// --- Manejar envío de formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idSalon = $_POST['id_salon'] ?? null;
    $idMesa = $_POST['id_mesa'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $idHorario = $_POST['id_horario'] ?? null;

    if (!$fecha || !$idHorario) {
        $mensaje = "⚠️ Por favor selecciona una fecha y horario.";
    } else {
        if (empty($idMesa)) {
            // Reservación de salón completo
            $stmtMesas = $conn->prepare("SELECT id_mesa FROM mesas WHERE id_salon = ?");
            $stmtMesas->bind_param("i", $idSalon);
            $stmtMesas->execute();
            $mesasSalon = $stmtMesas->get_result()->fetch_all(MYSQLI_ASSOC);

            $bloqueado = false;
            foreach ($mesasSalon as $m) {
                $stmtCheck = $conn->prepare("
                    SELECT 1 FROM Hreservaciones 
                    WHERE fecha = ? AND id_horario = ? AND id_mesa = ? AND estado = 'activa'
                ");
                $stmtCheck->bind_param("sii", $fecha, $idHorario, $m['id_mesa']);
                $stmtCheck->execute();
                if ($stmtCheck->get_result()->num_rows > 0) {
                    $bloqueado = true;
                    break;
                }
            }

            if ($bloqueado) {
                $mensaje = "❌ No se puede reservar el salón completo: al menos una mesa ya está ocupada.";
            } else {
// Insertar una sola reservación con id_mesa = NULL
$stmtInsert = $conn->prepare("
    INSERT INTO Hreservaciones (id_usuario, id_salon, id_mesa, fecha, id_horario, estado)
    VALUES (?, ?, NULL, ?, ?, 'activa')
");
$stmtInsert->bind_param("iisi", $id_usuario, $idSalon, $fecha, $idHorario);
$stmtInsert->execute();
$mensaje = "✅ Reservación del salón completa realizada.";}
        } else {
            // Reservación de mesa individual
            $stmtCheck = $conn->prepare("
                SELECT 1 FROM Hreservaciones 
                WHERE fecha = ? AND id_horario = ? AND id_mesa = ? AND estado = 'activa'
            ");
            $stmtCheck->bind_param("sii", $fecha, $idHorario, $idMesa);
            $stmtCheck->execute();

            if ($stmtCheck->get_result()->num_rows > 0) {
                $mensaje = "❌ Esa mesa ya está reservada para ese horario.";
            } else {
                $stmtInsert = $conn->prepare("
                    INSERT INTO Hreservaciones (id_usuario, id_salon, id_mesa, fecha, id_horario, estado)
                    VALUES (?, ?, ?, ?, ?, 'activa')
                ");
                $stmtInsert->bind_param("iiisi", $id_usuario, $idSalon, $idMesa, $fecha, $idHorario);
                $stmtInsert->execute();
                $mensaje = "✅ Reservación de mesa realizada.";
            }
        }
    }
}

// --- Consultar reservaciones del usuario ---
$sqlRes = "
  SELECT 
    H.id_reservacion, 
    H.fecha, 
    hor.hora_inicio AS hora,
    H.estado,
    S.nombre AS nombre_salon, 
    M.numero AS numero_mesa
  FROM Hreservaciones H
  LEFT JOIN salones S ON H.id_salon = S.id_salon
  LEFT JOIN mesas M ON H.id_mesa = M.id_mesa
  LEFT JOIN horarios hor ON H.id_horario = hor.id_horario
  WHERE H.id_usuario = ?
  ORDER BY H.fecha DESC
";

$stmtRes = $conn->prepare($sqlRes);
$stmtRes->bind_param("i", $id_usuario);
$stmtRes->execute();
$reservas = $stmtRes->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<link rel="stylesheet" href="re.css">
<head>

  <meta charset="UTF-8">
  <title>Reservaciones</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <section class="reservas-container">
    <div style="margin-top:2rem;">
  <a href="index.php" class="btn-retorno">← Volver al inicio</a>
    <h1>Reservaciones de <?= htmlspecialchars($nombre_cliente) ?></h1>

    <?php if (isset($mensaje)): ?>
      <div class="alert"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- ======== NUEVA SECCIÓN DE SELECCIÓN ======== -->
<?php     
$minFecha = date('Y-m-d', strtotime('+1 day'));
$maxFecha = date('Y-m-d', strtotime('+2 months'));
?>
<!-- Botones de sucursales -->
<div class="card-grid">
  <?php foreach ($sucursales as $s): ?>
    <form method="GET" class="card" style="text-align:center;">
      <input type="hidden" name="sucursal" value="<?= $s['id_sucursal'] ?>">
      <img src="imagenes/sucursales/<?= $s['id_sucursal'] ?>.jpg" 
           alt="<?= htmlspecialchars($s['nombre']) ?>" 
           style="width:100%; height:150px; object-fit:cover; border-radius:8px;">
      <h3><?= htmlspecialchars($s['nombre']) ?></h3>
      <button type="submit" style="margin-top:0.5rem;">Ver Salones</button>
    </form>
  <?php endforeach; ?>
</div>

<!-- Despliegue de salones y mesas -->
<?php if ($sucursal_id): ?>
  <div class="salones-container">
    <h2>Salones y Mesas de la Sucursal Seleccionada</h2>

    <?php foreach ($salones as $salon): ?>
      <div class="salon-block">
        <h3><?= htmlspecialchars($salon['nombre']) ?></h3>

        <?php if (strtolower($salon['nombre']) === 'salón principal'): ?>
          <p>Este salón no se puede reservar.</p>
        <?php else: ?>
          <!-- Reservar salón completo -->
 <form method="POST" class="salon-form">
  <input type="hidden" name="id_salon" value="<?= $salon['id_salon'] ?>">
  <input type="hidden" name="id_mesa" value="">

  <label>Fecha:</label>
<input type="date" name="fecha" required min="<?= $minFecha ?>" max="<?= $maxFecha ?>">
  <label>Horario:</label>
  <select name="id_horario" required>
    <option value="1">Desayuno (7am - 11am)</option>
    <option value="2">Almuerzo (11am - 5pm)</option>
    <option value="3">Cena (5pm - 8pm)</option>
  </select>

  <button type="submit" class="reservar-salon">Reservar este salón completo</button>
</form>
        <?php endif; ?>

        <!-- Listado de mesas -->
        <div class="mesa-grid">
          <?php foreach ($salon['mesas'] as $mesa): ?>
 <form method="POST" class="mesa-form">
  <input type="hidden" name="id_mesa" value="<?= $mesa['id_mesa'] ?>">
  <input type="hidden" name="id_salon" value="<?= $salon['id_salon'] ?>">

  <label>Fecha:</label>
  <input type="date" name="fecha" required min="<?= $minFecha ?>" max="<?= $maxFecha ?>">

  <label>Horario:</label>
  <select name="id_horario" required>
    <option value="1">Desayuno (7am - 11am)</option>
    <option value="2">Almuerzo (11am - 5pm)</option>
    <option value="3">Cena (5pm - 8pm)</option>
  </select>

  <button type="submit" class="mesa">Reservar Mesa <?= $mesa['numero'] ?></button>
</form>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<h2>Mis Reservaciones</h2>
<div class="card-grid">
  <?php foreach ($reservas as $r): ?>
    <div class="card reserva">
      <h3>Reservación #<?= htmlspecialchars($r['id_reservacion']) ?></h3>
      <p><strong>Fecha:</strong> <?= htmlspecialchars($r['fecha']) ?> <?= htmlspecialchars($r['hora']) ?></p>
      <p><strong>Salón:</strong> <?= htmlspecialchars($r['nombre_salon'] ?? '-') ?></p>
<?php if (empty($r['numero_mesa'])): ?>
  <p><strong>Reservación:</strong> Salón <?= htmlspecialchars($r['nombre_salon'] ?? '-') ?> reservado</p>
<?php else: ?>
  <p><strong>Reservación:</strong> Mesa <?= htmlspecialchars($r['numero_mesa']) ?> reservada</p>
<?php endif; ?>      <p><strong>Estado:</strong> <?= htmlspecialchars($r['estado']) ?></p>
      <form method="POST" action="cancelar_reserva.php">
        <input type="hidden" name="id_reservacion" value="<?= $r['id_reservacion'] ?>">
        <button type="submit">Cancelar</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>
  </section>
</div>
</body>
</html>




