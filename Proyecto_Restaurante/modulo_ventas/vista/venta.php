<?php
// vista/venta.php - SOLO VISTA (Presentación)
session_start();

// Inicializar variables con valores por defecto
$mensaje = '';
$mesas = [];
$id_mesa = null;
$id_usuario = 2; // ID del mesero logueado

try {
    // Incluir controladores con verificación
    if (!class_exists('MenuControlador')) {
        require_once("../Controlador/MenuControlador.php");
    }
    if (!class_exists('PedidoControlador')) {
        require_once("../Controlador/PedidoControlador.php");
    }
    if (!class_exists('MesaDAO')) {
        require_once("../Modelo/MesaDAO.php");
    }

    // Instanciar controladores con verificación
    $menuControlador = new MenuControlador();
    $pedidoControlador = new PedidoControlador();
    $mesaDAO = new MesaDAO();

    // Obtener datos del modelo con manejo de errores
    $mesas = $mesaDAO->listarMesas();
    
    // Verificar si es un array, si no, inicializar como array vacío
    if (!is_array($mesas)) {
        $mesas = [];
    }
    
    // Obtener mesa de GET si existe
    if (isset($_GET['mesa'])) {
        $id_mesa = intval($_GET['mesa']);
    }

    // Verificar si hay mensajes en sesión
    if (isset($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }

} catch (Exception $e) {
    // Manejar errores de carga
    error_log("Error en venta.php: " . $e->getMessage());
    $mensaje = "Error al cargar los datos: " . $e->getMessage();
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
        <a href="../controlador/LoginControlador.php?accion=logout">🚪 Salir</a>
    </nav>
</header>

<main>
    <!-- Mostrar mensajes -->
    <?php if (!empty($mensaje)): ?>
        <div class="mensaje-sistema"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- Sección Mesas -->
    <section>
        <h2>📦 Mesas Disponibles</h2>
        <div id="contenedor-mesas">
            <?php if (!empty($mesas)): ?>
                <?php foreach ($mesas as $m): ?>
                    <form method="GET" style="display:inline;">
                        <input type="hidden" name="mesa" value="<?= $m['id_mesa'] ?>">
                        <button type="submit" class="mesa-btn <?= ($m['estado'] === 'ocupada') ? 'ocupada' : 'libre' ?>">
                            Mesa <?= htmlspecialchars($m['numero']) ?><br>
                            <small>(<?= htmlspecialchars($m['estado']) ?>)</small>
                        </button>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay mesas disponibles en este momento.</p>
            <?php endif; ?>
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
    <!-- En cualquier parte de venta.php donde quieras poner el botón -->
<div class="busqueda-factura" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
    <h3>🔍 Buscar Factura</h3>
    <div style="display: flex; gap: 10px; align-items: center;">
        <input type="text" id="numero-factura" placeholder="Número de factura (ej: FAC-20241201-001)" 
               style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        <button onclick="buscarYVerFactura()" class="btn-ver-factura">
            🧾 Ver Factura
        </button>
    </div>
    <small style="color: #666;">Ejemplo: FAC-20241201-12345</small>
</div>
</main>

<script>
// ============================================
// FUNCIONALIDAD DEL ACORDEÓN
// ============================================
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

    // Verificar inventario para todos los platillos después de 1 segundo
    setTimeout(() => {
        document.querySelectorAll('.plato-card').forEach(platoCard => {
            onCantidadChange(platoCard);
        });
    }, 1000);
});
// ============================================
// FUNCIÓN PARA ELIMINAR PLATILLOS DEL PEDIDO
// ============================================

