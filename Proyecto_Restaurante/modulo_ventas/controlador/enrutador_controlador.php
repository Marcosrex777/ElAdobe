<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../loginEmpleados.php");
    exit();
}

// CORREGIR TODAS LAS RUTAS DE INCLUSIÓN
require_once(__DIR__ . "/PedidoControlador.php");
require_once(__DIR__ . "/../Modelo/InventarioDAO.php");

$pedidoControlador = new PedidoControlador();

$accion = $_GET['accion'] ?? '';
$id_mesa = $_GET['mesa'] ?? null;

// ✅ USAR SIEMPRE ID 6 (usuario marcos)
$id_usuario = 6;

switch ($accion) {
    case 'agregar':
        $id_menu = $_GET['id_menu'] ?? null;
        $cantidad = $_GET['cantidad'] ?? 1;
        $precio = $_GET['precio'] ?? 0;
        
        if ($id_mesa && $id_menu) {
            $pedidoControlador->agregarPlatillo($id_mesa, $id_usuario, $id_menu, $cantidad, $precio);
        }
        header("Location: ../vista/venta.php?mesa=$id_mesa");
        break;

    case 'eliminar_platillo':
        $id_detalle = $_GET['id_detalle'] ?? null;
        $id_pedido = $_GET['id_pedido'] ?? null;
        
        if ($id_detalle && $id_pedido) {
            $pedidoControlador->eliminarPlatillo($id_detalle, $id_pedido);
        }
        header("Location: ../vista/venta.php?mesa=$id_mesa");
        break;

    case 'enviar':
        if ($id_mesa) {
            $pedidoControlador->enviarPedido($id_mesa);
        }
        header("Location: ../vista/venta.php?mesa=$id_mesa");
        break;

    case 'marcar_entregado':
        if ($id_mesa) {
            $pedidoControlador->marcarEntregado($id_mesa);
        }
        header("Location: ../vista/venta.php?mesa=$id_mesa");
        break;

    case 'finalizar':
        if ($id_mesa) {
            $pedidoControlador->cerrarCuenta($id_mesa, $id_usuario);
        }
        header("Location: ../vista/venta.php?mesa=$id_mesa");
        break;

    case 'verificar_inventario':
        $id_menu = intval($_GET['id_menu'] ?? 0);
        $cantidad = intval($_GET['cantidad'] ?? 1);
        
        if ($id_menu > 0) {
            $inventarioDAO = new InventarioDAO();
            $resultado = $inventarioDAO->verificarInventarioRapido($id_menu, $cantidad);
            
            // ENVIAR SOLO JSON
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['suficiente' => false, 'faltantes' => []]);
            exit;
        }
        break;

    default:
        header("Location: ../vista/venta.php");
        break;
}
?>