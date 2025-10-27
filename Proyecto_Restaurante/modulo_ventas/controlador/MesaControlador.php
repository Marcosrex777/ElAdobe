<?php
// MesaControlador.php
require_once("../Modelo/MesaDAO.php");

class MesaControlador
{
    private $mesaDAO;

    public function __construct()
    {
        $this->mesaDAO = new MesaDAO();
    }

    public function obtenerMesas()
    {
        $mesas = $this->mesaDAO->listarMesas();

        if (empty($mesas)) {
            return "<p>No hay mesas registradas en el sistema.</p>";
        }

        $html = "";
        foreach ($mesas as $mesa) {
            $clase = strtolower($mesa['estado']) === 'ocupada' ? 'ocupada' : '';
            $disabled = strtolower($mesa['estado']) === 'ocupada' ? 'disabled' : '';

            $html .= "
                <button class='mesa-btn {$clase}' data-id='{$mesa['id_mesa']}' {$disabled}>
                    Mesa {$mesa['numero']}<br>
                    <small>Estado: {$mesa['estado']}</small>
                </button>
            ";
        }

        return $html;
    }
}

// Manejo de peticiones AJAX
if (isset($_GET['accion']) && $_GET['accion'] === 'listar') {
    $controlador = new MesaControlador();
    echo $controlador->obtenerMesas();
}
?>
