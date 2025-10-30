<?php
// inventario-adobe/inventario/comestibles/php/retiros.php
include("../../config/conexion.php");

// Cargar productos disponibles del inventario
$productos = $conn->query("SELECT codigo, nombre, cantidad_total FROM inventario_comestibles ORDER BY nombre ASC");

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = trim($conn->real_escape_string($_POST['codigo']));
    $motivo = trim($conn->real_escape_string($_POST['motivo']));
    $cantidad = floatval($_POST['cantidad']);

    // Validar que los datos sean correctos
    if ($codigo && $motivo && $cantidad > 0) {
        // Verificar si el producto existe y tiene stock suficiente
        $verificar = $conn->prepare("SELECT cantidad_total FROM inventario_comestibles WHERE codigo = ?");
        $verificar->bind_param("s", $codigo);
        $verificar->execute();
        $res = $verificar->get_result();

        if ($res->num_rows > 0) {
            $dato = $res->fetch_assoc();
            if ($dato['cantidad_total'] >= $cantidad) {
                // Insertar en la tabla retiros_consumibles
                $insert = $conn->prepare("INSERT INTO retiros_consumibles (codigo, motivo, cantidad, fecha_retiro)
                                          VALUES (?, ?, ?, NOW())");
                $insert->bind_param("ssd", $codigo, $motivo, $cantidad);
                if ($insert->execute()) {
                    $mensaje = "Retiro registrado correctamente.";
                    $tipo_mensaje = "exito";
                } else {
                    $mensaje = "Error al registrar el retiro.";
                    $tipo_mensaje = "error";
                }
                $insert->close();
            } else {
                $mensaje = "No hay suficiente stock para retirar esa cantidad.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "El producto seleccionado no existe en el inventario.";
            $tipo_mensaje = "error";
        }
        $verificar->close();
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
    <title>Registrar Retiro - Comestibles</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>

<div class="contenedor">
    <header>
        <h1>Registrar Retiro - Comestibles</h1>
    </header>

    <?php if (isset($mensaje)): ?>
        <div class="alerta <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <main>
        <form method="POST" id="form-retiro" class="formulario">

            <div class="campo">
                <label for="codigo">Producto:</label>
                <select name="codigo" id="codigo" required>
                    <option value="">Seleccione un producto...</option>
                    <?php
                    if ($productos && $productos->num_rows > 0) {
                        while ($p = $productos->fetch_assoc()) {
                            echo "<option value='{$p['codigo']}'>{$p['nombre']} (Stock: {$p['cantidad_total']})</option>";
                        }
                    } else {
                        echo "<option value=''>No hay productos en inventario</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="campo">
                <label for="motivo">Motivo del retiro:</label>
                <select name="motivo" id="motivo" required>
                    <option value="">Seleccione...</option>
                    <option value="Caducado">Caducado</option>
                    <option value="Roto">Roto</option>
                    <option value="Mal estado">Mal estado</option>
                    <option value="Inservible">Inservible</option>
                </select>
            </div>

            <div class="campo">
                <label for="cantidad">Cantidad a retirar:</label>
                <input type="number" step="0.01" name="cantidad" id="cantidad" required>
            </div>

            <div class="acciones-form">
                <button type="submit" class="btn btn-registrar">Guardar Retiro</button>
                <a href="listar.php" class="btn btn-volver">Volver</a>
            </div>
        </form>
    </main>
</div>

<script src="../../js/inventario.js"></script>
</body>
</html>
