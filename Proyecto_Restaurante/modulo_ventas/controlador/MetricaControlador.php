<?php
require_once("../Modelo/PedidoDAO.php");

class MetricaControlador {
    private $pedidoDAO;

    public function __construct() {
        $this->pedidoDAO = new PedidoDAO();
    }

    /**
     * Obtiene métricas de tiempo para reportes
     */
    public function obtenerMetricasTiempo($fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        $sql = "
            SELECT 
                id_pedido,
                id_mesa,
                TIMESTAMPDIFF(MINUTE, fecha_envio, fecha_preparacion) as tiempo_espera,
                TIMESTAMPDIFF(MINUTE, fecha_preparacion, fecha_listo) as tiempo_preparacion,
                TIMESTAMPDIFF(MINUTE, fecha_listo, fecha_entregado) as tiempo_entrega,
                TIMESTAMPDIFF(MINUTE, fecha_envio, fecha_entregado) as tiempo_total
            FROM Pedidos 
            WHERE DATE(fecha_envio) = ? 
            AND estado IN ('entregado', 'finalizado', 'facturado')
        ";

        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene resumen de métricas del día
     */
    public function obtenerResumenMetricas($fecha = null) {
        $metricas = $this->obtenerMetricasTiempo($fecha);
        
        if (empty($metricas)) {
            return [
                'promedio_espera' => 0,
                'promedio_preparacion' => 0,
                'promedio_entrega' => 0,
                'promedio_total' => 0,
                'total_pedidos' => 0
            ];
        }

        $sumas = [
            'espera' => 0,
            'preparacion' => 0,
            'entrega' => 0,
            'total' => 0
        ];

        foreach ($metricas as $metrica) {
            $sumas['espera'] += $metrica['tiempo_espera'] ?: 0;
            $sumas['preparacion'] += $metrica['tiempo_preparacion'] ?: 0;
            $sumas['entrega'] += $metrica['tiempo_entrega'] ?: 0;
            $sumas['total'] += $metrica['tiempo_total'] ?: 0;
        }

        $total_pedidos = count($metricas);

        return [
            'promedio_espera' => round($sumas['espera'] / $total_pedidos, 2),
            'promedio_preparacion' => round($sumas['preparacion'] / $total_pedidos, 2),
            'promedio_entrega' => round($sumas['entrega'] / $total_pedidos, 2),
            'promedio_total' => round($sumas['total'] / $total_pedidos, 2),
            'total_pedidos' => $total_pedidos
        ];
    }
}
?>