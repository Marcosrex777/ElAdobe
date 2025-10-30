<?php
// inventario-adobe/inventario/comestibles/php/listar.php
include("../../config/conexion.php");

// Traer todo el inventario actual de comestibles
$sql = "SELECT 
            codigo,
            categoria,
            nombre,
            unidad_medida,
            coste,
            cantidad_total,
            coste_inventario,
            fecha_primera,
            fecha_actualizacion
        FROM inventario_comestibles
        ORDER BY nombre ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Comestibles</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>
<div class="contenedor">
    <header>
        <h1>Inventario de Comestibles</h1>
    </header>

    <main>
        <div class="acciones">
            <a href="registrar.php" class="btn btn-registrar">Registrar entrada</a>
            <a href="listar_movimientos.php" class="btn btn-historial">Ver movimientos</a>
            <a href="listar_retiros.php" class="btn btn-historial">Ver retiros</a>
            <a href="../../../inventario.php" class="btn btn-volver">Volver</a>
        </div>

        <input type="text" id="buscar" class="buscador" placeholder="Buscar por código, nombre o categoría...">

        <table id="tabla-inventario">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Categoría</th>
                    <th>Nombre</th>
                    <th>Unidad</th>
                    <th>Coste (Q)</th>
                    <th>Cantidad</th>
                    <th>Valor total (Q)</th>
                    <th>Fecha 1ra</th>
                    <th>Última actualización</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // clases para colorear según stock
                        $clase = ($row['cantidad_total'] <= 0) ? "sin-stock" : "stock-ok";

                        echo "<tr class='{$clase}'>";
                        echo "<td>{$row['codigo']}</td>";
                        echo "<td>{$row['categoria']}</td>";
                        echo "<td>{$row['nombre']}</td>";
                        echo "<td>{$row['unidad_medida']}</td>";
                        echo "<td>" . number_format($row['coste'], 2) . "</td>";
                        echo "<td>" . number_format($row['cantidad_total'], 2) . "</td>";
                        echo "<td>" . number_format($row['coste_inventario'], 2) . "</td>";
                        echo "<td>" . ($row['fecha_primera'] ? date("d/m/Y", strtotime($row['fecha_primera'])) : "") . "</td>";
                        echo "<td>" . ($row['fecha_actualizacion'] ? date("d/m/Y", strtotime($row['fecha_actualizacion'])) : "") . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='sin-datos'>No hay productos en el inventario.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>

<script src="../../../js/inventario.js"></script>
<script>
// activar buscador sobre la tabla
document.addEventListener("DOMContentLoaded", () => {
    filtrarTabla("buscar", "tabla-inventario");
});
</script>
</body>
</html>
