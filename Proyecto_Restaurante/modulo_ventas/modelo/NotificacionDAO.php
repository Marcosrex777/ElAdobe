<?php
require_once(__DIR__ . "/../../conectar_bd.php");

class NotificacionDAO {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
            public function getConexion() {
        return $this->conn;
    }

    public function crearNotificacion($id_usuario, $mensaje) {
        $sql = "INSERT INTO notificaciones (id_usuario, mensaje) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id_usuario, $mensaje);
        return $stmt->execute();
    }

    public function obtenerNotificaciones($id_usuario, $no_leidas = true) {
        $sql = "SELECT * FROM notificaciones WHERE id_usuario = ?";
        if ($no_leidas) {
            $sql .= " AND leida = FALSE";
        }
        $sql .= " ORDER BY fecha DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function marcarLeidas($id_usuario) {
        $sql = "UPDATE notificaciones SET leida = TRUE WHERE id_usuario = ? AND leida = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();
    }

    public function marcarLeida($id_notificacion) {
        $sql = "UPDATE notificaciones SET leida = TRUE WHERE id_notificacion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_notificacion);
        return $stmt->execute();
    }
}
?>