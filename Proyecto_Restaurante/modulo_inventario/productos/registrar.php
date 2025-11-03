<?php
include __DIR__ . '/../../conectar_bd.php';

// Inicializar variables
$editando = false;
$producto = [
    'id_producto' => '',
    'nombre' => '',
    'tipo' => '',
    'unidad_medida' => '',
    'precio_unitario' => '',
    'stock' => '',
    'stock_minimo' => '',
    'categoria' => ''
];

// Si se proporciona el tipo vía GET (para preseleccionar al abrir desde listar), aplicarlo
if (isset($_GET['tipo']) && in_array($_GET['tipo'], ['comestible', 'mobiliario'])) {
    $producto['tipo'] = $_GET['tipo'];
}

// Verificar si se está editando un producto existente
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editando = true;
    $id = intval($_GET['id']);
    $query = "SELECT p.*, 
                     COALESCE(c.stock, m.stock) AS stock,
                     COALESCE(c.stock_minimo, m.stock_minimo) AS stock_minimo,
                     COALESCE(c.categoria, m.categoria) AS categoria
              FROM productos p
              LEFT JOIN comestibles c ON p.id_producto = c.id_producto
              LEFT JOIN mobiliario_equipo m ON p.id_producto = m.id_producto
              WHERE p.id_producto = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado && $resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
    } else {
        die("El producto con ID $id no existe.");
    }
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    function limpiarEntrada($valor) {
        return htmlspecialchars(strip_tags(trim($valor)));
    }

    $nombre = limpiarEntrada($_POST['nombre']);
    $tipo = limpiarEntrada($_POST['tipo']);
    $unidad_medida = limpiarEntrada($_POST['unidad_medida']);
    $precio_unitario = floatval($_POST['precio_unitario']);
    $stock = intval($_POST['stock']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $categoria = limpiarEntrada($_POST['categoria']);

    // Validaciones básicas
    if (!preg_match("/^[a-zA-ZÁÉÍÓÚáéíóúñÑ0-9\s.,-]+$/u", $nombre)) {
        header("Location: registrar.php?error=" . urlencode("El nombre del producto contiene caracteres no permitidos."));
        exit;
    }

    if (!in_array($tipo, ['comestible', 'mobiliario'])) {
        header("Location: registrar.php?error=" . urlencode("Tipo de producto inválido."));
        exit;
    }

    if ($stock_minimo > $stock) {
        header("Location: registrar.php?error=" . urlencode("El stock mínimo no puede ser mayor que el stock actual."));
        exit;
    }

    // Verificar duplicados solo si es registro nuevo
    if (!$editando) {
        $verificar = $conn->prepare("SELECT COUNT(*) FROM productos WHERE nombre = ? AND tipo = ?");
        $verificar->bind_param("ss", $nombre, $tipo);
        $verificar->execute();
        $verificar->bind_result($existe);
        $verificar->fetch();
        $verificar->close();

        if ($existe > 0) {
            header("Location: registrar.php?error=" . urlencode("El producto '$nombre' ya existe en la categoría '$tipo'."));
            exit;
        }
    }

    // Si se está editando
    if ($editando) {
        $sql = "UPDATE productos 
                SET nombre = ?, tipo = ?, unidad_medida = ?, precio_unitario = ? 
                WHERE id_producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdi", $nombre, $tipo, $unidad_medida, $precio_unitario, $id);
        $stmt->execute();

        if ($tipo === 'comestible') {
            $query_inv = "UPDATE comestibles 
                          SET categoria = ?, stock = ?, stock_minimo = ?, ultima_actualizacion = NOW()
                          WHERE id_producto = ?";
        } else {
            $query_inv = "UPDATE mobiliario_equipo 
                          SET categoria = ?, stock = ?, stock_minimo = ?, ultima_actualizacion = NOW()
                          WHERE id_producto = ?";
        }

        $stmt2 = $conn->prepare($query_inv);
        $stmt2->bind_param("siii", $categoria, $stock, $stock_minimo, $id);
        $stmt2->execute();

        $mensaje = "Producto actualizado correctamente.";
    } 
    // Si es un registro nuevo
    else {
        $sql = "INSERT INTO productos (nombre, tipo, unidad_medida, precio_unitario)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssd", $nombre, $tipo, $unidad_medida, $precio_unitario);
        $stmt->execute();
        $id_producto = $conn->insert_id;

        if ($tipo === 'comestible') {
            $query_inv = "INSERT INTO comestibles (id_producto, categoria, stock, stock_minimo, fecha_agregado)
                          VALUES (?, ?, ?, ?, CURRENT_DATE())";
        } else {
            $query_inv = "INSERT INTO mobiliario_equipo (id_producto, categoria, stock, stock_minimo, fecha_agregado)
                          VALUES (?, ?, ?, ?, CURRENT_DATE())";
        }

        $stmt2 = $conn->prepare($query_inv);
        $stmt2->bind_param("isii", $id_producto, $categoria, $stock, $stock_minimo);
        $stmt2->execute();

        $mensaje = "Producto registrado correctamente.";
    }

    // Redirigir con mensaje de éxito
    if ($tipo === 'comestible') {
        header("Location: ../listar_comestible.php?mensaje=" . urlencode($mensaje));
    } else {
        header("Location: ../listar_mobiliario.php?mensaje=" . urlencode($mensaje));
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $editando ? "Editar Producto" : "Registrar Nuevo Producto"; ?></title>
    <link rel="stylesheet" href="../css/inventario.css">
</head>
<body>

<div class="contenedor">
    <h2><?php echo $editando ? "Editar Producto" : "Registrar Nuevo Producto"; ?></h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="mensaje-form error">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre del producto:</label>
        <input type="text" id="nombre" name="nombre"
               value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

        <label for="tipo">Tipo de producto:</label>
        <select id="tipo" name="tipo" required>
            <option value="">Seleccione un tipo</option>
            <option value="comestible" <?php if ($producto['tipo'] === 'comestible') echo 'selected'; ?>>Comestible</option>
            <option value="mobiliario" <?php if ($producto['tipo'] === 'mobiliario') echo 'selected'; ?>>Mobiliario</option>
        </select>

        <label for="categoria">Categoría:</label>
        <input type="text" id="categoria" name="categoria"
               value="<?php echo htmlspecialchars($producto['categoria']); ?>"
               placeholder="Ejemplo: Lácteos, Papelería, Limpieza..." required>

        <label for="unidad_medida">Unidad de medida:</label>
        <input type="text" id="unidad_medida" name="unidad_medida"
               value="<?php echo htmlspecialchars($producto['unidad_medida']); ?>"
               placeholder="Ejemplo: unidad, litro, kg" required>

        <label for="precio_unitario">Precio unitario (Q):</label>
        <input type="number" id="precio_unitario" name="precio_unitario"
               step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio_unitario']); ?>" required>

        <label for="stock">Stock actual:</label>
        <input type="number" id="stock" name="stock" min="0"
               value="<?php echo htmlspecialchars($producto['stock']); ?>" required>

        <label for="stock_minimo">Stock mínimo:</label>
        <input type="number" id="stock_minimo" name="stock_minimo" min="0"
               value="<?php echo htmlspecialchars($producto['stock_minimo']); ?>" required>

        <div style="margin-top: 20px; display:flex; gap:10px; align-items:center;">
            <input type="submit" value="<?php echo $editando ? 'Actualizar Producto' : 'Registrar Producto'; ?>">
            <!-- Botón que vuelve a la última página desde donde se abrió (usa sessionStorage) -->
            <button type="button" id="btn-volver-ultimo" class="btn-agregar">Volver</button>
            <!-- Fallback para navegadores sin JS: mostrar enlaces contextuales -->
            <noscript>
                <?php if ($producto['tipo'] === 'comestible'): ?>
                    <a href="../listar_comestible.php" class="btn-agregar">Volver a Comestibles</a>
                <?php elseif ($producto['tipo'] === 'mobiliario'): ?>
                    <a href="../listar_mobiliario.php" class="btn-agregar">Volver a Mobiliario</a>
                <?php else: ?>
                    <a href="../listar_comestible.php" class="btn-agregar">Volver a Comestibles</a>
                    <a href="../listar_mobiliario.php" class="btn-agregar">Volver a Mobiliario</a>
                <?php endif; ?>
            </noscript>
        </div>
    </form>
</div>

<script src="../Script/inventario.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('btn-volver-ultimo');
    if (!btn) return;
    btn.addEventListener('click', function(){
        try {
            const last = sessionStorage.getItem('lastPage');
            if (last) {
                window.location.href = last;
                return;
            }
        } catch (e) {
            // si storage falla, continuar con fallback
        }

        // fallback: usar tipo preseleccionado en servidor
        var tipo = "<?php echo $producto['tipo'] ?? ''; ?>";
        if (tipo === 'comestible') window.location.href = '../listar_comestible.php';
        else if (tipo === 'mobiliario') window.location.href = '../listar_mobiliario.php';
        else window.location.href = '../listar_comestible.php';
    });
});
</script>
</body>
</html>
