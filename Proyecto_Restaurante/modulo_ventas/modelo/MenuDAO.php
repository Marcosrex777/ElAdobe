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
// Obtener todos los platillos del menú
public function listarMenu() {
    $sql = "SELECT m.id_menu, m.nombre, m.descripcion, m.precio, c.nombre as categoria 
            FROM menu m 
            JOIN categorias c ON m.id_categoria = c.id_categoria 
            WHERE m.activo = TRUE 
            ORDER BY c.id_categoria, m.nombre";
    $resultado = $this->conexion->getConexion()->query($sql);

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
