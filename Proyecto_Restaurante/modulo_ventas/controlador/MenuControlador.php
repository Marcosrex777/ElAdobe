<?php
// controlador/MenuControlador.php
require_once("../Modelo/MenuDAO.php");

class MenuControlador {
    private $menuDAO;

    public function __construct() {
        $this->menuDAO = new MenuDAO();
    }

    /**
     * Obtiene el menú completo organizado por categorías para la vista interactiva
     */
    public function obtenerMenuInteractivo() {
        $menu = $this->menuDAO->listarMenu();
        
        if (empty($menu)) {
            return "<div class='mensaje-sistema'>No hay platillos registrados en el menú.</div>";
        }

        // Agrupar por categoría
        $menuPorCategoria = [];
        foreach ($menu as $plato) {
            $categoria = $plato['categoria'];
            if (!isset($menuPorCategoria[$categoria])) {
                $menuPorCategoria[$categoria] = [];
            }
            $menuPorCategoria[$categoria][] = $plato;
        }

        // Ordenar categorías según el orden deseado
        $ordenCategorias = ['Desayunos', 'Entradas', 'Almuerzos y Cenas', 'Postres', 'Bebidas'];
        
        $html = '';
        
        foreach ($ordenCategorias as $categoria) {
            if (isset($menuPorCategoria[$categoria])) {
                $html .= $this->generarCategoriaHTML($categoria, $menuPorCategoria[$categoria]);
            }
        }

        return $html;
    }

    /**
     * Genera el HTML para una categoría específica
     */
    private function generarCategoriaHTML($categoria, $platos) {
        $icono = $this->obtenerIconoCategoria($categoria);
        
        $html = "
        <div class='accordion-categoria' data-categoria='".strtolower(str_replace(' ', '-', $categoria))."'>
            <button class='accordion-header'>
                <span>{$icono} {$categoria} <small>(" . count($platos) . " items)</small></span>
                <span class='accordion-icon'>▼</span>
            </button>
            <div class='accordion-content'>
                <div class='platos-grid'>";
        
        foreach ($platos as $plato) {
            $html .= $this->generarPlatoHTML($plato);
        }
        
        $html .= "
                </div>
            </div>
        </div>";
        
        return $html;
    }

    /**
     * Genera el HTML para un plato individual
     */
    private function generarPlatoHTML($plato) {
        $descripcion = !empty($plato['descripcion']) ? $plato['descripcion'] : 'Delicioso platillo de nuestra cocina tradicional.';
        
        return "
        <div class='plato-card' data-id='{$plato['id_menu']}'>
            <div class='plato-header'>
                <h4 class='plato-nombre'>{$plato['nombre']}</h4>
                <div class='plato-precio'>Q{$plato['precio']}</div>
            </div>
            <p class='plato-descripcion'>{$descripcion}</p>
            <div class='plato-acciones'>
                <div class='cantidad-control'>
                    <button class='btn-cantidad' onclick='disminuirCantidad(this)'>-</button>
                    <span class='cantidad-display'>1</span>
                    <button class='btn-cantidad' onclick='aumentarCantidad(this)'>+</button>
                </div>
                <button class='agregar-btn' 
                        data-id='{$plato['id_menu']}' 
                        data-precio='{$plato['precio']}'
                        data-nombre='{$plato['nombre']}'
                        onclick='agregarAlPedido(this)'>
                    ➕ Agregar
                </button>
            </div>
        </div>";
    }

    /**
     * Obtiene el ícono correspondiente para cada categoría
     */
    private function obtenerIconoCategoria($categoria) {
        $iconos = [
            'Desayunos' => '🍳',
            'Entradas' => '🥗',
            'Almuerzos y Cenas' => '🍽️',
            'Postres' => '🍰',
            'Bebidas' => '🥤'
        ];
        
        return $iconos[$categoria] ?? '📋';
    }

    /**
     * Método original para compatibilidad (puede ser eliminado gradualmente)
     */
    public function obtenerMenu() {
        return $this->obtenerMenuInteractivo();
    }
}
?>