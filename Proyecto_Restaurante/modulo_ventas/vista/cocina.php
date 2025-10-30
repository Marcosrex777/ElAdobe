<?php
require_once("../Controlador/CocinaControlador.php");
$cocinaControlador = new CocinaControlador();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cocina - Restaurante El Adobe</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>
<body>
    <header>
        <h1>🏭 Cocina - Restaurante El Adobe</h1>
        <div class="estado-cocina">
            <span id="contador-pedidos">0 pedidos activos</span>
            <button onclick="actualizarPedidos()">🔄 Actualizar</button>
        </div>
    </header>

    <main>
        <section id="seccion-pedidos">
            <h2>Pedidos Activos</h2>
            <div id="contenedor-pedidos">
                <?= $cocinaControlador->obtenerPedidosCocina() ?>
            </div>
        </section>

        <section id="seccion-metricas">
            <h2>📊 Métricas de Tiempo</h2>
            <div id="metricas-tiempo">
                <!-- Las métricas se cargarán via AJAX -->
            </div>
        </section>
    </main>

    <script>
    // Actualizar pedidos cada 30 segundos
    setInterval(actualizarPedidos, 30000);

    function actualizarPedidos() {
        fetch('../Controlador/CocinaControlador.php?accion=obtener_pedidos')
            .then(response => response.text())
            .then(html => {
                document.getElementById('contenedor-pedidos').innerHTML = html;
                actualizarContador();
            });
    }

    function cambiarEstadoPedido(id_pedido, estado) {
        if (confirm(`¿Cambiar estado del pedido #${id_pedido} a "${estado}"?`)) {
            fetch(`../Controlador/CocinaControlador.php?accion=cambiar_estado&id_pedido=${id_pedido}&estado=${estado}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        actualizarPedidos();
                    } else {
                        alert('Error al cambiar estado');
                    }
                });
        }
    }

    function cancelarPedido(id_pedido) {
        if (confirm(`¿Estás seguro de cancelar el pedido #${id_pedido}?`)) {
            fetch(`../Controlador/CocinaControlador.php?accion=cancelar&id_pedido=${id_pedido}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        actualizarPedidos();
                    } else {
                        alert('Error al cancelar pedido');
                    }
                });
        }
    }

    function actualizarContador() {
        const pedidos = document.querySelectorAll('.pedido-cocina-card');
        document.getElementById('contador-pedidos').textContent = `${pedidos.length} pedidos activos`;
    }

    // Configuración de Pusher para notificaciones en tiempo real
    const pusher = new Pusher('TU_APP_KEY', {
        cluster: 'TU_CLUSTER'
    });

    const channel = pusher.subscribe('cocina');
    channel.bind('pedido-actualizado', function(data) {
        actualizarPedidos();
    });

    // Inicializar
    actualizarContador();
    </script>
</body>
</html>