function eliminarPlatillo(id_detalle_pedido, id_pedido) {
    if (confirm('¿Estás seguro de eliminar este platillo del pedido?')) {
        // Usar el id_mesa de PHP, con valor por defecto 0 si es null
        const idMesa = <?= $id_mesa ?: 0 ?>;
        
        if (!idMesa) {
            alert("Error: No se ha seleccionado una mesa.");
            return;
        }

        // Mostrar feedback visual
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Eliminando...';
        button.disabled = true;

        // Redireccionar para eliminar el platillo
        window.location.href = `../controlador/enrutador_controlador.php?mesa=${idMesa}&accion=eliminar_platillo&id_detalle=${id_detalle_pedido}&id_pedido=${id_pedido}`;
    }
}

// ============================================
// CONTROL DE CANTIDAD Y VERIFICACIÓN DE INVENTARIO
// ============================================

function aumentarCantidad(button) {
    const display = button.parentElement.querySelector('.cantidad-display');
    let cantidad = parseInt(display.textContent);
    display.textContent = cantidad + 1;
    
    const platoCard = button.closest('.plato-card');
    onCantidadChange(platoCard);
}

function disminuirCantidad(button) {
    const display = button.parentElement.querySelector('.cantidad-display');
    let cantidad = parseInt(display.textContent);
    if (cantidad > 1) {
        display.textContent = cantidad - 1;
        
        const platoCard = button.closest('.plato-card');
        onCantidadChange(platoCard);
    }
}

async function verificarInventario(idMenu, cantidad) {
    try {
        const response = await fetch(`../controlador/enrutador_controlador.php?accion=verificar_inventario&id_menu=${idMenu}&cantidad=${cantidad}`);
        return await response.json();
    } catch (error) {
        console.error('Error verificando inventario:', error);
        return { suficiente: false, faltantes: [] };
    }
}

function actualizarEstadoBoton(button, inventario) {
    if (inventario.suficiente) {
        button.disabled = false;
        button.style.background = '#8b5e3c';
        button.innerHTML = '➕ Agregar';
        button.title = 'Agregar al pedido';
    } else {
        button.disabled = true;
        button.style.background = '#dc3545';
        button.innerHTML = '🚫 Sin Stock';
        
        // Tooltip con detalles de lo que falta
        const faltantes = inventario.faltantes.map(f => 
            `${f.ingrediente}: requieren ${f.requerido ? f.requerido.toFixed(2) : 0}${f.unidad || 'unidades'}, hay ${f.disponible ? f.disponible.toFixed(2) : 0}`
        ).join('\n');
        button.title = `Faltan ingredientes:\n${faltantes}`;
    }
}

// Verificar inventario cuando cambia la cantidad
function onCantidadChange(platoCard) {
    const button = platoCard.querySelector('.agregar-btn');
    const cantidad = parseInt(platoCard.querySelector('.cantidad-display').textContent);
    const idMenu = button.dataset.id;
    
    verificarInventario(idMenu, cantidad).then(inventario => {
        actualizarEstadoBoton(button, inventario);
    });
}

