<?php
// vista/index.php - SOLO VISTA (Presentación)
session_start();

// Incluir controladores
require_once("../Controlador/MenuControlador.php");
require_once("../Controlador/PedidoControlador.php");
require_once("../Modelo/MesaDAO.php");

$menuControlador = new MenuControlador();
$pedidoControlador = new PedidoControlador();
$mesaDAO = new MesaDAO();

// Obtener datos del modelo
$mesas = $mesaDAO->listarMesas();
$id_usuario = 2; // ID del mesero logueado
$id_mesa = isset($_GET['mesa']) ? intval($_GET['mesa']) : null;

// Verificar si hay mensajes en sesión
$mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos - Restaurante El Adobe</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pedidos.css">
</head>
<body>

<header>
    <h1>🍽️ Restaurante El Adobe</h1>
    <nav>
        <a href="index.php">🏠 Inicio</a>
        <a href="cocina.php" target="_blank">🏭 Cocina</a>
        <a href="#">🚪 Salir</a>
    </nav>
</header>

<main>
    <!-- Mostrar mensajes -->
    <?php if ($mensaje): ?>
        <div class="mensaje-sistema"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- Sección Mesas -->
    <section>
        <h2>📦 Mesas Disponibles</h2>
        <div id="contenedor-mesas">
            <?php foreach ($mesas as $m): ?>
                <form method="GET" style="display:inline;">
                    <input type="hidden" name="mesa" value="<?= $m['id_mesa'] ?>">
                    <button type="submit" class="mesa-btn <?= ($m['estado'] === 'ocupada') ? 'ocupada' : 'libre' ?>">
                        Mesa <?= $m['numero'] ?><br>
                        <small>(<?= $m['estado'] ?>)</small>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Sección Pedido Actual -->
    <section>
        <h2>📋 Pedido Actual</h2>
        <div id="pedido">
            <?php 
            if ($id_mesa) {
                echo $pedidoControlador->mostrarPedidoMesa($id_mesa, $id_usuario);
            } else {
                echo "<p>👆 Selecciona una mesa para ver su pedido.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Sección Menú -->
    <section>
        <h2>📖 Menú Disponible</h2>
        <div id="menu">
            <?= $menuControlador->obtenerMenu(); ?>
        </div>
    </section>
</main>


<script>
// Script para agregar platillos al pedido
document.querySelectorAll('.agregar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id_menu = btn.dataset.id;
        const precio = btn.dataset.precio;
        const cantidad = prompt("Ingrese cantidad:", 1);
        
        if (cantidad > 0 && <?= $id_mesa ?: '0' ?>) {
            window.location.href = `../controlador/enrutador_controlador.php?mesa=<?= $id_mesa ?>&accion=agregar&id_menu=${id_menu}&cantidad=${cantidad}&precio=${precio}`;
        } else if (!<?= $id_mesa ?: '0' ?>) {
            alert("Por favor, selecciona una mesa antes de agregar un platillo.");
        } else if (cantidad <= 0) {
            alert("La cantidad debe ser mayor a 0.");
        }
    });
});

// Función global para eliminar platillos
function eliminarPlatillo(idDetalle, idPedido) {
    if (confirm('¿Estás seguro de que quieres eliminar este platillo del pedido?')) {
        window.location.href = `../controlador/enrutador_controlador.php?accion=eliminar_platillo&id_detalle=${idDetalle}&id_pedido=${idPedido}&mesa=<?= $id_mesa ?>`;
    }
}

// Validación para evitar enviar pedidos vacíos
document.addEventListener('DOMContentLoaded', function() {
    const btnEnviar = document.getElementById('btn-enviar-pedido');
    if (btnEnviar) {
        btnEnviar.addEventListener('click', function(e) {
            const tabla = document.querySelector('.tabla-pedido');
            if (!tabla || tabla.querySelectorAll('tbody tr').length === 0) {
                e.preventDefault();
                alert('❌ No se puede enviar un pedido vacío. Agregue al menos un platillo antes de enviar.');
                return false;
            }
        });
    }
});

// Auto-actualización cada 30 segundos para ver cambios de estado
setInterval(() => {
    if (<?= $id_mesa ?: '0' ?>) {
        window.location.href = `index.php?mesa=<?= $id_mesa ?>`;
    }
}, 30000);
</script>
<!-- Agrega esto al final de index.php antes de </body> -->
<script src="../js/pdf-generator.js"></script>
<!-- Agrega esto temporalmente en index.php para probar -->
<script src="../js/pdf-generator.js"></script>
<script>
// Función para probar manualmente
function probarGeneracionPDF() {
    // Reemplaza con un número de factura real de tu base de datos
    generarFacturaPDF('FAC-20241211-12345');
}
</script>
<!-- En index.php, agrega esto antes de </body> -->
<!-- En index.php, cambia esta línea: -->
<script src="js/pdf-generator.js"></script>

<!-- Reemplaza todo el script al final de index.php con esto: -->
<script src="js/pdf-generator.js"></script>
<!-- En index.php, agrega este script mejorado: -->
<script>
// Función para probar manualmente - VERSIÓN MEJORADA
function probarPDFManual() {
    console.log('Probando PDF manualmente...');
    
    // Cargar dinámicamente SIEMPRE para evitar duplicación
    console.log('📥 Cargando script PDF dinámicamente...');
    const script = document.createElement('script');
    script.src = 'js/pdf-generator.js';
    script.onload = function() {
        console.log('✅ Script PDF cargado dinámicamente');
        const numeroFactura = prompt('Ingresa el número de factura para probar:');
        if (numeroFactura) {
            generarFacturaPDF(numeroFactura);
        }
    };
    script.onerror = function() {
        console.error('❌ Error cargando script PDF');
        alert('Error al cargar el generador de PDF');
    };
    document.head.appendChild(script);
}

// Botón de prueba MEJORADO
document.addEventListener('DOMContentLoaded', function() {
    // Solo mostrar en desarrollo
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        const botonPrueba = document.createElement('button');
        botonPrueba.textContent = 'Generar Factura';
        botonPrueba.style.position = 'fixed';
        botonPrueba.style.top = '60px';
        botonPrueba.style.right = '10px';
        botonPrueba.style.zIndex = '10000';
        botonPrueba.style.padding = '12px 20px';
        botonPrueba.style.background = '#28a745';
        botonPrueba.style.color = 'white';
        botonPrueba.style.border = 'none';
        botonPrueba.style.borderRadius = '8px';
        botonPrueba.style.cursor = 'pointer';
        botonPrueba.style.fontWeight = 'bold';
        botonPrueba.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        botonPrueba.onclick = probarPDFManual;
        document.body.appendChild(botonPrueba);
        
        console.log('✅ Botón de prueba PDF agregado');
    }
});
</script>
</body>
</html>
