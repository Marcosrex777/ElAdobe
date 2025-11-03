<?php
include __DIR__ . '/../conectar_bd.php';

// Sesión y protección de acceso
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../loginEmpleados.php");
    exit();
}
$rol = $_SESSION['nombre_rol'] ?? '';

$where = [];

if (!empty($_GET['producto'])) {
    $where[] = "p.nombre LIKE '%" . $conn->real_escape_string($_GET['producto']) . "%'";
}
if (!empty($_GET['categoria'])) {
    $where[] = "m.categoria LIKE '%" . $conn->real_escape_string($_GET['categoria']) . "%'";
}
if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $where[] = "m.fecha_agregado BETWEEN '" . $_GET['fecha_inicio'] . "' AND '" . $_GET['fecha_fin'] . "'";
}

$sql = "SELECT * FROM vista_inventario_mobiliario m JOIN productos p ON p.id_producto = m.id_producto";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY p.nombre ASC;";

$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Mobiliario y Equipo</title>
    <link rel="stylesheet" href="css/inventario.css">
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
    <h2>Inventario de Mobiliario y Equipo</h2>

    <div class="panel-filtros">
        <h3>Buscar / Filtrar Datos</h3>
        <form method="GET" class="filtros-busqueda" id="form-filtros" onsubmit="return false;">
            <div class="fila-filtros">
                <div class="campo">
                    <label>Producto</label>
                    <input type="text" id="producto" name="producto" placeholder="Buscar producto..." value="<?php echo $_GET['producto'] ?? ''; ?>">
                </div>

                <div class="campo">
                    <label>Categoría</label>
                    <select id="categoria">
                        <option value="">Todas</option>
                        <option value="Mobiliario">Mobiliario</option>
                        <option value="Equipo">Equipo</option>
                    </select>
                </div>

                <div class="campo">
                    <label>Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $_GET['fecha_inicio'] ?? ''; ?>">
                </div>

                <div class="campo">
                    <label>Fecha Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $_GET['fecha_fin'] ?? ''; ?>">
                </div>
            </div>

            <div class="botones-filtros">
                <button type="button" class="btn-buscar" id="btnBuscar">Buscar</button>
                <a href="listar_mobiliario.php" class="btn-limpiar">Limpiar</a>
                <a href="productos/registrar.php?tipo=mobiliario" class="btn-agregar btn-registrar">Registrar Producto</a>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio Unitario (Q)</th>
                <th>Fecha de Ingreso</th>
                <th>Última Actualización</th>
            </tr>
        </thead>
        <tbody id="tabla-mobiliario">
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $fila['id_producto']; ?></td>
                    <td><?= htmlspecialchars($fila['nombre']); ?></td>
                    <td><?= htmlspecialchars($fila['categoria']); ?></td>
                    <td><?= $fila['stock']; ?></td>
                    <td><?= number_format($fila['precio_unitario'], 2); ?></td>
                    <td><?= $fila['fecha_agregado']; ?></td>
                    <td><?= $fila['ultima_actualizacion']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- JS externo -->
<script src="Script/inventario.js"></script>
</body>
</html>
