<?php
// FacturaDAO.php
require_once("Conexion.php");

class FacturaDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }
    public function getConexion() {
    return $this->conexion->getConexion();
}

    /**
     * Obtiene los datos principales de la factura
     */
    public function obtenerDatosFactura($numero_factura) {
        $sql = "SELECT f.*, p.id_pedido, p.id_mesa, m.numero as numero_mesa, 
                       u.nombre_completo as mesero, v.fecha as fecha_venta
                FROM Facturas f
                JOIN Pedidos p ON f.id_pedido = p.id_pedido
                JOIN Mesas m ON p.id_mesa = m.id_mesa
                JOIN Ventas v ON f.id_venta = v.id_venta
                JOIN Usuarios u ON p.id_usuario = u.id_usuario
                WHERE f.numero_factura = ?";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("s", $numero_factura);
        $stmt->execute();
        $result = $stmt->get_result();
        $factura = $result->fetch_assoc();
        
        // Si no encuentra por número, buscar por ID
        if (!$factura) {
            $sql = "SELECT f.*, p.id_pedido, p.id_mesa, m.numero as numero_mesa, 
                           u.nombre_completo as mesero, v.fecha as fecha_venta
                    FROM Facturas f
                    JOIN Pedidos p ON f.id_pedido = p.id_pedido
                    JOIN Mesas m ON p.id_mesa = m.id_mesa
                    JOIN Ventas v ON f.id_venta = v.id_venta
                    JOIN Usuarios u ON p.id_usuario = u.id_usuario
                    WHERE f.id_factura = ?";
            
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->bind_param("i", $numero_factura);
            $stmt->execute();
            $result = $stmt->get_result();
            $factura = $result->fetch_assoc();
        }
        
        return $factura;
    }

    /**
     * Obtiene los detalles de la factura
     */
    public function obtenerDetallesFactura($id_factura) {
        $sql = "SELECT dv.cantidad, dv.precio_unitario, m.nombre, 
                       (dv.cantidad * dv.precio_unitario) as subtotal
                FROM Detalle_Venta dv
                JOIN Menu m ON dv.id_menu = m.id_menu
                WHERE dv.id_venta = (SELECT id_venta FROM Facturas WHERE id_factura = ?)";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_factura);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>