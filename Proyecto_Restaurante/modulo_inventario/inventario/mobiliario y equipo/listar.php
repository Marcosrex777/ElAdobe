<?php
include("../../config/conexion.php");
$conexion = $conn;

// Verificamos si hay búsqueda
$busqueda = "";
if (isset($_GET['buscar'])) {
    $busqueda = htmlspecialchars(trim($_GET['buscar']));
    $query = "SELECT * FROM inventario_mobiliario_equipo 
              WHERE codigo LIKE ? OR nombre LIKE ? OR tipo LIKE ? OR estado LIKE ?
              ORDER BY fecha_actualizacion DESC";
    $stmt = $conexion->prepare($query);
    $like = "%$busqueda%";
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $query = "SELECT * FROM inventario_mobiliario_equipo ORDER BY fecha_actualizacion DESC";
    $resultado = $conexion->query($query);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Mobiliario y Equipo</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>

<h2>Inventario de Mobiliario y Equipo</h2>

<!-- Barra de búsqueda -->
<form method="GET" id="formBusqueda" class="busqueda-form">
    <input type="text" name="buscar" placeholder="Buscar por nombre, tipo o estado..." 
           value="<?php echo $busqueda; ?>">
    <button type="submit">Buscar</button>
</form>

<!-- Tabla de inventario -->
<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Cantidad Total</th>
            <th>Costo Unidad (Q)</th>
            <th>Estado</th>
            <th>Fecha Registro</th>
            <th>Última Actualización</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['codigo']); ?></td>
                    <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                    <td><?php echo $fila['cantidad_total']; ?></td>
                    <td><?php echo number_format($fila['costo_unidad'], 2); ?></td>
                    <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                    <td><?php echo $fila['fecha_registro']; ?></td>
                    <td><?php echo $fila['fecha_actualizacion']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No se encontraron registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script src="../../../js/inventario.js"></script>


</body>
</html>
