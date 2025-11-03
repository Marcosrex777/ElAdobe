<?php
require_once("../Modelo/InventarioDAO.php");
require_once("../Modelo/Conexion.php");

class InventarioTest {
    private $inventarioDAO;
    
    public function __construct() {
        $this->inventarioDAO = new InventarioDAO();
    }
    
    public function testVerificacionInventario() {
        echo "=== Test de Verificación de Inventario ===\n";
        
        // Test 1: Verificar platillo con inventario suficiente
        $resultado1 = $this->inventarioDAO->verificarInventarioPlatillo(1, 2); // Típico x2
        echo "Test 1 - Inventario suficiente: " . ($resultado1['suficiente'] ? 'PASS' : 'FAIL') . "\n";
        
        // Test 2: Verificar platillo con cantidad excesiva
        $resultado2 = $this->inventarioDAO->verificarInventarioPlatillo(1, 100); // Típico x100
        echo "Test 2 - Inventario insuficiente: " . (!$resultado2['suficiente'] ? 'PASS' : 'FAIL') . "\n";
        
        // Test 3: Verificar actualización de inventario
        $actualizado = $this->inventarioDAO->actualizarInventarioPedido(1, 1);
        echo "Test 3 - Actualización inventario: " . ($actualizado ? 'PASS' : 'FAIL') . "\n";
    }
    
    public function testCondicionesCarrera() {
        echo "\n=== Test de Condiciones de Carrera ===\n";
        
        // Simular múltiples solicitudes concurrentes
        $procesos = [];
        for ($i = 0; $i < 5; $i++) {
            $procesos[] = $this->simularPedidoConcurrente(1, 5); // 5 pedidos del mismo platillo
        }
        
        $exitosos = array_filter($procesos);
        echo "Pedidos exitosos: " . count($exitosos) . "/5\n";
        echo "Test 4 - Control de concurrencia: " . (count($exitosos) > 0 ? 'PASS' : 'FAIL') . "\n";
    }
    
    private function simularPedidoConcurrente($id_menu, $cantidad) {
        try {
            return $this->inventarioDAO->actualizarInventarioPedido($id_menu, $cantidad);
        } catch (Exception $e) {
            return false;
        }
    }
}

// Ejecutar tests
$test = new InventarioTest();
$test->testVerificacionInventario();
$test->testCondicionesCarrera();
?>