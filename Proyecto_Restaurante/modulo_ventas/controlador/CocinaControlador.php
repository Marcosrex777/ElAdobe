<?php
require_once("../Modelo/PedidoDAO.php");
require_once("../Modelo/NotificacionDAO.php");

class CocinaControlador {
    private $pedidoDAO;
    private $notificacionDAO;

    public function __construct() {
        $this->pedidoDAO = new PedidoDAO();
        $this->notificacionDAO = new NotificacionDAO();
    }

// obtiene los pedidos para cocina
    public function obtenerPedidosCocina() {
        $pedidos = $this->pedidoDAO->obtenerPedidosPorEstado(['enviado', 'preparando']);
        
        if (empty($pedidos)) {
            return "<p>No hay pedidos en cocina en este momento.</p>";
        }

        $html = "<div class='cocina-container'>";
        foreach ($pedidos as $pedido) {
            $detalles = $this->pedidoDAO->obtenerDetalles($pedido['id_pedido']);
            
            $html .= "
                <div class='pedido-cocina-card' data-pedido='{$pedido['id_pedido']}'>
                    <div class='pedido-header'>
                        <h3>📦 Pedido #{$pedido['id_pedido']} - Mesa {$pedido['id_mesa']}</h3>
                        <span class='estado-badge {$pedido['estado']}'>{$pedido['estado']}</span>
                    </div>
                    <div class='pedido-detalles'>
                        <h4>Platillos:</h4>
                        <ul>";
            
            foreach ($detalles as $detalle) {
                $html .= "<li>{$detalle['cantidad']}x {$detalle['nombre']}</li>";
            }
            
            $html .= "
                        </ul>
                    </div>
                    <div class='pedido-acciones'>";
            
            // Botones según el estado actual
            if ($pedido['estado'] == 'enviado') {
                $html .= "
                    <button class='btn-preparar' onclick='cambiarEstadoPedido({$pedido['id_pedido']}, \"preparando\")'>
                        🍳 Iniciar Preparación
                    </button>
                    <button class='btn-cancelar' onclick='cancelarPedido({$pedido['id_pedido']})'>
                        ❌ Cancelar
                    </button>
                ";
            } elseif ($pedido['estado'] == 'preparando') {
                $html .= "
                    <button class='btn-listo' onclick='cambiarEstadoPedido({$pedido['id_pedido']}, \"listo\")'>
                        ✅ Marcar como Listo
                    </button>
                ";
            }
            
            $html .= "
                    </div>
                    <div class='pedido-tiempos'>
                        <small>Enviado: " . ($pedido['fecha_envio'] ?: 'Pendiente') . "</small>
                        " . ($pedido['fecha_preparacion'] ? "<small>Inicio prep: {$pedido['fecha_preparacion']}</small>" : "") . "
                    </div>
                </div>
            ";
        }
        $html .= "</div>";

        return $html;
    }

//cambia el estado del pedido
    public function cambiarEstadoPedido($id_pedido, $nuevo_estado) {
        $result = $this->pedidoDAO->actualizarEstadoPedido($id_pedido, $nuevo_estado);
        
        if ($result && $nuevo_estado == 'listo') {
            // Notificar al mesero que el pedido está listo
            $pedido = $this->pedidoDAO->obtenerPedidoPorId($id_pedido);
            $mensaje = "El pedido #{$id_pedido} de la mesa {$pedido['id_mesa']} está listo para servir.";
            $this->notificacionDAO->crearNotificacion($pedido['id_usuario'], $mensaje);
        }
        
        return $result;
    }

// /7cancela un pedido
    public function cancelarPedido($id_pedido) {
        return $this->pedidoDAO->actualizarEstadoPedido($id_pedido, 'cancelado');
    }
}

// Manejo de peticiones AJAX
if (isset($_GET['accion'])) {
    $controlador = new CocinaControlador();
    
    switch ($_GET['accion']) {
        case 'obtener_pedidos':
            echo $controlador->obtenerPedidosCocina();
            break;
            
        case 'cambiar_estado':
            $id_pedido = intval($_GET['id_pedido']);
            $estado = $_GET['estado'];
            echo json_encode(['success' => $controlador->cambiarEstadoPedido($id_pedido, $estado)]);
            break;
            
        case 'cancelar':
            $id_pedido = intval($_GET['id_pedido']);
            echo json_encode(['success' => $controlador->cancelarPedido($id_pedido)]);
            break;
    }
}
?>