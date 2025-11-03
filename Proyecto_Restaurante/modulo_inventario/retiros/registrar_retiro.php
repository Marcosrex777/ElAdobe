<?php
include __DIR__ . '/../../conectar_bd.php';

    // Obtener productos con su categoría real (comestibles y mobiliario)
    $sql_productos = "
        SELECT p.id_producto, p.nombre, c.categoria, 'comestible' AS tipo
        FROM productos p
        JOIN comestibles c ON p.id_producto = c.id_producto
        UNION
        SELECT p.id_producto, p.nombre, m.categoria, 'mobiliario' AS tipo
        FROM productos p
        JOIN mobiliario_equipo m ON p.id_producto = m.id_producto
        ORDER BY nombre ASC;
        ";
    $resultado_productos = $conn->query($sql_productos);

    // Variables de mensaje
    $mensaje = "";
    $clase = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST["id_producto"];
    $tipo = $_POST["tipo"];
    $cantidad = $_POST["cantidad"];
    $razon = trim($_POST["razon"]);

    // Validaciones básicas del lado servidor
    if (empty($id_producto) || empty($tipo) || empty($cantidad) || empty($razon)) {
        $mensaje = "Todos los campos son obligatorios.";
        $clase = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s.,-]+$/u", $razon)) {
        $mensaje = "La razón contiene caracteres no permitidos.";
        $clase = "error";
    } else {
        // Intentar insertar retiro (el trigger se encarga del descuento)
        $stmt = $conn->prepare("INSERT INTO retiros (id_producto, tipo, cantidad, razon) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $id_producto, $tipo, $cantidad, $razon);

        try {
            if ($stmt->execute()) {
                $mensaje = "Retiro registrado exitosamente.";
                $clase = "exito";
            } else {
                $mensaje = "Error al registrar el retiro.";
                $clase = "error";
            }
        } catch (mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), "Stock insuficiente")) {
                $mensaje = "No se puede realizar el retiro: stock insuficiente.";
            } else {
                $mensaje = "Error: " . htmlspecialchars($e->getMessage());
            }
            $clase = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Retiro</title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>
<body>

<div class="contenedor">
    <h2>Registrar Retiro de Inventario</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje-form <?= $clase; ?>">
            <?= htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

<form method="POST" id="form-retiro" onsubmit="return validarRetiro();">

    <!-- 🔹 PRIMERO: CATEGORÍA / TIPO -->
    <div class="campo">
        <label for="tipo">Tipo de inventario:</label>
        <select name="tipo" id="tipo" required>
            <option value="">Seleccione tipo</option>
            <option value="comestible" <?= (($_POST['tipo'] ?? '') == 'comestible') ? 'selected' : ''; ?>>Comestible</option>
            <option value="mobiliario" <?= (($_POST['tipo'] ?? '') == 'mobiliario') ? 'selected' : ''; ?>>Mobiliario</option>
        </select>
    </div>

    <!-- 🔹 SEGUNDO: PRODUCTO (se filtra según tipo) -->
    <div class="campo" style="position: relative;">
        <label for="nombre_producto">Producto:</label>
        <input type="text" id="nombre_producto" name="nombre_producto" placeholder="Escriba para buscar..." autocomplete="off" required disabled>
        <input type="hidden" id="id_producto" name="id_producto">
        <ul id="lista-sugerencias" class="lista-sugerencias"></ul>
    </div>

    <!-- 🔹 TERCERO: STOCK DISPONIBLE -->
    <div class="campo">
        <label>Stock disponible:</label>
        <input type="text" id="stock_disponible" readonly placeholder="Seleccione un producto" style="background:#f9f9f9; font-weight:bold;">
    </div>

    <!-- 🔹 CUARTO: CANTIDAD Y RAZÓN -->
    <div class="campo">
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" id="cantidad" min="1" required value="<?= $_POST['cantidad'] ?? ''; ?>">
    </div>

    <div class="campo">
        <label for="razon">Razón del retiro:</label>
        <textarea name="razon" id="razon" rows="3" required><?= $_POST['razon'] ?? ''; ?></textarea>
    </div>

    <div class="botones-filtros">
        <input type="submit" value="Registrar Retiro" class="btn-buscar">
        <a href="listar_retiros.php" class="btn-limpiar">Volver</a>
    </div>
</form>
</div>

<script src="../Script/inventario.js"></script>
<script>
// Validación de caracteres en el formulario
function validarRetiro() {
    const razon = document.getElementById("razon").value.trim();
    const regex = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s.,-]+$/u;

    if (!regex.test(razon)) {
        mostrarMensaje("error", "No ingresar caracteres especiales en la razón del retiro");
        return false;
    }
    return true;
}
</script>
</body>
</html>
