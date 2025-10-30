<?php
require_once("Conexion.php");

class NotificacionDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

//crea una nueva notificacion
    public function crearNotificacion($id_usuario, $mensaje) {
        $sql = "INSERT INTO Notificaciones (id_usuario, mensaje) VALUES (?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $mensaje);
        return $stmt->execute();
    }

//obtiene notificaciones no leidas
    public function obtenerNotificaciones($id_usuario, $no_leidas = true) {
        $sql = "SELECT * FROM Notificaciones WHERE id_usuario = ?";
        if ($no_leidas) {
            $sql .= " AND leida = FALSE";
        }
        $sql .= " ORDER BY fecha DESC";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

//marca notificaciones como leidas
    public function marcarLeidas($id_usuario) {
        $sql = "UPDATE Notificaciones SET leida = TRUE WHERE id_usuario = ? AND leida = FALSE";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();
    }

//  marca una notificacion como leida
    public function marcarLeida($id_notificacion) {
        $sql = "UPDATE Notificaciones SET leida = TRUE WHERE id_notificacion = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("i", $id_notificacion);
        return $stmt->execute();
    }
}
?>