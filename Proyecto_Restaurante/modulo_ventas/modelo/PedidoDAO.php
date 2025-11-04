<?php
require_once(__DIR__ . "/../../conectar_bd.php");

class PedidoDAO {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
        public function getConexion() {
        return $this->conn;
    }

    public function obtenerPedidoPorMesa($id_mesa) {
        $sql = "SELECT * FROM pedidos WHERE id_mesa = ? AND estado NOT IN ('finalizado', 'facturado', 'cancelado')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_mesa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerPedidoPorId($id_pedido) {
        $sql = "SELECT * FROM pedidos WHERE id_pedido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crearPedido($id_mesa, $id_usuario) {
        $sql = "INSERT INTO pedidos (id_mesa, id_usuario, estado, total) VALUES (?, ?, 'pendiente', 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_mesa, $id_usuario);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            error_log("Error al crear pedido: " . $stmt->error);
            return false;
        }
    }

    public function agregarDetalle($id_pedido, $id_menu, $cantidad, $precio) {
        $sqlCheck = "SELECT * FROM detalle_pedido WHERE id_pedido = ? AND id_menu = ?";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $id_pedido, $id_menu);
        $stmtCheck->execute();
        $existe = $stmtCheck->get_result()->fetch_assoc();

        if ($existe) {
            $sqlUpdate = "UPDATE detalle_pedido SET cantidad = cantidad + ? WHERE id_detalle_pedido = ?";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ii", $cantidad, $existe['id_detalle_pedido']);
            return $stmtUpdate->execute();
        } else {
            $sql = "INSERT INTO detalle_pedido (id_pedido, id_menu, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiid", $id_pedido, $id_menu, $cantidad, $precio);
            return $stmt->execute();
        }
    }

    public function eliminarDetalle($id_detalle_pedido, $id_pedido) {
        $pedido = $this->obtenerPedidoPorId($id_pedido);
        if ($pedido && $pedido['estado'] === 'pendiente') {
            $sql = "DELETE FROM detalle_pedido WHERE id_detalle_pedido = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_detalle_pedido);
            return $stmt->execute();
        }
        return false;
    }

    public function obtenerDetalles($id_pedido) {
        $sql = "SELECT d.id_detalle_pedido, d.id_menu, m.nombre, d.cantidad, d.precio_unitario, (d.cantidad * d.precio_unitario) as subtotal
                FROM detalle_pedido d
                JOIN menu m ON d.id_menu = m.id_menu
                WHERE d.id_pedido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        $detalles = $result->fetch_all(MYSQLI_ASSOC);
        
        return $detalles ? $detalles : [];
    }

    public function pedidoTienePlatillos($id_pedido) {
        $sql = "SELECT COUNT(*) as total FROM detalle_pedido WHERE id_pedido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] > 0;
    }

    public function actualizarTotal($id_pedido) {
        $sql = "UPDATE pedidos 
                SET total = (SELECT SUM(cantidad * precio_unitario) FROM detalle_pedido WHERE id_pedido = ?) 
                WHERE id_pedido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_pedido, $id_pedido);
        return $stmt->execute();
    }

    public function enviarPedido($id_pedido, $id_mesa) {
        if (!$this->pedidoTienePlatillos($id_pedido)) {
            return false;
        }

        $this->conn->begin_transaction();
        try {
            $sql1 = "UPDATE pedidos SET estado = 'enviado', fecha_envio = NOW() WHERE id_pedido = ?";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bind_param("i", $id_pedido);
            $stmt1->execute();

            $sql2 = "UPDATE mesas SET estado = 'ocupada' WHERE id_mesa = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bind_param("i", $id_mesa);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en enviarPedido: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstadoPedido($id_pedido, $estado) {
        $campo_fecha = '';
        switch ($estado) {
            case 'preparando': $campo_fecha = 'fecha_preparacion'; break;
            case 'listo': $campo_fecha = 'fecha_listo'; break;
            case 'entregado': $campo_fecha = 'fecha_entregado'; break;
            case 'finalizado': $campo_fecha = 'fecha_finalizado'; break;
            case 'cancelado': $campo_fecha = 'fecha_cancelado'; break;
        }
        
        $sql = "UPDATE pedidos SET estado = ?";
        if ($campo_fecha) {
            $sql .= ", $campo_fecha = NOW()";
        }
        $sql .= " WHERE id_pedido = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $estado, $id_pedido);
        return $stmt->execute();
    }

    public function actualizarEstadoMesa($id_mesa, $estado) {
        $sql = "UPDATE mesas SET estado = ? WHERE id_mesa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $estado, $id_mesa);
        return $stmt->execute();
    }

    public function obtenerPedidosPorEstado($estados) {
        if (is_array($estados)) {
            $placeholders = str_repeat('?,', count($estados) - 1) . '?';
            $sql = "SELECT * FROM pedidos WHERE estado IN ($placeholders) ORDER BY fecha_envio ASC";
        } else {
            $sql = "SELECT * FROM pedidos WHERE estado = ? ORDER BY fecha_envio ASC";
            $estados = [$estados];
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if (is_array($estados)) {
            $types = str_repeat('s', count($estados));
            $stmt->bind_param($types, ...$estados);
        } else {
            $stmt->bind_param("s", $estados);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function marcarPedidoListo($id_pedido) {
        return $this->actualizarEstadoPedido($id_pedido, 'listo');
    }

    public function marcarPedidoEntregado($id_pedido) {
        return $this->actualizarEstadoPedido($id_pedido, 'entregado');
    }
}
?>