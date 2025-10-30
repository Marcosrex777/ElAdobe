<?php
require_once("Conexion.php");

class CuentaDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /**
     * Obtiene las cuentas separadas de un pedido
     */
    public function obtenerCuentasPorPedido($id_pedido) {
        $sql = "SELECT * FROM Cuentas_Separadas WHERE id_pedido = ? ORDER BY numero_cuenta";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los detalles de una cuenta específica
     */
    public function obtenerDetallesCuenta($id_cuenta) {
        $sql = "SELECT dc.*, dp.id_menu, m.nombre, dp.precio_unitario
                FROM Detalle_Cuenta dc
                JOIN Detalle_Pedido dp ON dc.id_detalle_pedido = dp.id_detalle_pedido
                JOIN Menu m ON dp.id_menu = m.id_menu
                WHERE dc.id_cuenta = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_cuenta);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene el total de propinas de un día
     */
    public function obtenerPropinaDelDia($fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        $sql = "SELECT SUM(propina) as total_propina 
                FROM Facturas 
                WHERE DATE(fecha_emision) = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_propina'] ?: 0;
    }
}
?>