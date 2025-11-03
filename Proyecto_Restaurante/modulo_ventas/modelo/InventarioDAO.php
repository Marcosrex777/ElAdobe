<?php
require_once("Conexion.php");

class InventarioDAO {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }
    // Agregar este método en la clase InventarioDAO (después del constructor)

/**
 * Verificación rápida de inventario (sin transacciones)
 */
public function verificarInventarioRapido($id_menu, $cantidad_platillos) {
    $sql = "SELECT dr.id_comestible, dr.cantidad_usada, c.stock, p.nombre as nombre_ingrediente,
                   (dr.cantidad_usada * ?) as cantidad_requerida,
                   c.stock - (dr.cantidad_usada * ?) as stock_restante,
                   CASE 
                       WHEN c.stock >= (dr.cantidad_usada * ?) THEN 'suficiente'
                       ELSE 'insuficiente'
                   END as estado
            FROM detalle_receta dr
            JOIN recetas r ON dr.id_receta = r.id_receta
            JOIN comestibles c ON dr.id_comestible = c.id_comestible
            JOIN productos p ON c.id_producto = p.id_producto
            WHERE r.id_menu = ?";
    
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->bind_param("dddi", $cantidad_platillos, $cantidad_platillos, $cantidad_platillos, $id_menu);
    $stmt->execute();
    $result = $stmt->get_result();
    $ingredientes = $result->fetch_all(MYSQLI_ASSOC);
    
    $faltantes = [];
    $suficiente = true;
    
    foreach ($ingredientes as $ing) {
        if ($ing['estado'] === 'insuficiente') {
            $suficiente = false;
            $faltantes[] = [
                'ingrediente' => $ing['nombre_ingrediente'],
                'requerido' => $ing['cantidad_requerida'],
                'disponible' => $ing['stock'],
                'faltante' => $ing['cantidad_requerida'] - $ing['stock']
            ];
        }
    }
    
    return [
        'suficiente' => $suficiente,
        'faltantes' => $faltantes,
        'ingredientes' => $ingredientes
    ];
}
    public function getConexion() {
    return $this->conexion->getConexion();
}

    /**
     * Verifica si hay suficiente inventario para preparar un platillo
     */
    public function verificarInventarioPlatillo($id_menu, $cantidad_platillos) {
        $conn = $this->conexion->getConexion();
        $conn->autocommit(FALSE);
        $conn->begin_transaction();
        
        try {
            // Obtener receta y bloquear registros para evitar condiciones de carrera
            $sql = "SELECT dr.id_comestible, dr.cantidad_usada, c.stock, p.nombre as nombre_ingrediente,
                           (dr.cantidad_usada * ?) as cantidad_requerida,
                           c.stock - (dr.cantidad_usada * ?) as stock_restante
                    FROM detalle_receta dr
                    JOIN recetas r ON dr.id_receta = r.id_receta
                    JOIN comestibles c ON dr.id_comestible = c.id_comestible
                    JOIN productos p ON c.id_producto = p.id_producto
                    WHERE r.id_menu = ?
                    FOR UPDATE"; // Bloqueo para evitar condiciones de carrera
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ddi", $cantidad_platillos, $cantidad_platillos, $id_menu);
            $stmt->execute();
            $result = $stmt->get_result();
            $ingredientes = $result->fetch_all(MYSQLI_ASSOC);
            
            $faltantes = [];
            $suficiente = true;
            
            foreach ($ingredientes as $ing) {
                $cantidad_requerida = $ing['cantidad_requerida'];
                $stock_actual = $ing['stock'];
                
                if ($stock_actual < $cantidad_requerida) {
                    $suficiente = false;
                    $faltantes[] = [
                        'ingrediente' => $ing['nombre_ingrediente'],
                        'requerido' => $cantidad_requerida,
                        'disponible' => $stock_actual,
                        'faltante' => $cantidad_requerida - $stock_actual
                    ];
                }
            }
            
            $conn->commit();
            
            return [
                'suficiente' => $suficiente,
                'faltantes' => $faltantes,
                'ingredientes' => $ingredientes
            ];
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        } finally {
            $conn->autocommit(TRUE);
        }
    }

    /**
     * Actualiza el inventario después de confirmar un pedido
     */
    public function actualizarInventarioPedido($id_menu, $cantidad_platillos) {
        $conn = $this->conexion->getConexion();
        $conn->autocommit(FALSE);
        $conn->begin_transaction();
        
        try {
            // Verificar inventario nuevamente (doble verificación)
            $verificacion = $this->verificarInventarioPlatillo($id_menu, $cantidad_platillos);
            
            if (!$verificacion['suficiente']) {
                throw new Exception("Inventario insuficiente después de verificación final");
            }
            
            // Actualizar inventario
            $sql = "UPDATE comestibles c
                    JOIN detalle_receta dr ON c.id_comestible = dr.id_comestible
                    JOIN recetas r ON dr.id_receta = r.id_receta
                    SET c.stock = c.stock - (dr.cantidad_usada * ?)
                    WHERE r.id_menu = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $cantidad_platillos, $id_menu);
            $stmt->execute();
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error al actualizar inventario: " . $e->getMessage());
            return false;
        } finally {
            $conn->autocommit(TRUE);
        }
    }

/**
 * Actualiza inventario con reintentos automáticos para manejar deadlocks
 */
public function actualizarInventarioConReintento($id_menu, $cantidad, $max_reintentos = 3) {
    $reintentos = 0;
    
    while ($reintentos < $max_reintentos) {
        try {
            return $this->actualizarInventarioPedido($id_menu, $cantidad);
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1213 || $e->getCode() == 1205) { // Deadlock codes
                $reintentos++;
                usleep(100000 * $reintentos); // Backoff exponencial
                continue;
            }
            throw $e;
        }
    }
    
    throw new Exception("No se pudo completar la operación después de $max_reintentos reintentos");
}

    /**
     * Obtiene el estado del inventario para un platillo (sin transacción)
     */
    public function obtenerEstadoInventario($id_menu, $cantidad_platillos) {
        $sql = "SELECT dr.id_comestible, dr.cantidad_usada, c.stock, p.nombre as nombre_ingrediente,
                       (dr.cantidad_usada * ?) as cantidad_requerida,
                       c.stock - (dr.cantidad_usada * ?) as stock_restante,
                       CASE 
                           WHEN c.stock >= (dr.cantidad_usada * ?) THEN 'suficiente'
                           ELSE 'insuficiente'
                       END as estado
                FROM detalle_receta dr
                JOIN recetas r ON dr.id_receta = r.id_receta
                JOIN comestibles c ON dr.id_comestible = c.id_comestible
                JOIN productos p ON c.id_producto = p.id_producto
                WHERE r.id_menu = ?";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->bind_param("dddi", $cantidad_platillos, $cantidad_platillos, $cantidad_platillos, $id_menu);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>