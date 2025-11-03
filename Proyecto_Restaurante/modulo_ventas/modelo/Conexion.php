<?php
class Conexion
{
    private $host = "localhost";
    private $usuario = "root";
    private $password = "cesar123"; 
    private $baseDatos = "eladobe";
    private $port = "3306";        
    private $conexion;

    public function __construct()
    {
        // Se incluye el puerto en la conexión
        $this->conexion = new mysqli(
            $this->host,
            $this->usuario,
            $this->password,
            $this->baseDatos,
            $this->port
        );

        if ($this->conexion->connect_error) {
            // Este método de manejo de errores es el original de la clase
            die("Error de conexión: " . $this->conexion->connect_error);
        }

        // Configuración para soportar UTF-8
        $this->conexion->set_charset("utf8");
        $this->conexion->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        $this->conexion->options(MYSQLI_OPT_READ_TIMEOUT, 10);
    }

public function getConexion() {
    return $this->conexion; // Retorna directamente la instancia mysqli
}
}
?>