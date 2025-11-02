<?php
include 'conectabd.php';

// Leer los datos desde $_POST porque JS envía FormData
$id = $_POST["id"] ?? null;
$nombre = $_POST["nombre_proveedor"] ?? '';
$contacto = $_POST["persona_contacto"] ?? '';
$telefono = $_POST["telefono"] ?? '';
$correo = $_POST["correo"] ?? '';
$direccion = $_POST["direccion"] ?? '';
$categoria = $_POST["categoria"] ?? '';
$estado = $_POST["estado"] ?? '';

if ($id) {
    // Actualizar proveedor existente
    $sql = "UPDATE proveedores SET nombre_proveedor=?, persona_contacto=?, telefono=?, correo=?, direccion=?, categoria=?, estado=? WHERE id=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssssi", $nombre, $contacto, $telefono, $correo, $direccion, $categoria, $estado, $id);
    if ($stmt->execute()) {
        echo "Proveedor actualizado correctamente.";
    } else {
        echo "Error al actualizar el proveedor: " . $conexion->error;
    }
} else {
    // Insertar nuevo proveedor
    $sql = "INSERT INTO proveedores (nombre_proveedor, persona_contacto, telefono, correo, direccion, categoria, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssss", $nombre, $contacto, $telefono, $correo, $direccion, $categoria, $estado);
    if ($stmt->execute()) {
        echo "Proveedor guardado correctamente.";
    } else {
        echo "Error al guardar el proveedor: " . $conexion->error;
    }
}

$stmt->close();
$conexion->close();
?>
