<?php
// vista/venta.php - SOLO VISTA (Presentación)
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
    <link rel="stylesheet" href="css/menu-interactivo.css">
</head>
<body>

<header>
    <h1>🍽️ Restaurante El Adobe</h1>
    <nav>
        <a href="venta.php">🏠 Inicio</a>
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
    <section id="menu-interactivo">
        <h2>📖 Menú Interactivo</h2>
        <div class="menu-herramientas">
            <div class="buscador-container">
                <input type="text" 
                    id="buscador-menu" 
                    placeholder="🔍 Buscar en el menú..." 
                    onkeyup="buscarEnMenu()">
                <div class="filtros-rapidos">
                    <button onclick="filtrarPorCategoria('todas')" class="filtro-btn active">Todos</button>
                    <button onclick="filtrarPorCategoria('desayunos')" class="filtro-btn">🍳 Desayunos</button>
                    <button onclick="filtrarPorCategoria('almuerzos-y-cenas')" class="filtro-btn">🍽️ Principal</button>
                    <button onclick="filtrarPorCategoria('bebidas')" class="filtro-btn">🥤 Bebidas</button>
                </div>
            </div>
        </div>
        <div class="menu-accordion">
            <?= $menuControlador->obtenerMenuInteractivo() ?>
        </div>
    </section>
</main>

 </body>
<script>
// Funcionalidad del acordeón
document.addEventListener('DOMContentLoaded', function() {
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const isActive = content.classList.contains('active');
            
            // Cerrar todos los acordeones
            document.querySelectorAll('.accordion-content').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelectorAll('.accordion-header').forEach(item => {
                item.classList.remove('active');
            });
            
            // Abrir el acordeón clickeado si no estaba activo
            if (!isActive) {
                content.classList.add('active');
                this.classList.add('active');
            }
        });
    });
    
    // Abrir la primera categoría por defecto
    if (accordionHeaders.length > 0) {
        accordionHeaders[0].click();
    }
});

// Control de cantidad
function aumentarCantidad(button) {
    const display = button.parentElement.querySelector('.cantidad-display');
    let cantidad = parseInt(display.textContent);
    display.textContent = cantidad + 1;
}

function disminuirCantidad(button) {
    const display = button.parentElement.querySelector('.cantidad-display');
    let cantidad = parseInt(display.textContent);
    if (cantidad > 1) {
        display.textContent = cantidad - 1;
    }
}

// Agregar al pedido
function agregarAlPedido(button) {
    const platoCard = button.closest('.plato-card');
    const cantidad = parseInt(platoCard.querySelector('.cantidad-display').textContent);
    const idMenu = button.dataset.id;
    const precio = button.dataset.precio;
    const nombre = button.dataset.nombre;
    
    if (!<?= $id_mesa ?: '0' ?>) {
        alert("Por favor, selecciona una mesa antes de agregar un platillo.");
        return;
    }
    
    if (cantidad <= 0) {
        alert("La cantidad debe ser mayor a 0.");
        return;
    }
    
    // Confirmación visual
    const originalText = button.innerHTML;
    button.innerHTML = '✅ Agregado';
    button.style.background = '#28a745';
    button.disabled = true;
    
    // Redireccionar para agregar el platillo
    setTimeout(() => {
        window.location.href = `../controlador/enrutador_controlador.php?mesa=<?= $id_mesa ?>&accion=agregar&id_menu=${idMenu}&cantidad=${cantidad}&precio=${precio}`;
    }, 800);
    
    // Restaurar botón después de 2 segundos (en caso de error)
    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = '';
        button.disabled = false;
    }, 2000);
}

// Buscar en el menú
function buscarEnMenu() {
    const searchTerm = document.getElementById('buscador-menu').value.toLowerCase();
    const platos = document.querySelectorAll('.plato-card');
    const categorias = document.querySelectorAll('.accordion-categoria');
    
    let resultadosEncontrados = false;
    
    categorias.forEach(categoria => {
        const platosCategoria = categoria.querySelectorAll('.plato-card');
        let categoriaTieneResultados = false;
        
        platosCategoria.forEach(plato => {
            const nombre = plato.querySelector('.plato-nombre').textContent.toLowerCase();
            const descripcion = plato.querySelector('.plato-descripcion').textContent.toLowerCase();
            
            if (nombre.includes(searchTerm) || descripcion.includes(searchTerm)) {
                plato.style.display = 'block';
                categoriaTieneResultados = true;
                resultadosEncontrados = true;
            } else {
                plato.style.display = 'none';
            }
        });
        
        // Mostrar/ocultar categoría según si tiene resultados
        if (categoriaTieneResultados) {
            categoria.style.display = 'block';
            // Abrir la categoría si tiene resultados
            const header = categoria.querySelector('.accordion-header');
            const content = categoria.querySelector('.accordion-content');
            if (!content.classList.contains('active')) {
                header.click();
            }
        } else {
            categoria.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados
    const mensajeNoResultados = document.getElementById('mensaje-no-resultados');
    if (!resultadosEncontrados && searchTerm) {
        if (!mensajeNoResultados) {
            const mensaje = document.createElement('div');
            mensaje.id = 'mensaje-no-resultados';
            mensaje.className = 'mensaje-sistema';
            mensaje.textContent = 'No se encontraron platillos que coincidan con tu búsqueda.';
            document.querySelector('.menu-accordion').prepend(mensaje);
        }
    } else if (mensajeNoResultados) {
        mensajeNoResultados.remove();
    }
}

// Filtrar por categoría (opcional)
function filtrarPorCategoria(categoria) {
    const todasCategorias = document.querySelectorAll('.accordion-categoria');
    
    todasCategorias.forEach(cat => {
        if (categoria === 'todas' || cat.dataset.categoria === categoria) {
            cat.style.display = 'block';
        } else {
            cat.style.display = 'none';
        }
    });
}
</script>
<!-- Agrega esto al final de venta.php antes de </body> -->
<script src="../js/pdf-generator.js"></script>
<!-- Agrega esto temporalmente en venta.php para probar -->
<script src="../js/pdf-generator.js"></script>
<script>
// Función para probar manualmente
function probarGeneracionPDF() {
    // Reemplaza con un número de factura real de tu base de datos
    generarFacturaPDF('FAC-20241211-12345');
}
</script>
<!-- En venta.php, agrega esto antes de </body> -->
<!-- En venta.php, cambia esta línea: -->
<script src="js/pdf-generator.js"></script>

<!-- Reemplaza todo el script al final de venta.php con esto: -->
<script src="js/pdf-generator.js"></script>
<!-- En venta.php, agrega este script mejorado: -->
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
