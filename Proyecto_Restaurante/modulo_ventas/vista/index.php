<?php
require_once("../Controlador/MenuControlador.php");

// Instancia el controlador del menú
$controlador = new MenuControlador();
$menuHTML = $controlador->obtenerMenu();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Ventas</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Librería jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<header>
    <h1>🍽️ Sistema de Ventas</h1>
    <nav>
        <a href="#">🏠 Inicio</a>
        <a href="#">Mesas</a>
        <a href="#">Pedidos</a>
    </nav>
</header>

<main>
    <!-- Sección Mesas -->
    <section id="mesas-section">
        <h2>Mesas disponibles</h2>
        <div id="contenedor-mesas">
            <!-- Las mesas se cargarán vía AJAX -->
        </div>
    </section>

    <!-- Sección Menú -->
    <section id="menu-section">
        <h2>Menú</h2>
        <div id="contenedor-menu">
            <?= $menuHTML ?>
        </div>
    </section>

    <!-- Sección Pedido -->
    <section id="pedido-section">
        <h2>Resumen del pedido</h2>
        <table id="tabla-pedido">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <!-- Productos del pedido -->
            </tbody>
        </table>

        <div id="total-pedido">
            Total: Q<span id="total">0.00</span>
        </div>

        <div id="estado-pedido">
            Estado: <span id="label-estado">Pendiente</span>
        </div>

        <button id="btn-enviar">Enviar Pedido</button>
    </section>
</main>

<footer>
    <p>© 2025 Sistema de Ventas | Restaurante XYZ</p>
</footer>

<script src="js/app.js"></script>
</body>
</html>
