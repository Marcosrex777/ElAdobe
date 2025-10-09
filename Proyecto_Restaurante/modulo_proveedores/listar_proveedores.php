<?php
include_once("../conectar_bd.php");

// Obtener todos los proveedores con MySQLi
$sql = "SELECT * FROM proveedores";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Proveedores</title>
    <link rel="stylesheet" href="proveedores.css">
</head>
<body>

    <h1>Lista de Proveedores</h1>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Proveedor</th>
                <th>Persona de Contacto</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($prov = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $prov['id_proveedor']; ?></td>
                <td><?php echo $prov['nombre']; ?></td>
                <td><?php echo $prov['contacto']; ?></td>
                <td><?php echo $prov['telefono']; ?></td>
                <td><?php echo $prov['email']; ?></td>
                <td><?php echo $prov['direccion']; ?></td>
                <td>
                    <a href="editar_proveedor.php?id=<?php echo $prov['id_proveedor']; ?>">Editar</a> | 
                    <a href="eliminar_proveedor.php?id=<?php echo $prov['id_proveedor']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');">Borrar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
