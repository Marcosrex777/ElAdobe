<?php
require_once(__DIR__ . "/../../conectar_bd.php");

class FacturaDAO {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
            public function getConexion() {
        return $this->conn;
    }

    public function obtenerDatosFactura($numero_factura) {
        $sql = "SELECT f.*, p.id_pedido, p.id_mesa, m.numero as numero_mesa, 
                       u.nombre_completo as mesero, v.fecha as fecha_venta
                FROM facturas f
                JOIN pedidos p ON f.id_pedido = p.id_pedido
                JOIN mesas m ON p.id_mesa = m.id_mesa
                JOIN ventas v ON f.id_venta = v.id_venta
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE f.numero_factura = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $numero_factura);
        $stmt->execute();
        $result = $stmt->get_result();
        $factura = $result->fetch_assoc();
        
        if (!$factura) {
            $sql = "SELECT f.*, p.id_pedido, p.id_mesa, m.numero as numero_mesa, 
                           u.nombre_completo as mesero, v.fecha as fecha_venta
                    FROM facturas f
                    JOIN pedidos p ON f.id_pedido = p.id_pedido
                    JOIN mesas m ON p.id_mesa = m.id_mesa
                    JOIN ventas v ON f.id_venta = v.id_venta
                    JOIN usuarios u ON p.id_usuario = u.id_usuario
                    WHERE f.id_factura = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $numero_factura);
            $stmt->execute();
            $result = $stmt->get_result();
            $factura = $result->fetch_assoc();
        }
        
        return $factura;
    }

    public function obtenerDetallesFactura($id_factura) {
        $sql = "SELECT dv.cantidad, dv.precio_unitario, m.nombre, 
                       (dv.cantidad * dv.precio_unitario) as subtotal
                FROM detalle_venta dv
                JOIN menu m ON dv.id_menu = m.id_menu
                WHERE dv.id_venta = (SELECT id_venta FROM facturas WHERE id_factura = ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_factura);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>