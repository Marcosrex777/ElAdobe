<?php
include("../../../config/conexion.php");

// Verificamos si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Evitar inyección SQL
    $codigo = htmlspecialchars(trim($_POST['codigo']));
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $tipo = htmlspecialchars(trim($_POST['tipo']));
    $cantidad = intval($_POST['cantidad']);
    $costo_unidad = floatval($_POST['costo_unidad']);
    $estado = htmlspecialchars(trim($_POST['estado']));
    $fecha_compra = htmlspecialchars(trim($_POST['fecha_compra']));
    $id_proveedor = intval($_POST['id_proveedor']);

    // Validaciones básicas
    if (empty($codigo) || empty($nombre) || empty($tipo)) {
        echo "<script>alert('Por favor, completa todos los campos obligatorios.');</script>";
    } else {
        // Inserción en la tabla registro_mobiliario_equipo
        $sql = "INSERT INTO registro_mobiliario_equipo 
                (codigo, nombre, tipo, cantidad, costo_unidad, estado, fecha_compra, id_proveedor)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssids si", 
            $codigo, $nombre, $tipo, $cantidad, $costo_unidad, $estado, $fecha_compra, $id_proveedor
        );

        if ($stmt->execute()) {
            echo "<script>
                    alert('✅ Registro guardado correctamente');
                    window.location.href='listar.php';
                  </script>";
        } else {
            echo "<script>alert('❌ Error al registrar: " . $conexion->error . "');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Mobiliario o Equipo</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>

<h2>Registrar Mobiliario o Equipo</h2>

<form method="POST" id="formMobiliario">
    <label>Código:</label>
    <input type="text" name="codigo" required>

    <label>Nombre:</label>
    <input type="text" name="nombre" required>

    <label>Tipo:</label>
    <select name="tipo" required>
        <option value="">Seleccione...</option>
        <option value="Mobiliario">Mobiliario</option>
        <option value="Equipo">Equipo</option>
    </select>

    <label>Cantidad:</label>
    <input type="number" name="cantidad" min="1" value="1" required>

    <label>Costo por unidad (Q):</label>
    <input type="number" step="0.01" name="costo_unidad" required>

    <label>Estado:</label>
    <select name="estado" required>
        <option value="Activo">Activo</option>
        <option value="En reparación">En reparación</option>
        <option value="Dado de baja">Dado de baja</option>
    </select>

    <label>Fecha de compra:</label>
    <input type="datetime-local" name="fecha_compra" required>

    <label>Proveedor (ID):</label>
    <input type="number" name="id_proveedor" min="1" placeholder="Ej. 1">

    <button type="submit">Registrar</button>
</form>

<script src="../../../js/inventario.js"></script>
<script src="../js/mobiliario_equipo.js"></script>

</body>
</html>
