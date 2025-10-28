<?php
require_once("Conexion.php");

class PedidoDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // Ver si una mesa tiene un pedido activo
    public function obtenerPedidoPorMesa($id_mesa) {
        $sql = "SELECT * FROM Pedidos WHERE id_mesa = ? AND estado IN ('En espera','Preparando')";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_mesa);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Crear un nuevo pedido para una mesa
    public function crearPedido($id_mesa, $id_usuario) {
        $sql = "INSERT INTO Pedidos (id_mesa, id_usuario, estado, total) VALUES (?, ?, 'En espera', 0)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id_mesa, $id_usuario);
        $stmt->execute();
        return $this->conexion->getConexion()->insert_id;
    }

    // Agregar un platillo al pedido
    public function agregarDetalle($id_pedido, $id_menu, $cantidad, $precio) {
        $sql = "INSERT INTO Detalle_Pedido (id_pedido, id_menu, cantidad, precio_unitario)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("iiid", $id_pedido, $id_menu, $cantidad, $precio);
        return $stmt->execute();
    }

    // Obtener detalles del pedido (platillos)
    public function obtenerDetalles($id_pedido) {
        $sql = "SELECT d.id_detalle_pedido, m.nombre, d.cantidad, d.precio_unitario, d.subtotal
                FROM Detalle_Pedido d
                JOIN Menu m ON d.id_menu = m.id_menu
                WHERE d.id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Actualizar total del pedido
    public function actualizarTotal($id_pedido) {
        $sql = "UPDATE Pedidos 
                SET total = (SELECT SUM(subtotal) FROM Detalle_Pedido WHERE id_pedido = ?) 
                WHERE id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id_pedido, $id_pedido);
        $stmt->execute();
    }

    // Cambiar estado del pedido y de la mesa
    public function enviarPedido($id_pedido, $id_mesa) {
        $sql1 = "UPDATE Pedidos SET estado = 'Preparando' WHERE id_pedido = ?";
        $sql2 = "UPDATE Mesas SET estado = 'ocupada' WHERE id_mesa = ?";

        $conn = $this->conexion->getConexion();
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("i", $id_pedido);
            $stmt1->execute();

            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $id_mesa);
            $stmt2->execute();

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }
}
?>
