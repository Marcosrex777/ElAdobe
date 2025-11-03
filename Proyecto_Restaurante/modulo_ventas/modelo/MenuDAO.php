<?php
require_once(__DIR__ . "/../../conectar_bd.php");

class MenuDAO {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
            public function getConexion() {
        return $this->conn;
    }

    public function listarMenu() {
        $sql = "SELECT m.id_menu, m.nombre, m.descripcion, m.precio, c.nombre as categoria 
                FROM menu m 
                JOIN categorias c ON m.id_categoria = c.id_categoria 
                WHERE m.activo = TRUE 
                ORDER BY c.id_categoria, m.nombre";
        $resultado = $this->conn->query($sql);

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