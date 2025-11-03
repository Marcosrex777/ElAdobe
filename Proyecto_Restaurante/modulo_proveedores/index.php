<?php
session_start();

// Proteger acceso
if (!isset($_SESSION['usuario'])) {
    header("Location: ../loginEmpleados.php");
    exit();
}

$rol = $_SESSION['nombre_rol']; // Ejemplo: "Mesero", "Administrador"
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Proveedores</title>
    <link rel="stylesheet" href="style.css">

<style>
/* === Encabezado igual al de COMPRAS, centrado correctamente === */
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
}

header {
    background-color: #333;
    color: white;
    padding: 1rem 4rem;          /* 馃敼 Menos separaci贸n horizontal */
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-sizing: border-box;
}

.company-name {
    font-size: 1.5rem;
}

/* 馃敼 Enlaces visibles y con espacio justo */
.cerrarSesion {
    color: white;
    text-decoration: none;
    margin-left: 1.2rem;
}

.cerrarSesion:hover {
    color: #d4b28c;
}

/* 馃敼 Baja el contenido para que el header no lo tape */
.contenedor {
    margin-top: 7rem;
}
</style>

<header>
  <div class="company-name">El Adobe</div>
  <div>
      <a href="../menu_modulos.php" class="cerrarSesion">Inicio</a>
      <a href="../logout.php" class="cerrarSesion">Cerrar sesión</a>
  </div>
</header>





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
