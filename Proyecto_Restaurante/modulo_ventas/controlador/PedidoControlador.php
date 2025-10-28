<?php
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
            $pedido = ['id_pedido' => $id_pedido, 'estado' => 'En espera'];
        }

        $detalles = $this->pedidoDAO->obtenerDetalles($pedido['id_pedido']);
        $html = "<h3>Pedido de la Mesa #{$id_mesa}</h3>";
        if (empty($detalles)) {
            $html .= "<p>No hay platillos agregados.</p>";
        } else {
            $html .= "<table border='1' cellpadding='5'>
                        <tr><th>Platillo</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>";
            foreach ($detalles as $d) {
                $html .= "<tr>
                            <td>{$d['nombre']}</td>
                            <td>{$d['cantidad']}</td>
                            <td>Q{$d['precio_unitario']}</td>
                            <td>Q{$d['subtotal']}</td>
                          </tr>";
            }
            $html .= "</table>";
        }

        return $html;
    }

    public function agregarPlatillo($id_mesa, $id_usuario, $id_menu, $cantidad, $precio) {
        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        if (!$pedido) {
            $id_pedido = $this->pedidoDAO->crearPedido($id_mesa, $id_usuario);
        } else {
            $id_pedido = $pedido['id_pedido'];
        }

        $this->pedidoDAO->agregarDetalle($id_pedido, $id_menu, $cantidad, $precio);
        $this->pedidoDAO->actualizarTotal($id_pedido);
    }

    public function enviarPedido($id_mesa) {
        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        if ($pedido) {
            $this->pedidoDAO->enviarPedido($pedido['id_pedido'], $id_mesa);
        }
    }
  public function cerrarCuenta($id_mesa, $id_usuario)
{
    require_once("../Modelo/Conexion.php");
    $conexion = new Conexion();
    $db = $conexion->getConexion();

    // Buscar pedido activo
    $sqlPedido = "SELECT * FROM Pedidos WHERE id_mesa = ? AND estado IN ('En espera','Preparando','Servido') LIMIT 1";
    $stmt = $db->prepare($sqlPedido);
    $stmt->bind_param("i", $id_mesa);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();

    if (!$pedido) {
        echo "<script>alert('No hay pedido activo en esta mesa.');</script>";
        return;
    }

    $id_pedido = $pedido['id_pedido'];

    // Crear venta
    $sqlVenta = "INSERT INTO Ventas (id_mesa, id_usuario, total, metodo_pago, estado)
                 VALUES (?, ?, ?, 'efectivo', 'pagada')";
    $stmtVenta = $db->prepare($sqlVenta);
    $stmtVenta->bind_param("iid", $id_mesa, $id_usuario, $pedido['total']);
    $stmtVenta->execute();
    $id_venta = $stmtVenta->insert_id;

    // 🔸🔸🔸 AQUI VA EL CÓDIGO DE LA FACTURA 🔸🔸🔸
    $sqlFactura = "INSERT INTO Facturas (id_venta, id_pedido, numero_factura, subtotal, total, metodo_pago)
                   VALUES (?, ?, CONCAT('FAC-', ?), ?, ?, 'efectivo')";
    $stmtFactura = $db->prepare($sqlFactura);
    $stmtFactura->bind_param("iisdd", $id_venta, $id_pedido, $id_venta, $pedido['total'], $pedido['total']);
    $stmtFactura->execute();
    // 🔸🔸🔸 FIN DE LA FACTURA 🔸🔸🔸

    // Pasar los detalles del pedido a detalle_venta
    $sqlDetalles = "SELECT id_menu, cantidad, precio_unitario FROM Detalle_Pedido WHERE id_pedido = ?";
    $stmtDetalles = $db->prepare($sqlDetalles);
    $stmtDetalles->bind_param("i", $id_pedido);
    $stmtDetalles->execute();
    $detalles = $stmtDetalles->get_result();

    while ($d = $detalles->fetch_assoc()) {
        $sqlDV = "INSERT INTO Detalle_Venta (id_venta, id_menu, cantidad, precio_unitario) 
                  VALUES (?, ?, ?, ?)";
        $stmtDV = $db->prepare($sqlDV);
        $stmtDV->bind_param("iiid", $id_venta, $d['id_menu'], $d['cantidad'], $d['precio_unitario']);
        $stmtDV->execute();
    }

    // Cambiar estado del pedido
    $db->query("UPDATE Pedidos SET estado = 'Finalizado' WHERE id_pedido = $id_pedido");

    // Liberar mesa
    $db->query("UPDATE Mesas SET estado = 'libre' WHERE id_mesa = $id_mesa");

    echo "<script>alert('✅ Cuenta cerrada, factura generada y mesa liberada.'); window.location='index.php';</script>";
}


}
?>
