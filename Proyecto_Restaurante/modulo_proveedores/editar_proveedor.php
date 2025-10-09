<?php
include_once("../conectar_bd.php");

$id = $_GET['id'];

// Consultar proveedor por ID
$sql = "SELECT * FROM proveedores WHERE id_proveedor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$proveedor = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre   = $_POST['nombre'];
    $contacto = $_POST['contacto'];
    $telefono = $_POST['telefono'];
    $email    = $_POST['email'];
    $direccion= $_POST['direccion'];
    $categoria= $_POST['categoria'];

    $update = "UPDATE proveedores 
               SET nombre=?, contacto=?, telefono=?, email=?, direccion=?, categoria=? 
               WHERE id_proveedor=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssssssi", $nombre, $contacto, $telefono, $email, $direccion, $categoria, $id);
    $stmt->execute();

    header("Location: listar_proveedores.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Proveedor</title>
    <link rel="stylesheet" href="proveedores.css">
</head>
<body>

<h1>Editar Proveedor</h1>

<form method="POST">
    <label>Nombre del Proveedor:</label>
    <input type="text" name="nombre" value="<?php echo $proveedor['nombre']; ?>" required><br>

    <label>Persona de Contacto:</label>
    <input type="text" name="contacto" value="<?php echo $proveedor['contacto']; ?>" required><br>

    <label>Teléfono:</label>
    <input type="text" name="telefono" value="<?php echo $proveedor['telefono']; ?>"><br>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo $proveedor['email']; ?>"><br>

    <label>Dirección:</label>
    <input type="text" name="direccion" value="<?php echo $proveedor['direccion']; ?>"><br>

    <label>Categoría:</label>
    <select name="categoria">
        <option value="Carnes"   <?php if($proveedor['categoria']=="Carnes") echo "selected"; ?>>Carnes</option>
        <option value="Verduras" <?php if($proveedor['categoria']=="Verduras") echo "selected"; ?>>Verduras</option>
        <option value="Lácteos"  <?php if($proveedor['categoria']=="Lácteos") echo "selected"; ?>>Lácteos</option>
        <option value="Bebidas"  <?php if($proveedor['categoria']=="Bebidas") echo "selected"; ?>>Bebidas</option>
        <option value="Otros"    <?php if($proveedor['categoria']=="Otros") echo "selected"; ?>>Otros</option>
    </select><br><br>

    <button type="submit">Actualizar</button>
</form>

</body>
</html>
