<?php
require_once("Conexion.php");

class PedidoDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /**
     * Obtiene pedido activo por mesa (incluye todos los estados excepto finalizados)
     */
    public function obtenerPedidoPorMesa($id_mesa) {
        $sql = "SELECT * FROM Pedidos WHERE id_mesa = ? AND estado NOT IN ('finalizado', 'facturado', 'cancelado')";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_mesa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene pedido por ID sin restricción de estado
     */
    public function obtenerPedidoPorId($id_pedido) {
        $sql = "SELECT * FROM Pedidos WHERE id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Crea un nuevo pedido para una mesa
     */
    public function crearPedido($id_mesa, $id_usuario) {
        $sql = "INSERT INTO Pedidos (id_mesa, id_usuario, estado, total) VALUES (?, ?, 'pendiente', 0)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id_mesa, $id_usuario);
        
        if ($stmt->execute()) {
            return $this->conexion->getConexion()->insert_id;
        } else {
            error_log("Error al crear pedido: " . $stmt->error);
            return false;
        }
    }

    /**
     * Agrega un platillo al pedido
     */
    public function agregarDetalle($id_pedido, $id_menu, $cantidad, $precio) {
        // Verificar si ya existe el platillo en el pedido
        $sqlCheck = "SELECT * FROM Detalle_Pedido WHERE id_pedido = ? AND id_menu = ?";
        $stmtCheck = $this->conexion->getConexion()->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $id_pedido, $id_menu);
        $stmtCheck->execute();
        $existe = $stmtCheck->get_result()->fetch_assoc();

        if ($existe) {
            // Actualizar cantidad si ya existe
            $sqlUpdate = "UPDATE Detalle_Pedido SET cantidad = cantidad + ? WHERE id_detalle_pedido = ?";
            $stmtUpdate = $this->conexion->getConexion()->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ii", $cantidad, $existe['id_detalle_pedido']);
            return $stmtUpdate->execute();
        } else {
            // Insertar nuevo detalle
            $sql = "INSERT INTO Detalle_Pedido (id_pedido, id_menu, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->bind_param("iiid", $id_pedido, $id_menu, $cantidad, $precio);
            return $stmt->execute();
        }
    }

    /**
     * Elimina un platillo del pedido (solo si está en estado pendiente)
     */
    public function eliminarDetalle($id_detalle_pedido, $id_pedido) {
        // Verificar que el pedido esté en estado pendiente
        $pedido = $this->obtenerPedidoPorId($id_pedido);
        if ($pedido && $pedido['estado'] === 'pendiente') {
            $sql = "DELETE FROM Detalle_Pedido WHERE id_detalle_pedido = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->bind_param("i", $id_detalle_pedido);
            return $stmt->execute();
        }
        return false;
    }

    /**
     * Obtiene detalles del pedido (platillos)
     */
     public function obtenerDetalles($id_pedido) {
        $sql = "SELECT d.id_detalle_pedido, d.id_menu, m.nombre, d.cantidad, d.precio_unitario, d.subtotal
                FROM Detalle_Pedido d
                JOIN Menu m ON d.id_menu = m.id_menu
                WHERE d.id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        $detalles = $result->fetch_all(MYSQLI_ASSOC);
        
        return $detalles ? $detalles : [];
    }

    /**
     * Verifica si un pedido tiene platillos
     */
    public function pedidoTienePlatillos($id_pedido) {
        $sql = "SELECT COUNT(*) as total FROM Detalle_Pedido WHERE id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] > 0;
    }

    /**
     * Actualiza total del pedido
     */
    public function actualizarTotal($id_pedido) {
        $sql = "UPDATE Pedidos 
                SET total = (SELECT SUM(subtotal) FROM Detalle_Pedido WHERE id_pedido = ?) 
                WHERE id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id_pedido, $id_pedido);
        return $stmt->execute();
    }

    /**
     * Envía pedido a cocina (solo si tiene platillos)
     */
    public function enviarPedido($id_pedido, $id_mesa) {
        // Verificar que el pedido tenga platillos
        if (!$this->pedidoTienePlatillos($id_pedido)) {
            return false;
        }

        $conn = $this->conexion->getConexion();
        $conn->begin_transaction();
        try {
            // Actualizar pedido a 'enviado'
            $sql1 = "UPDATE Pedidos SET estado = 'enviado', fecha_envio = NOW() WHERE id_pedido = ?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("i", $id_pedido);
            $stmt1->execute();

            // Actualizar mesa a 'ocupada'
            $sql2 = "UPDATE Mesas SET estado = 'ocupada' WHERE id_mesa = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $id_mesa);
            $stmt2->execute();

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en enviarPedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de un pedido
     */
    public function actualizarEstadoPedido($id_pedido, $estado) {
        $campo_fecha = '';
        switch ($estado) {
            case 'preparando': $campo_fecha = 'fecha_preparacion'; break;
            case 'listo': $campo_fecha = 'fecha_listo'; break;
            case 'entregado': $campo_fecha = 'fecha_entregado'; break;
            case 'finalizado': $campo_fecha = 'fecha_finalizado'; break;
            case 'cancelado': $campo_fecha = 'fecha_cancelado'; break;
        }
        
        $sql = "UPDATE Pedidos SET estado = ?";
        if ($campo_fecha) {
            $sql .= ", $campo_fecha = NOW()";
        }
        $sql .= " WHERE id_pedido = ?";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("si", $estado, $id_pedido);
        return $stmt->execute();
    }

    /**
     * Actualiza el estado de una mesa
     */
    public function actualizarEstadoMesa($id_mesa, $estado) {
        $sql = "UPDATE Mesas SET estado = ? WHERE id_mesa = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("si", $estado, $id_mesa);
        return $stmt->execute();
    }

    /**
     * Obtiene pedidos por estado (para cocina)
     */
    public function obtenerPedidosPorEstado($estados) {
        if (is_array($estados)) {
            $placeholders = str_repeat('?,', count($estados) - 1) . '?';
            $sql = "SELECT * FROM Pedidos WHERE estado IN ($placeholders) ORDER BY fecha_envio ASC";
        } else {
            $sql = "SELECT * FROM Pedidos WHERE estado = ? ORDER BY fecha_envio ASC";
            $estados = [$estados];
        }
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        
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

    /**
     * Marca pedido como listo (desde cocina)
     */
    public function marcarPedidoListo($id_pedido) {
        return $this->actualizarEstadoPedido($id_pedido, 'listo');
    }

    /**
     * Marca pedido como entregado (desde mesero)
     */
    public function marcarPedidoEntregado($id_pedido) {
        return $this->actualizarEstadoPedido($id_pedido, 'entregado');
    }
}
?>