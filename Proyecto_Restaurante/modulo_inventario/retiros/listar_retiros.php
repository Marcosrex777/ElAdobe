<?php
include __DIR__ . '/../../conectar_bd.php';

// Sesión y protección de acceso
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../loginEmpleados.php");
    exit();
}
$rol = $_SESSION['nombre_rol'] ?? '';

$where = [];

if (!empty($_GET['producto'])) {
    $where[] = "p.nombre LIKE '%" . $conn->real_escape_string($_GET['producto']) . "%'";
}
if (!empty($_GET['tipo'])) {
    $where[] = "r.tipo = '" . $conn->real_escape_string($_GET['tipo']) . "'";
}
if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $where[] = "DATE(r.fecha_retiro) BETWEEN '" . $_GET['fecha_inicio'] . "' AND '" . $_GET['fecha_fin'] . "'";
}
if (!empty($_GET['razon'])) {
    $where[] = "r.razon LIKE '%" . $conn->real_escape_string($_GET['razon']) . "%'";
}

$sql = "SELECT r.*, p.nombre
        FROM retiros r
        JOIN productos p ON p.id_producto = r.id_producto";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY r.fecha_retiro DESC;";

$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Retiros</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>
<body>

<header>
    <div class="company-name">El Adobe</div>
    <div>
            <a href="../../menu_modulos.php" class="cerrarSesion">Inicio</a>
            <a href="../../logout.php" class="cerrarSesion">Cerrar sesión</a>
    </div>
</header>

<div class="contenedor">
    <h2>Historial de Retiros</h2>

    <div class="panel-filtros">
        <h3>Buscar / Filtrar Datos</h3>
        <form method="GET" class="filtros-busqueda">
            <div class="campo">
                <label>Producto</label>
                <input type="text" name="producto" placeholder="Buscar producto..." value="<?= $_GET['producto'] ?? '' ?>">
            </div>
            <div class="campo">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="comestible" <?= (($_GET['tipo'] ?? '') === 'comestible') ? 'selected' : '' ?>>Comestible</option>
                    <option value="mobiliario" <?= (($_GET['tipo'] ?? '') === 'mobiliario') ? 'selected' : '' ?>>Mobiliario</option>
                </select>
            </div>
            <div class="campo">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="<?= $_GET['fecha_inicio'] ?? '' ?>">
            </div>
            <div class="campo">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" value="<?= $_GET['fecha_fin'] ?? '' ?>">
            </div>
            <div class="campo">
                <label>Razón</label>
                <input type="text" name="razon" placeholder="Buscar razón..." value="<?= $_GET['razon'] ?? '' ?>">
            </div>

            <div class="botones-filtros">
                <button type="submit" class="btn-buscar">Buscar</button>
                <a href="listar_retiros.php" class="btn-limpiar">Limpiar</a>
                <!-- Botón adicional para registrar un nuevo retiro -->
                <a href="registrar_retiro.php" class="btn-agregar">Registrar Retiro</a>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Razón</th>
                <th>Fecha Retiro</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $fila['id_retiro']; ?></td>
                    <td><?= htmlspecialchars($fila['nombre']); ?></td>
                    <td><?= ucfirst($fila['tipo']); ?></td>
                    <td><?= $fila['cantidad']; ?></td>
                    <td><?= htmlspecialchars($fila['razon']); ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($fila['fecha_retiro'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="../Script/inventario.js"></script>
</body>
</html>
