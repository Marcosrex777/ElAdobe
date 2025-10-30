<?php
// controlador/router.php - CONTROLADOR FRONTAL
session_start();

require_once("PedidoControlador.php");
require_once("MenuControlador.php");
require_once("../Modelo/MesaDAO.php");

// Función auxiliar para redirigir con mensaje opcional
function redirigir($mesa = null, $mensaje = null) {
    if ($mensaje) {
        $_SESSION['mensaje'] = $mensaje;
    }
    $url = "../vista/index.php" . ($mesa ? "?mesa=" . intval($mesa) : "");
    header("Location: $url");
    exit;
}

// Obtener parámetros
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$id_mesa = isset($_GET['mesa']) ? intval($_GET['mesa']) : null;
$id_usuario = 2; // ID del mesero logueado

// Instanciar controladores
$pedidoControlador = new PedidoControlador();
$menuControlador = new MenuControlador();
$mesaDAO = new MesaDAO();

// Procesar acciones
switch ($accion) {

    case 'enviar':
        if ($id_mesa) {
            $result = $pedidoControlador->enviarPedido($id_mesa);
            $mensaje = $result ? "✅ Pedido enviado a cocina correctamente." : "❌ No se puede enviar un pedido vacío.";
            redirigir($id_mesa, $mensaje);
        }
        break;

    case 'eliminar_platillo':
        $id_detalle = isset($_GET['id_detalle']) ? intval($_GET['id_detalle']) : null;
        $id_pedido = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : null;
        if ($id_detalle && $id_pedido && $id_mesa) {
            $result = $pedidoControlador->eliminarPlatillo($id_detalle, $id_pedido);
            $mensaje = $result ? "✅ Platillo eliminado correctamente." : "❌ No se pudo eliminar el platillo.";
            redirigir($id_mesa, $mensaje);
        }
        break;

    case 'marcar_entregado':
        if ($id_mesa) {
            $pedidoControlador->marcarEntregado($id_mesa);
            redirigir($id_mesa, "✅ Pedido marcado como entregado.");
        }
        break;

    case 'finalizar':
        if ($id_mesa) {
            $pedidoControlador->cerrarCuenta($id_mesa, $id_usuario);
            // Se asume que cerrarCuenta maneja la redirección, si no:
            // redirigir($id_mesa, "✅ Cuenta finalizada correctamente.");
        }
        break;

    case 'agregar':
        if ($id_mesa) {
            $id_menu = isset($_GET['id_menu']) ? intval($_GET['id_menu']) : null;
            $cantidad = isset($_GET['cantidad']) ? intval($_GET['cantidad']) : null;
            $precio = isset($_GET['precio']) ? floatval($_GET['precio']) : null;

            if ($id_menu && $cantidad && $precio) {
                $pedidoControlador->agregarPlatillo($id_mesa, $id_usuario, $id_menu, $cantidad, $precio);
                redirigir($id_mesa);
            } else {
                redirigir($id_mesa, "❌ Parámetros incompletos para agregar platillo.");
            }
        }
        break;

    default:
        // Si no hay acción específica, redirigir a la vista principal
        redirigir($id_mesa);
}
?>
