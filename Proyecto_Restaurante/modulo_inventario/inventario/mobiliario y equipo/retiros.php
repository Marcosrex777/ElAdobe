<?php
include("../../config/conexion.php");

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = htmlspecialchars(trim($_POST['codigo']));
    $cantidad = intval($_POST['cantidad']);
    $motivo = htmlspecialchars(trim($_POST['motivo']));

    // Validación básica
    if (empty($codigo) || $cantidad <= 0) {
        echo "<script>alert('Por favor, completa todos los campos correctamente.');</script>";
    } else {
        // Verificar si existe el código en inventario
        $consulta = $conexion->prepare("SELECT * FROM inventario_mobiliario_equipo WHERE codigo = ?");
        $consulta->bind_param("s", $codigo);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            $item = $resultado->fetch_assoc();

            // Si hay suficiente cantidad
            if ($item['cantidad_total'] >= $cantidad) {
                // Registrar el retiro en la tabla de registro
                $sql = "INSERT INTO registro_mobiliario_equipo
                        (codigo, nombre, tipo, cantidad, costo_unidad, estado, id_proveedor)
                        VALUES (?, ?, ?, ?, ?, ?, NULL)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param(
                    "sssids",
                    $item['codigo'],
                    $item['nombre'],
                    $item['tipo'],
                    $cantidad * -1,   // cantidad negativa para retiro
                    $item['costo_unidad'],
                    $item['estado']
                );

                if ($stmt->execute()) {
                    echo "<script>alert('✅ Retiro registrado correctamente');</script>";
                } else {
                    echo "<script>alert('❌ Error al registrar el retiro: " . $conexion->error . "');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('⚠️ No hay suficiente cantidad en inventario.');</script>";
            }
        } else {
            echo "<script>alert('⚠️ Código no encontrado en el inventario.');</script>";
        }
        $consulta->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Retiros de Mobiliario y Equipo</title>
    <link rel="stylesheet" href="../../css/inventario.css">
</head>
<body>

<h2>Registrar Retiro de Mobiliario o Equipo</h2>

<form method="POST" id="formRetiro">
    <label>Código del artículo:</label>
    <input type="text" name="codigo" required placeholder="Ej. EQP-001">

    <label>Cantidad a retirar:</label>
    <input type="number" name="cantidad" min="1" required>

    <label>Motivo del retiro:</label>
    <textarea name="motivo" rows="3" placeholder="Ej. Daño, reemplazo, traslado..." required></textarea>

    <button type="submit">Registrar Retiro</button>
</form>

<!-- Scripts -->
<script src="../../js/inventario.js"></script>
<script src="js/mobiliario_equipo.js"></script>

</body>
</html>
