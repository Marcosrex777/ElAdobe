<?php
include __DIR__ . '/../../conectar_bd.php';

$where = [];

if (!empty($_GET['producto'])) {
    $where[] = "p.nombre LIKE '%" . $conn->real_escape_string($_GET['producto']) . "%'";
}

if (!empty($_GET['categoria'])) {
    $where[] = "c.categoria = '" . $conn->real_escape_string($_GET['categoria']) . "'";
}

if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $where[] = "c.fecha_agregado BETWEEN '" . $_GET['fecha_inicio'] . "' AND '" . $_GET['fecha_fin'] . "'";
}

$sql = "SELECT 
    p.id_producto,
    p.nombre,
    c.categoria,
    p.unidad_medida,
    c.stock,
    c.stock_minimo,
    p.precio_unitario,
    c.fecha_agregado,
    c.ultima_actualizacion
FROM productos p
JOIN comestibles c ON p.id_producto = c.id_producto";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.nombre ASC;";

$resultado = $conn->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>
                <td>{$fila['id_producto']}</td>
                <td>" . htmlspecialchars($fila['nombre']) . "</td>
                <td>" . htmlspecialchars($fila['categoria']) . "</td>
                <td>{$fila['stock']}</td>
                <td>Q " . number_format($fila['precio_unitario'], 2) . "</td>
                <td>{$fila['fecha_agregado']}</td>
                <td>{$fila['ultima_actualizacion']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>No se encontraron registros</td></tr>";
}
?>
