<?php
include("../../config/conexion.php");

// Consultar los movimientos registrados
$sql = "SELECT M.id_movimiento, M.codigo, I.nombre, M.tipo, M.motivo, 
        M.cantidad, M.fecha_movimiento
        FROM Movimientos M
        LEFT JOIN inventario_comestibles I ON M.codigo = I.codigo
        ORDER BY M.fecha_movimiento DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos - Comestibles</title>
    <link rel="stylesheet" href="../../css/inventario.css">
    <style>
    </style>
</head>
<body>

<div class="contenedor">
    <header>
        <h1>Historial de Movimientos - Comestibles</h1>
    </header>

    <main>
        <div class="acciones">
            <a href="movimientos.php" class="btn btn-accion">Registrar movimiento</a>
            <a href="listar.php" class="btn btn-volver">Volver al inventario</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Motivo</th>
                    <th>Cantidad</th>
                    <th>Fecha del Movimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $clase = ($row['tipo'] == 'Entrada') ? 'entrada' : 'salida';
                        echo "<tr class='{$clase}'>";
                        echo "<td>{$row['id_movimiento']}</td>";
                        echo "<td>{$row['codigo']}</td>";
                        echo "<td>{$row['nombre']}</td>";
                        echo "<td>{$row['tipo']}</td>";
                        echo "<td>{$row['motivo']}</td>";
                        echo "<td>" . number_format($row['cantidad'], 2) . "</td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_movimiento'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='sin-datos'>No hay movimientos registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>

</body>
</html>
