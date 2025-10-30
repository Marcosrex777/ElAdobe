<?php
// inventario-adobe/inventario/comestibles/php/registrar.php
include("../../config/conexion.php");

// Cargar proveedores activos
$proveedores = $conn->query("SELECT id_proveedor, nombre_proveedor FROM proveedores WHERE estado = 'Activo'");

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = trim($conn->real_escape_string($_POST['codigo']));
    $categoria = trim($conn->real_escape_string($_POST['categoria']));
    $nombre = trim($conn->real_escape_string($_POST['nombre']));
    $unidad_medida = trim($conn->real_escape_string($_POST['unidad_medida']));
    $coste = floatval($_POST['coste']);
    $cantidad = floatval($_POST['cantidad']);
    $id_proveedor = intval($_POST['id_proveedor']);

    if ($codigo && $categoria && $nombre && $unidad_medida && $coste > 0 && $cantidad > 0 && $id_proveedor > 0) {
        $sql = "INSERT INTO registro_comestibles 
                (codigo, categoria, nombre, unidad_medida, coste, cantidad, fecha_actualizacion, id_proveedor)
                VALUES (?, ?, ?, ?, ?, ?, CURRENT_DATE, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssddi", $codigo, $categoria, $nombre, $unidad_medida, $coste, $cantidad, $id_proveedor);
        
        if ($stmt->execute()) {
            $mensaje = "Producto registrado correctamente.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al registrar el producto.";
            $tipo_mensaje = "error";
        }
        $stmt->close();
    } else {
        $mensaje = "Complete todos los campos correctamente.";
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Entrada - Comestibles</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>

<div class="contenedor">
    <header>
        <h1>Registrar Entrada - Comestibles</h1>
    </header>

    <?php if (isset($mensaje)): ?>
        <div class="alerta <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <main>
        <form method="POST" id="form-registro" class="formulario">
            <div class="campo">
                <label for="codigo">Código:</label>
                <input type="text" name="codigo" id="codigo" maxlength="30" required>
            </div>

            <div class="campo">
                <label for="categoria">Categoría:</label>
                <input type="text" name="categoria" id="categoria" maxlength="50" required>
            </div>

            <div class="campo">
                <label for="nombre">Nombre del producto:</label>
                <input type="text" name="nombre" id="nombre" maxlength="100" required>
            </div>

            <div class="campo">
                <label for="unidad_medida">Unidad de medida:</label>
                <input type="text" name="unidad_medida" id="unidad_medida" maxlength="20" required>
            </div>

            <div class="campo">
                <label for="coste">Costo unitario (Q):</label>
                <input type="number" step="0.01" name="coste" id="coste" required>
            </div>

            <div class="campo">
                <label for="cantidad">Cantidad ingresada:</label>
                <input type="number" step="0.01" name="cantidad" id="cantidad" required>
            </div>

            <div class="campo">
                <label for="id_proveedor">Proveedor:</label>
                <select name="id_proveedor" id="id_proveedor" required>
                    <option value="">Seleccione un proveedor...</option>
                    <?php
                    if ($proveedores && $proveedores->num_rows > 0) {
                        while ($prov = $proveedores->fetch_assoc()) {
                            echo "<option value='{$prov['id_proveedor']}'>{$prov['nombre_proveedor']}</option>";
                        }
                    } else {
                        echo "<option value=''>No hay proveedores activos</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="acciones-form">
                <button type="submit" class="btn btn-registrar">Guardar Registro</button>
                <a href="listar.php" class="btn btn-volver">Volver</a>
            </div>
        </form>
    </main>
</div>

<script src="../../js/inventario.js"></script>
</body>
</html>
