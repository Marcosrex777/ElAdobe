<?php
include("../../config/conexion.php");

$sql = "SELECT R.id_retiro, R.codigo, I.nombre, R.motivo, R.cantidad, R.fecha_retiro
        FROM Retiros R
        LEFT JOIN inventario_comestibles I ON R.codigo = I.codigo
        ORDER BY R.fecha_retiro DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Retiros - Comestibles</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>

<div class="contenedor">
    <header>
        <h1>Historial de Retiros - Comestibles</h1>
    </header>

    <main>
        <div class="acciones">
            <a href="retiros.php" class="btn btn-registrar">Registrar nuevo retiro</a>
            <a href="listar.php" class="btn btn-volver">Volver al inventario</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Motivo</th>
                    <th>Cantidad</th>
                    <th>Fecha de Retiro</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['id_retiro']}</td>";
                        echo "<td>{$row['codigo']}</td>";
                        echo "<td>{$row['nombre']}</td>";
                        echo "<td>{$row['motivo']}</td>";
                        echo "<td>" . number_format($row['cantidad'], 2) . "</td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha_retiro'])) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='sin-datos'>No hay retiros registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>

</body>
</html>
