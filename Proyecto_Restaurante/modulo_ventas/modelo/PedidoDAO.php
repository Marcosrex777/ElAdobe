<?php
require_once("Conexion.php");

class PedidoDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // Crear un nuevo pedido
    public function crearPedido($id_mesa, $id_usuario) {
        $sql = "INSERT INTO Pedidos (id_mesa, id_usuario, estado, total) VALUES (?, ?, 'En espera', 0)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id_mesa, $id_usuario);
        $stmt->execute();
        return $this->conexion->getConexion()->insert_id;
    }

    // Insertar detalle de pedido
    public function agregarDetalle($id_pedido, $id_menu, $cantidad, $precio) {
        $sql = "INSERT INTO Detalle_Pedido (id_pedido, id_menu, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("iiid", $id_pedido, $id_menu, $cantidad, $precio);
        return $stmt->execute();
    }

    // Actualizar total del pedido
    public function actualizarTotal($id_pedido) {
        $sql = "UPDATE Pedidos 
                SET total = (SELECT SUM(subtotal) FROM Detalle_Pedido WHERE id_pedido = ?) 
                WHERE id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id_pedido, $id_pedido);
        return $stmt->execute();
    }

    // Obtener resumen de pedido por mesa
    public function obtenerPedidoPorMesa($id_mesa) {
        $sql = "SELECT p.id_pedido, p.estado, p.total 
                FROM Pedidos p
                WHERE p.id_mesa = ? AND p.estado IN ('En espera','Preparando')
                ORDER BY p.fecha DESC LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_mesa);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    // Obtener detalle del pedido
    public function obtenerDetallePedido($id_pedido) {
        $sql = "SELECT m.nombre, d.cantidad, d.precio_unitario, d.subtotal 
                FROM Detalle_Pedido d
                INNER JOIN Menu m ON d.id_menu = m.id_menu
                WHERE d.id_pedido = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $detalles = [];

        while ($fila = $resultado->fetch_assoc()) {
            $detalles[] = $fila;
        }
        return $detalles;
    }
}
?>
