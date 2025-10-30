<?php
// controlador/PedidoControlador.php - SOLO LÓGICA
require_once("../Modelo/PedidoDAO.php");

class PedidoControlador {
    private $pedidoDAO;

    public function __construct() {
        $this->pedidoDAO = new PedidoDAO();
    }

    public function mostrarPedidoMesa($id_mesa, $id_usuario) {
        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        
        if (!$pedido) {
            $id_pedido = $this->pedidoDAO->crearPedido($id_mesa, $id_usuario);
            if (!$id_pedido) {
                return "<div class='error'>Error al crear el pedido para la mesa.</div>";
            }
            $pedido = [
                'id_pedido' => $id_pedido, 
                'estado' => 'pendiente', 
                'total' => 0
            ];
        }

        $detalles = $this->pedidoDAO->obtenerDetalles($pedido['id_pedido']);
        
        $html = "<div class='pedido-actual'>";
        $html .= "<h3>📋 Pedido - Mesa #{$id_mesa}</h3>";
        $html .= "<div class='estado-pedido'><strong>Estado:</strong> <span class='estado-{$pedido['estado']}'>{$pedido['estado']}</span></div>";
        
        if (empty($detalles)) {
            $html .= "<p class='pedido-vacio'>No hay platillos agregados al pedido.</p>";
        } else {
            $html .= "<table class='tabla-pedido'>";
            $html .= "<thead><tr><th>Platillo</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th>";
            
            if ($pedido['estado'] === 'pendiente') {
                $html .= "<th>Acciones</th>";
            }
            
            $html .= "</tr></thead><tbody>";
            
            $total = 0;
            foreach ($detalles as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                $total += $subtotal;
                
                $html .= "<tr>";
                $html .= "<td>{$detalle['nombre']}</td>";
                $html .= "<td>{$detalle['cantidad']}</td>";
                $html .= "<td>Q{$detalle['precio_unitario']}</td>";
                $html .= "<td>Q{$subtotal}</td>";
                
                if ($pedido['estado'] === 'pendiente') {
                    $html .= "<td>
                        <button class='btn-eliminar' 
                                onclick='eliminarPlatillo({$detalle['id_detalle_pedido']}, {$pedido['id_pedido']})'
                                title='Eliminar platillo'>
                            🗑️ Eliminar
                        </button>
                    </td>";
                }
                
                $html .= "</tr>";
            }
            
            $html .= "</tbody>";
            $html .= "<tfoot><tr class='total-row'><td colspan='3'><strong>Total:</strong></td><td><strong>Q{$total}</strong></td>";
            
            if ($pedido['estado'] === 'pendiente') {
                $html .= "<td></td>";
            }
            
            $html .= "</tr></tfoot>";
            $html .= "</table>";
        }

        $html .= "</div>";

        // Botones de acción según el estado del pedido
        $html .= $this->generarBotonesAccion($pedido, $id_mesa, $detalles);
        
        return $html;
    }

    private function generarBotonesAccion($pedido, $id_mesa, $detalles) {
        $html = "<div class='acciones-pedido'>";
        
        switch ($pedido['estado']) {
            case 'pendiente':
                $html .= "
                    <form method='GET' action='../controlador/enrutador_controlador.php' class='form-accion'>
                        <input type='hidden' name='mesa' value='{$id_mesa}'>
                        <input type='hidden' name='accion' value='enviar'>
                        <button type='submit' class='btn-enviar' id='btn-enviar-pedido'>
                            🚀 Enviar a Cocina
                        </button>
                    </form>
                ";
                break;
                
            case 'listo':
                $html .= "
                    <form method='GET' action='../controlador/enrutador_controlador.php' class='form-accion'>
                        <input type='hidden' name='mesa' value='{$id_mesa}'>
                        <input type='hidden' name='accion' value='marcar_entregado'>
                        <button type='submit' class='btn-entregado'>
                            ✅ Marcar como Entregado
                        </button>
                    </form>
                ";
                break;
                
            case 'entregado':
                $html .= "
                    <form method='GET' action='../controlador/enrutador_controlador.php' class='form-accion'>
                        <input type='hidden' name='mesa' value='{$id_mesa}'>
                        <input type='hidden' name='accion' value='finalizar'>
                        <button type='submit' class='btn-finalizar'>
                            🧾 Finalizar Pedido
                        </button>
                    </form>
                ";
                break;
                
            case 'enviado':
            case 'preparando':
                $html .= "<p class='info-estado'>El pedido está siendo procesado en cocina...</p>";
                break;
        }
        
        $html .= "</div>";
        return $html;
    }

