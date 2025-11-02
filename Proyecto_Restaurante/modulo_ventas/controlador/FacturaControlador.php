<?php
// FacturaControlador.php
require_once("../Modelo/FacturaDAO.php");

class FacturaControlador {
    private $facturaDAO;

    public function __construct() {
        $this->facturaDAO = new FacturaDAO();
    }

    /**
     * Obtiene los datos de la factura para generar el PDF
     */
    public function obtenerDatosFactura($numero_factura) {
        return $this->facturaDAO->obtenerDatosFactura($numero_factura);
    }

    /**
     * Obtiene los detalles de la factura
     */
    public function obtenerDetallesFactura($id_factura) {
        return $this->facturaDAO->obtenerDetallesFactura($id_factura);
    }
}

// Manejo de peticiones AJAX
if (isset($_GET['accion'])) {
    $controlador = new FacturaControlador();
    
    switch ($_GET['accion']) {
        case 'obtener_datos_factura':
            $numero_factura = $_GET['numero_factura'] ?? '';
            if ($numero_factura) {
                $datosFactura = $controlador->obtenerDatosFactura($numero_factura);
                header('Content-Type: application/json');
                echo json_encode($datosFactura);
            }
            break;
            
        case 'obtener_detalles_factura':
            $id_factura = intval($_GET['id_factura'] ?? 0);
            if ($id_factura) {
                $detalles = $controlador->obtenerDetallesFactura($id_factura);
                header('Content-Type: application/json');
                echo json_encode($detalles);
            }
            break;
    }
}
?>