<?php
// proveedores.php
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Proveedores</title>
    <link rel="stylesheet" href="proveedores.css">
</head>
<body>

    <div class="form-container">
        <h2>Registro de Proveedores</h2>

        <!-- Puedes mostrar aquí mensajes de éxito o error si lo deseas -->
        <!-- <div class="success-message">Proveedor guardado con éxito.</div> -->
        <!-- <div class="error-message">Hubo un error al guardar el proveedor.</div> -->

        <form action="guardar_proveedor.php" method="POST">
            
            <label for="nombre">Nombre del Proveedor</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="contacto">Persona de Contacto</label>
            <input type="text" id="contacto" name="contacto" required>

            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" pattern="[0-9]{8,12}" required>

            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email">

            <label for="direccion">Dirección</label>
            <textarea id="direccion" name="direccion" rows="3"></textarea>

            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria">
                <option value="Carnes">Carnes</option>
                <option value="Verduras">Verduras</option>
                <option value="Lácteos">Lácteos</option>
                <option value="Bebidas">Bebidas</option>
                <option value="Otros">Otros</option>
            </select>

            <button type="submit">Guardar Proveedor</button>
        </form>
    </div>

</body>
</html>