    public function agregarPlatillo($id_mesa, $id_usuario, $id_menu, $cantidad, $precio) {
        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        if (!$pedido) {
            $id_pedido = $this->pedidoDAO->crearPedido($id_mesa, $id_usuario);
            if (!$id_pedido) {
                return false;
            }
        } else {
            $id_pedido = $pedido['id_pedido'];
        }

        $result = $this->pedidoDAO->agregarDetalle($id_pedido, $id_menu, $cantidad, $precio);
        if ($result) {
            $this->pedidoDAO->actualizarTotal($id_pedido);
        }
        return $result;
    }

    public function eliminarPlatillo($id_detalle_pedido, $id_pedido) {
        $result = $this->pedidoDAO->eliminarDetalle($id_detalle_pedido, $id_pedido);
        if ($result) {
            $this->pedidoDAO->actualizarTotal($id_pedido);
        }
        return $result;
    }

    public function enviarPedido($id_mesa) {
        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        if ($pedido) {
            if (!$this->pedidoDAO->pedidoTienePlatillos($pedido['id_pedido'])) {
                return false;
            }
            return $this->pedidoDAO->enviarPedido($pedido['id_pedido'], $id_mesa);
        }
        return false;
    }

    public function marcarEntregado($id_mesa) {
        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        if ($pedido) {
            return $this->pedidoDAO->marcarPedidoEntregado($pedido['id_pedido']);
        }
        return false;
    }

        public function cerrarCuenta($id_mesa, $id_usuario) {
        require_once("../Modelo/Conexion.php");
        $conexion = new Conexion();
        $db = $conexion->getConexion();

        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        if (!$pedido) {
            $_SESSION['mensaje'] = "❌ No hay pedido activo en esta mesa.";
            header("Location: ../vista/index.php");
            exit;
        }

        $id_pedido = $pedido['id_pedido'];

        // Crear venta
        $sqlVenta = "INSERT INTO Ventas (id_mesa, id_usuario, total, metodo_pago, estado)
                     VALUES (?, ?, ?, 'efectivo', 'pagada')";
        $stmtVenta = $db->prepare($sqlVenta);
        $stmtVenta->bind_param("iid", $id_mesa, $id_usuario, $pedido['total']);
        
        if (!$stmtVenta->execute()) {
            $_SESSION['mensaje'] = "❌ Error al crear la venta: " . $stmtVenta->error;
            header("Location: ../vista/index.php");
            exit;
        }
        
        $id_venta = $stmtVenta->insert_id;

        // Factura
        $numeroFactura = 'FAC-' . date('Ymd-His');
        $sqlFactura = "INSERT INTO Facturas (id_venta, id_pedido, numero_factura, subtotal, total, metodo_pago)
                       VALUES (?, ?, ?, ?, ?, 'efectivo')";
        $stmtFactura = $db->prepare($sqlFactura);
        $stmtFactura->bind_param("iisdd", $id_venta, $id_pedido, $numeroFactura, $pedido['total'], $pedido['total']);
        
        if (!$stmtFactura->execute()) {
            $_SESSION['mensaje'] = "❌ Error al crear la factura: " . $stmtFactura->error;
            header("Location: ../vista/index.php");
            exit;
        }

        // Copiar detalles del pedido a detalle_venta - CORREGIDO
        $detalles = $this->pedidoDAO->obtenerDetalles($id_pedido);
        
        if (empty($detalles)) {
            $_SESSION['mensaje'] = "❌ El pedido no tiene detalles para facturar.";
            header("Location: ../vista/index.php");
            exit;
        }

        foreach ($detalles as $detalle) {
            // Verificar que todos los campos necesarios estén presentes
            if (!isset($detalle['id_menu']) || !isset($detalle['cantidad']) || !isset($detalle['precio_unitario'])) {
                error_log("Detalle incompleto: " . print_r($detalle, true));
                continue; // Saltar este detalle si está incompleto
            }

            $sqlDV = "INSERT INTO Detalle_Venta (id_venta, id_menu, cantidad, precio_unitario) 
                      VALUES (?, ?, ?, ?)";
            $stmtDV = $db->prepare($sqlDV);
            $stmtDV->bind_param("iiid", $id_venta, $detalle['id_menu'], $detalle['cantidad'], $detalle['precio_unitario']);
            
            if (!$stmtDV->execute()) {
                error_log("Error al insertar detalle_venta: " . $stmtDV->error);
                // Continuar con los siguientes detalles aunque falle uno
            }
        }

        // Cambiar estado del pedido a 'finalizado'
        $this->pedidoDAO->actualizarEstadoPedido($id_pedido, 'finalizado');

        // Liberar mesa
        $this->pedidoDAO->actualizarEstadoMesa($id_mesa, 'libre');

        $_SESSION['mensaje'] = "✅ Cuenta cerrada correctamente. Factura: {$numeroFactura}";
        header("Location: ../vista/index.php");
        exit;
    }
}
?>