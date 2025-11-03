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
    $where[] = "c.categoria LIKE '%" . $conn->real_escape_string($_GET['categoria']) . "%'";
}
if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $where[] = "c.fecha_agregado BETWEEN '" . $_GET['fecha_inicio'] . "' AND '" . $_GET['fecha_fin'] . "'";
}

$sql = "SELECT * FROM vista_inventario_comestibles c JOIN productos p ON p.id_producto = c.id_producto";
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
    <title>Inventario de Comestibles</title>
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
    <h2>Inventario de Comestibles</h2>

    <div class="panel-filtros">
        <h3>Buscar / Filtrar Datos</h3>
        <form class="filtros-busqueda" id="form-comestibles" onsubmit="return false;">
            <div class="fila-filtros">
                <div class="campo">
                    <label>Producto</label>
                    <input type="text" id="producto" name="producto" placeholder="Buscar producto..." value="<?php echo $_GET['producto'] ?? ''; ?>">
                </div>

                <div class="campo">
                    <label>Categoría</label>
                    <select id="categoria">
                        <option value="">Todas</option>
                        <option value="Verduras">Verduras</option>
                        <option value="Carnes">Carnes</option>
                        <option value="Granos">Granos</option>
                        <option value="Frutas">Frutas</option>
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
                <!-- 👇  este ID es importante -->
                <button type="button" class="btn-buscar" id="btnBuscarComestibles">Buscar</button>
                <a href="listar_comestible.php" class="btn-limpiar">Limpiar</a>
                <a href="productos/registrar.php?tipo=comestible" class="btn-agregar btn-registrar">Registrar Producto</a>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>U.Medida</th>
                <th>Stock</th>
                <th>Stock minimo</th>
                <th>Precio Unitario (Q)</th>
                <th>Fecha de Ingreso</th>
                <th>Última Actualización</th>
            </tr>
        </thead>
        <tbody id="tabla-comestibles">
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $fila['id_producto']; ?></td>
                    <td><?= htmlspecialchars($fila['nombre']); ?></td>
                    <td><?= htmlspecialchars($fila['categoria']); ?></td>
                    <td><?= htmlspecialchars($fila['unidad_medida']); ?></td>
                    <td><?= $fila['stock']; ?></td>
                    <td><?= $fila['stock_minimo']; ?></td>
                    <td><?= number_format($fila['precio_unitario'], 2); ?></td>
                    <td><?= $fila['fecha_agregado']; ?></td>
                    <td><?= $fila['ultima_actualizacion']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="Script/inventario.js"></script>
</body>
</html>
