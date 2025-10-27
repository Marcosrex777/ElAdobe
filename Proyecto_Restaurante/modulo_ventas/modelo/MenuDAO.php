<?php
// MenuDAO.php
// Capa de acceso a datos para los platillos del menú

require_once("Conexion.php");

class MenuDAO
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public function listarMenu()
    {
        $sql = "SELECT id, nombre, descripcion, precio FROM menu";
        $resultado = $this->conexion->ejecutarConsulta($sql);

        if ($resultado === false) {
            return [];
        }

        $menu = [];
        while ($fila = $resultado->fetch_assoc()) {
            $menu[] = $fila;
        }

        return $menu;
    }
}
?>