// Agregar al pedido - VERSIÓN MEJORADA
async function agregarAlPedido(button) {
    const platoCard = button.closest('.plato-card');
    const cantidad = parseInt(platoCard.querySelector('.cantidad-display').textContent);
    const idMenu = button.dataset.id;
    const precio = button.dataset.precio;
    const nombre = button.dataset.nombre;
    
    // Usar el id_mesa de PHP, con valor por defecto 0 si es null
    const idMesa = <?= $id_mesa ?: 0 ?>;
    
    if (!idMesa) {
        alert("Por favor, selecciona una mesa antes de agregar un platillo.");
        return;
    }
    
    // Verificar inventario una última vez
    const inventario = await verificarInventario(idMenu, cantidad);
    if (!inventario.suficiente) {
        const mensaje = inventario.faltantes.map(f => 
            `• ${f.ingrediente}: necesitan ${f.requerido ? f.requerido.toFixed(2) : 0}${f.unidad || 'unidades'}, solo hay ${f.disponible ? f.disponible.toFixed(2) : 0}`
        ).join('\n');
        
        alert(`❌ No hay suficiente inventario para preparar ${nombre}:\n\n${mensaje}`);
        return;
    }
    
    // Confirmación visual
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ Procesando...';
    button.disabled = true;
    
    try {
        // Redireccionar para agregar el platillo
        window.location.href = `../controlador/enrutador_controlador.php?mesa=${idMesa}&accion=agregar&id_menu=${idMenu}&cantidad=${cantidad}&precio=${precio}`;
    } catch (error) {
        console.error('Error al agregar pedido:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        alert('Error al procesar el pedido');
    }
}

// ============================================
// BÚSQUEDA Y FILTRADO
// ============================================

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

// ============================================
// ELIMINAR PLATILLOS DEL PEDIDO
// ============================================

function eliminarPlatillo(id_detalle_pedido, id_pedido) {
    if (confirm('¿Estás seguro de eliminar este platillo del pedido?')) {
        const idMesa = <?= $id_mesa ?: 0 ?>;
        
        if (!idMesa) {
            alert("Error: No se ha seleccionado una mesa.");
            return;
        }

        // Feedback visual
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Eliminando...';
        button.disabled = true;

        // Redirección para eliminar
        window.location.href = `../controlador/enrutador_controlador.php?mesa=${idMesa}&accion=eliminar_platillo&id_detalle=${id_detalle_pedido}&id_pedido=${id_pedido}`;
    }
}

// ============================================
// INICIALIZACIÓN
// ============================================

// Verificar que el script se cargó
console.log('✅ Funciones de pedido cargadas correctamente');

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
// ============================================
// FUNCIONES PARA FACTURAS PDF
// ============================================

/**
 * Busca y muestra una factura por número
 */
function buscarYVerFactura() {
    const numeroFactura = document.getElementById('numero-factura').value.trim();
    
    if (!numeroFactura) {
        alert('⚠️ Por favor ingresa un número de factura');
        return;
    }

    // Validar formato básico de factura
    if (!numeroFactura.startsWith('FAC-')) {
        if (!confirm('El número de factura no parece tener el formato estándar (FAC-...). ¿Continuar?')) {
            return;
        }
    }

    console.log('🔍 Buscando factura:', numeroFactura);
    generarFacturaPDF(numeroFactura);
}

/**
 * Obtiene y muestra la factura del pedido actual de una mesa
 */
async function verFacturaPedido(idMesa) {
    try {
        console.log('📋 Buscando factura para mesa:', idMesa);
        
        // Mostrar loading
        const originalText = event.target.innerHTML;
        event.target.innerHTML = '⏳ Buscando factura...';
        event.target.disabled = true;

        // Primero obtener el pedido de la mesa
        const response = await fetch(`../controlador/enrutador_controlador.php?accion=obtener_factura_mesa&mesa=${idMesa}`);
        const data = await response.json();

        if (data.success && data.numero_factura) {
            console.log('✅ Factura encontrada:', data.numero_factura);
            generarFacturaPDF(data.numero_factura);
        } else {
            alert('❌ No se encontró una factura para esta mesa o el pedido no ha sido facturado aún.');
        }

    } catch (error) {
        console.error('Error al buscar factura:', error);
        alert('❌ Error al buscar la factura: ' + error.message);
    } finally {
        // Restaurar botón
        setTimeout(() => {
            event.target.innerHTML = originalText;
            event.target.disabled = false;
        }, 1000);
    }
}

/**
 * Función alternativa si prefieres buscar por ID de pedido
 */
async function verFacturaPorPedido(idPedido) {
    try {
        const response = await fetch(`../controlador/enrutador_controlador.php?accion=obtener_factura_pedido&id_pedido=${idPedido}`);
        const data = await response.json();
        
        if (data.success && data.numero_factura) {
            generarFacturaPDF(data.numero_factura);
        } else {
            alert('No se encontró factura para este pedido');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al buscar factura');
    }
}
</script>

<!-- Solo una inclusión del script PDF -->
<script src="js/pdf-generator.js"></script>

</body>
</html>