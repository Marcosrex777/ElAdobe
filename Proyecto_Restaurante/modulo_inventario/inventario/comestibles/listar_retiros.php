<?php
// inventario-adobe/inventario/comestibles/php/listar_retiros.php
include("../../config/conexion.php");

// Consulta general de retiros con el nombre del producto asociado
$sql = "SELECT 
            R.id_retiro,
            R.codigo,
            I.nombre AS producto,
            R.motivo,
            R.cantidad,
            R.fecha_retiro
        FROM retiros_consumibles R
        LEFT JOIN inventario_comestibles I ON R.codigo = I.codigo
        ORDER BY R.fecha_retiro DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Retiros - Comestibles</title>
    <link rel="stylesheet" href="../../css/inventario.css">
    <style>
        .caducado { background-color: #f8d7da; }  /* Rojo claro */
        .roto { background-color: #fff3cd; }      /* Amarillo claro */
        .mal-estado { background-color: #ffeeba; } /* Amarillo pálido */
        .inservible { background-color: #e2e3e5; } /* Gris */
    </style>
</head>
<body>

<div class="contenedor">
    <header>
        <h1>Historial de Retiros - Comestibles</h1>
    </header>

    <main>
        <div class="acciones">
            <a href="retiros.php" class="btn btn-accion">Registrar nuevo retiro</a>
            <a href="listar.php" class="btn btn-volver">Volver al inventario</a>
        </div>

        <input type="text" id="buscar" class="buscador" placeholder="Buscar producto o motivo...">

        <table id="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Motivo</th>
                    <th>Cantidad</th>
                    <th>Fecha del Retiro</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $motivo_clase = strtolower(str_replace(' ', '-', $row['motivo']));

                        echo "<tr class='{$motivo_clase}'>";
                        echo "<td>{$row['id_retiro']}</td>";
                        echo "<td>{$row['codigo']}</td>";
                        echo "<td>{$row['producto']}</td>";
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

<script src="../../../js/inventario.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    filtrarTabla("buscar", "tabla-inventario");
});
</script>
</body>
</html>
