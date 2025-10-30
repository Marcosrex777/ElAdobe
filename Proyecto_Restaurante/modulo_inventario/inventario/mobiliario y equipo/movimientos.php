<?php
include("../../config/conexion.php");

$productos = $conn->query("SELECT codigo, nombre, cantidad_total FROM inventario_comestibles ORDER BY nombre ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $motivo = $conn->real_escape_string($_POST['motivo']);
    $cantidad = floatval($_POST['cantidad']);

    $check = $conn->prepare("SELECT cantidad_total FROM inventario_comestibles WHERE codigo = ?");
    $check->bind_param("s", $codigo);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        if (!($tipo == 'Salida' && $data['cantidad_total'] < $cantidad)) {
            $insert = $conn->prepare("INSERT INTO Movimientos (codigo, tipo, motivo, cantidad, fecha_movimiento)
                                      VALUES (?, ?, ?, ?, NOW())");
            $insert->bind_param("sssd", $codigo, $tipo, $motivo, $cantidad);
            $insert->execute();
        }
    }
}
if ($insert->execute()) {
     header("Location: listar.php?exito=1");
        exit();
    } else {
        header("Location: listar.php?error=1");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Movimiento - Comestibles</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>

<div class="contenedor">
    <header><h1>Registrar Movimiento - Comestibles</h1></header>
    <main>
        <form method="POST" id="form-movimiento" class="formulario">
            <div class="campo">
                <label>Producto:</label>
                <select name="codigo" required>
                    <option value="">Seleccione un producto...</option>
                    <?php while ($p = $productos->fetch_assoc()) {
                        echo "<option value='{$p['codigo']}'>{$p['nombre']} (Stock: {$p['cantidad_total']})</option>";
                    } ?>
                </select>
            </div>

            <div class="campo">
                <label>Tipo de movimiento:</label>
                <select name="tipo" required>
                    <option value="">Seleccione...</option>
                    <option value="Entrada">Entrada</option>
                    <option value="Salida">Salida</option>
                </select>
            </div>

            <div class="campo">
                <label>Motivo:</label>
                <input type="text" name="motivo" maxlength="100" required>
            </div>

            <div class="campo">
                <label>Cantidad:</label>
                <input type="number" step="0.01" name="cantidad" required>
            </div>

            <div class="acciones-form">
                <button type="submit" class="btn btn-registrar">Guardar Movimiento</button>
                <a href="listar.php" class="btn btn-volver">Cancelar</a>
            </div>
        </form>
    </main>
</div>

<script src="../../js/inventario.js"></script>
</body>
</html>
