<?php
require_once(__DIR__ . "/../../conectar_bd.php");

class MesaDAO {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
            public function getConexion() {
        return $this->conn;
    }

    public function listarMesas() {
        try {
            $sql = "SELECT m.id_mesa, m.numero, m.capacidad, m.estado, 
                           u.nombre_completo AS mesero
                    FROM Mesas m
                    LEFT JOIN Usuarios u ON m.id_mesero_asignado = u.id_usuario
                    ORDER BY m.numero ASC";
                    
            $resultado = $this->conn->query($sql);
            
            if ($resultado === false) {
                error_log("Error en consulta listarMesas: " . $this->conn->error);
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

    public function actualizarEstado($id_mesa, $estado) {
        $sql = "UPDATE Mesas SET estado = ? WHERE id_mesa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $estado, $id_mesa);
        return $stmt->execute();
    }

    public function asignarMesero($id_mesa, $id_mesero) {
        $sql = "UPDATE Mesas SET id_mesero_asignado = ? WHERE id_mesa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_mesero, $id_mesa);
        return $stmt->execute();
    }
}
?>