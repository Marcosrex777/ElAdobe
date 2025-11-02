<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Proveedores</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="contenedor">
        <div class="formulario">
            <h2>Formulario para Proveedores</h2>
            <input type="hidden" id="id_proveedor">

            <label>Nombre del Proveedor:</label>
            <input type="text" id="nombre_proveedor">

            <label>Persona de Contacto:</label>
            <input type="text" id="persona_contacto">

            <label>Teléfono:</label>
            <input type="text" id="telefono">

            <label>Correo Electrónico:</label>
            <input type="email" id="correo">

            <label>Dirección:</label>
            <input type="text" id="direccion">

            <label>Categoría:</label>
            <input type="text" id="categoria">

            <label>Estado:</label>
            <select id="estado">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>

            <button id="guardarBtn">Guardar Datos</button>
            <button onclick="location.reload()">Refrescar Página</button>
        </div>

        <div class="tabla">
            <h2>Listado de Proveedores</h2>
            <button id="traerBtn">Traer Proveedores</button>
            <table id="tablaProveedores">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Opción</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
