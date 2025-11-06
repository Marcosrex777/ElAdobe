<?php
include __DIR__ . '/../../conectar_bd.php';

// Sesión y protección de acceso
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../loginEmpleados.php");
    exit();
}
$rol = $_SESSION['nombre_rol'] ?? '';

$sql = "SELECT r.*, p.nombre
        FROM retiros r
        JOIN productos p ON p.id_producto = r.id_producto
        ORDER BY r.fecha_retiro DESC;";

$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Retiros</title>
    <link rel="stylesheet" href="../css/inventario.css">

    <!-- ✅ Librerías DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.1/css/buttons.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
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

    <a href="registrar_retiro.php" class="btn-registrar">Registrar Retiro</a>

    <table id="tabla-retiros" class="display">
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

<!-- ✅ Inicialización de DataTable -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    $('#tabla-retiros').DataTable({
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json"
        },
        dom: 'Bfrtip',
        order: [[5, 'desc']], // Ordenar por fecha de retiro descendente por defecto
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📊 Exportar Excel',
                className: 'btn-buscar'
            },
            {
                extend: 'pdfHtml5',
                text: '📄 Exportar PDF',
                className: 'btn-buscar'
            },
            {
                extend: 'print',
                text: '🖨️ Imprimir',
                className: 'btn-limpiar'
            }
        ]
    });
});
</script>
</body>
</html>
