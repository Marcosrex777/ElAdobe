<?php
require_once("Conexion.php");

class MesaDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }
    public function getConexion() {
    return $this->conexion->getConexion();
}

    // Listar todas las mesas con información del mesero asignado (si existe)
    public function listarMesas() {
        try {
            $sql = "SELECT m.id_mesa, m.numero, m.capacidad, m.estado, 
                           u.nombre_completo AS mesero
                    FROM Mesas m
                    LEFT JOIN Usuarios u ON m.id_mesero_asignado = u.id_usuario
                    ORDER BY m.numero ASC";
                    
            $resultado = $this->conexion->getConexion()->query($sql);
            
            if ($resultado === false) {
                error_log("Error en consulta listarMesas: " . $this->conexion->getConexion()->error);
                return [];
            }
            
            $mesas = [];
            while ($fila = $resultado->fetch_assoc()) {
                $mesas[] = $fila;
            }
            return $mesas;
            
        } catch (Exception $e) {
            error_log("Error en MesaDAO::listarMesas: " . $e->getMessage());
            return [];
        }
    }

    // Actualizar el estado de una mesa
    public function actualizarEstado($id_mesa, $estado) {
        $sql = "UPDATE Mesas SET estado = ? WHERE id_mesa = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("si", $estado, $id_mesa);
        return $stmt->execute();
    }

    // Asignar mesero a una mesa
    public function asignarMesero($id_mesa, $id_mesero) {
        $sql = "UPDATE Mesas SET id_mesero_asignado = ? WHERE id_mesa = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("ii", $id_mesero, $id_mesa);
        return $stmt->execute();
    }
}
?>