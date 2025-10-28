<?php
// MenuControlador.php
// Controlador encargado de manejar la lógica del menú de platos

require_once("../Modelo/MenuDAO.php");

class MenuControlador
{
    private $menuDAO;

    public function __construct()
    {
        $this->menuDAO = new MenuDAO();
    }

    /**
     * Obtiene todos los platos del menú desde la base de datos
     */
    public function obtenerMenu()
    {
        $menu = $this->menuDAO->listarMenu();

        if (empty($menu)) {
            return "<p>No hay platillos registrados en el menú.</p>";
        }

        $html = "<div class='menu-container'>";
        foreach ($menu as $plato) {
            $html .= "
                <div class='plato-card'>
                    <h3>{$plato['nombre']}</h3>
                    <p>{$plato['descripcion']}</p>
                    <p><strong>Precio:</strong> Q{$plato['precio']}</p>
                    <button class='agregar-btn' data-id='{$plato['id_menu']}' data-precio='{$plato['precio']}'>Agregar al pedido</button>

                </div>
            ";
        }
        $html .= "</div>";

        return $html;
    }
}
?>
