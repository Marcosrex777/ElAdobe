<?php
// InventarioControlador.php
require_once(__DIR__ . "/../Modelo/InventarioDAO.php");

class InventarioControlador {
    private $inventarioDAO;

    public function __construct() {
        $this->inventarioDAO = new InventarioDAO();
    }

    public function verificarInventario($id_menu, $cantidad) {
        return $this->inventarioDAO->verificarInventarioRapido($id_menu, $cantidad);
    }
}

// Manejo directo de peticiones
if (isset($_GET['accion']) && $_GET['accion'] === 'verificar_inventario') {
    header('Content-Type: application/json');
    
    try {
        $id_menu = intval($_GET['id_menu'] ?? 0);
        $cantidad = intval($_GET['cantidad'] ?? 1);
        
        if ($id_menu <= 0) {
            throw new Exception("ID de menú inválido");
        }
        
        $controlador = new InventarioControlador();
        $resultado = $controlador->verificarInventario($id_menu, $cantidad);
        
        echo json_encode($resultado);
        
    } catch (Exception $e) {
        echo json_encode([
            'suficiente' => false,
            'faltantes' => [],
            'error' => $e->getMessage()
        ]);
    }
    exit;
}
?>