<?php
// Finalizacion de pedidos y generación de facturas
require_once("../Modelo/PedidoDAO.php");
require_once("../Modelo/CuentaDAO.php");

class FinalizacionControlador {
    private $pedidoDAO;
    private $cuentaDAO;

    public function __construct() {
        $this->pedidoDAO = new PedidoDAO();
        $this->cuentaDAO = new CuentaDAO();
    }

    /**
     * Muestra el formulario de finalización del pedido
     */
    public function mostrarFormularioFinalizacion($id_mesa, $id_usuario) {
        $pedido = $this->pedidoDAO->obtenerPedidoPorMesa($id_mesa);
        
        if (!$pedido) {
            return "<div class='error'>No hay pedido activo en esta mesa.</div>";
        }

        $detalles = $this->pedidoDAO->obtenerDetalles($pedido['id_pedido']);
        
        if (empty($detalles)) {
            return "<div class='error'>No hay platillos en el pedido.</div>";
        }

        $html = "
        <div class='finalizacion-container'>
            <h2>🍽️ Finalizar Pedido - Mesa #{$id_mesa}</h2>
            
            <div class='resumen-pedido'>
                <h3>Resumen del Pedido</h3>
                <div class='items-pedido'>";
        
        foreach ($detalles as $detalle) {
            $html .= "
                    <div class='item-pedido' data-id='{$detalle['id_detalle_pedido']}'>
                        <span class='nombre'>{$detalle['nombre']}</span>
                        <span class='cantidad'>Cantidad: {$detalle['cantidad']}</span>
                        <span class='precio'>Q{$detalle['precio_unitario']} c/u</span>
                        <span class='subtotal'>Subtotal: Q{$detalle['subtotal']}</span>
                    </div>";
        }
        
        $html .= "
                </div>
                <div class='total-pedido'>
                    <strong>Total del Pedido: Q{$pedido['total']}</strong>
                </div>
            </div>

            <form id='form-finalizacion' method='POST' action='../Controlador/FinalizacionControlador.php'>
                <input type='hidden' name='id_pedido' value='{$pedido['id_pedido']}'>
                <input type='hidden' name='id_mesa' value='{$id_mesa}'>
                <input type='hidden' name='id_usuario' value='{$id_usuario}'>
                
                <div class='configuracion-cuentas'>
                    <h3>🏷️ Configuración de Cuentas</h3>
                    <label>
                        <input type='radio' name='tipo_cuenta' value='unica' checked onchange='toggleCuentasSeparadas()'>
                        Cuenta Única
                    </label>
                    <label>
                        <input type='radio' name='tipo_cuenta' value='separada' onchange='toggleCuentasSeparadas()'>
                        Cuentas Separadas
                    </label>
                    
                    <div id='cuenta-unica' class='cuenta-config'>
                        <h4>Datos del Cliente (Cuenta Única)</h4>
                        <div class='form-group'>
                            <label>Nombre del Cliente:</label>
                            <input type='text' name='cliente_nombre' placeholder='Nombre completo' required>
                        </div>
                        <div class='form-group'>
                            <label>NIT:</label>
                            <input type='text' name='cliente_nit' placeholder='NIT (opcional)'>
                        </div>
                        <div class='form-group'>
                            <label>Método de Pago:</label>
                            <select name='metodo_pago' required>
                                <option value='efectivo'>Efectivo</option>
                                <option value='tarjeta'>Tarjeta</option>
                                <option value='transferencia'>Transferencia</option>
                                <option value='mixto'>Mixto</option>
                            </select>
                        </div>
                        <div class='form-group'>
                            <label>Propina:</label>
                            <input type='number' name='propina' value='0' min='0' step='0.01'>
                        </div>
                    </div>
                    
                    <div id='cuenta-separada' class='cuenta-config' style='display:none;'>
                        <h4>🏷️ Configurar Cuentas Separadas</h4>
                        <div id='contenedor-cuentas'>
                            <!-- Las cuentas se generarán dinámicamente -->
                        </div>
                        <button type='button' onclick='agregarCuenta()' class='btn-agregar-cuenta'>➕ Agregar Cuenta</button>
                    </div>
                </div>
                
                <div class='acciones-finalizacion'>
                    <button type='submit' name='accion' value='procesar_finalizacion' class='btn-procesar'>
                        🧾 Generar Factura y Finalizar
                    </button>
                    <button type='button' onclick='window.history.back()' class='btn-cancelar'>
                        ↩️ Cancelar
                    </button>
                </div>
            </form>
        </div>

        <script>
            function toggleCuentasSeparadas() {
                const cuentaUnica = document.getElementById('cuenta-unica');
                const cuentaSeparada = document.getElementById('cuenta-separada');
                const tipoCuenta = document.querySelector('input[name=\"tipo_cuenta\"]:checked').value;
                
                if (tipoCuenta === 'unica') {
                    cuentaUnica.style.display = 'block';
                    cuentaSeparada.style.display = 'none';
                } else {
                    cuentaUnica.style.display = 'none';
                    cuentaSeparada.style.display = 'block';
                    if (document.querySelectorAll('.cuenta-item').length === 0) {
                        agregarCuenta();
                    }
                }
            }

            let contadorCuentas = 0;
            function agregarCuenta() {
                contadorCuentas++;
                const contenedor = document.getElementById('contenedor-cuentas');
                const cuentaHtml = `
                    <div class='cuenta-item' data-cuenta='\${contadorCuentas}'>
                        <div class='cuenta-header'>
                            <h5>Cuenta \${contadorCuentas}</h5>
                            <button type='button' onclick='eliminarCuenta(\${contadorCuentas})' class='btn-eliminar'>🗑️</button>
                        </div>
                        <div class='form-group'>
                            <label>Nombre del Cliente:</label>
                            <input type='text' name='cuentas[\${contadorCuentas}][cliente_nombre]' placeholder='Nombre completo' required>
                        </div>
                        <div class='form-group'>
                            <label>NIT:</label>
                            <input type='text' name='cuentas[\${contadorCuentas}][cliente_nit]' placeholder='NIT (opcional)'>
                        </div>
                        <div class='form-group'>
                            <label>Método de Pago:</label>
                            <select name='cuentas[\${contadorCuentas}][metodo_pago]' required>
                                <option value='efectivo'>Efectivo</option>
                                <option value='tarjeta'>Tarjeta</option>
                                <option value='transferencia'>Transferencia</option>
                                <option value='mixto'>Mixto</option>
                            </select>
                        </div>
                        <div class='form-group'>
                            <label>Propina:</label>
                            <input type='number' name='cuentas[\${contadorCuentas}][propina]' value='0' min='0' step='0.01'>
                        </div>
                        <div class='asignacion-items'>
                            <h6>Asignar Ítems:</h6>
                            <!-- Los items se asignarán dinámicamente -->
                        </div>
                    </div>
                `;
                contenedor.innerHTML += cuentaHtml;
                actualizarAsignacionItems();
            }

            function eliminarCuenta(numeroCuenta) {
                const cuenta = document.querySelector(`[data-cuenta=\"\${numeroCuenta}\"]`);
                if (cuenta) {
                    cuenta.remove();
                    // Reorganizar números de cuenta si es necesario
                }
            }

            function actualizarAsignacionItems() {
                const items = document.querySelectorAll('.item-pedido');
                const cuentas = document.querySelectorAll('.cuenta-item');
                
                cuentas.forEach(cuenta => {
                    const asignacionDiv = cuenta.querySelector('.asignacion-items');
                    let html = '<div class=\"items-disponibles\">';
                    
                    items.forEach(item => {
                        const idDetalle = item.dataset.id;
                        const nombre = item.querySelector('.nombre').textContent;
                        const cantidad = item.querySelector('.cantidad').textContent;
                        
                        html += `
                            <div class='item-asignacion'>
                                <label>\${nombre} (\${cantidad})</label>
                                <input type='number' 
                                       name='cuentas[\${cuenta.dataset.cuenta}][items][\${idDetalle}]' 
                                       value='0' 
                                       min='0' 
                                       max='\${cantidad.split(': ')[1]}'
                                       onchange='validarAsignacion(this)'>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    asignacionDiv.innerHTML = html;
                });
            }

            function validarAsignacion(input) {
                // Validar que no se asignen más items de los disponibles
                const max = parseInt(input.max);
                const valor = parseInt(input.value);
                
                if (valor > max) {
                    input.value = max;
                    alert('No puedes asignar más items de los disponibles');
                }
            }

            // Inicializar
            document.addEventListener('DOMContentLoaded', function() {
                toggleCuentasSeparadas();
            });
        </script>";

        return $html;
    }

    /**
     * Procesa la finalización del pedido
     */
    public function procesarFinalizacion($id_pedido, $id_mesa, $id_usuario, $datos) {
        try {
            if ($datos['tipo_cuenta'] === 'unica') {
                return $this->procesarCuentaUnica($id_pedido, $id_mesa, $id_usuario, $datos);
            } else {
                return $this->procesarCuentasSeparadas($id_pedido, $id_mesa, $id_usuario, $datos);
            }
        } catch (Exception $e) {
            error_log("Error en procesarFinalizacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Procesa una cuenta única
     */
    private function procesarCuentaUnica($id_pedido, $id_mesa, $id_usuario, $datos) {
        $conn = $this->pedidoDAO->conexion->getConexion();
        $conn->begin_transaction();

        try {
            // Crear venta
            $pedido = $this->pedidoDAO->obtenerPedidoPorId($id_pedido);
            $iva = $pedido['total'] * 0.12; // 12% IVA
            $propina = floatval($datos['propina']);
            $total = $pedido['total'] + $iva + $propina;

            $sqlVenta = "INSERT INTO Ventas (id_mesa, id_usuario, total, metodo_pago, estado) 
                         VALUES (?, ?, ?, ?, 'pagada')";
            $stmtVenta = $conn->prepare($sqlVenta);
            $stmtVenta->bind_param("iids", $id_mesa, $id_usuario, $total, $datos['metodo_pago']);
            $stmtVenta->execute();
            $id_venta = $stmtVenta->insert_id;

            // Crear factura
            $numeroFactura = 'FAC-' . date('Ymd-His') . '-' . $id_venta;
            $sqlFactura = "INSERT INTO Facturas (id_venta, id_pedido, numero_factura, subtotal, iva, propina, total, metodo_pago, cliente_nombre, cliente_nit) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtFactura = $conn->prepare($sqlFactura);
            $stmtFactura->bind_param("iisdddddss", $id_venta, $id_pedido, $numeroFactura, $pedido['total'], $iva, $propina, $total, $datos['metodo_pago'], $datos['cliente_nombre'], $datos['cliente_nit']);
            $stmtFactura->execute();

            // Copiar detalles del pedido a detalle_venta
            $detalles = $this->pedidoDAO->obtenerDetalles($id_pedido);
            foreach ($detalles as $detalle) {
                $sqlDV = "INSERT INTO Detalle_Venta (id_venta, id_menu, cantidad, precio_unitario) 
                          VALUES (?, ?, ?, ?)";
                $stmtDV = $conn->prepare($sqlDV);
                $stmtDV->bind_param("iiid", $id_venta, $detalle['id_menu'], $detalle['cantidad'], $detalle['precio_unitario']);
                $stmtDV->execute();
            }

            // Actualizar estado del pedido y mesa
            $this->pedidoDAO->actualizarEstadoPedido($id_pedido, 'finalizado');
            $this->pedidoDAO->actualizarEstadoMesa($id_mesa, 'libre');

            $conn->commit();
            return $numeroFactura;

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Procesa cuentas separadas
     */
    private function procesarCuentasSeparadas($id_pedido, $id_mesa, $id_usuario, $datos) {
        $conn = $this->pedidoDAO->conexion->getConexion();
        $conn->begin_transaction();

        try {
            $pedido = $this->pedidoDAO->obtenerPedidoPorId($id_pedido);
            $detalles = $this->pedidoDAO->obtenerDetalles($id_pedido);
            $facturas = [];

            foreach ($datos['cuentas'] as $numeroCuenta => $cuenta) {
                // Calcular subtotal de la cuenta
                $subtotal = 0;
                foreach ($cuenta['items'] as $id_detalle => $cantidad) {
                    if ($cantidad > 0) {
                        foreach ($detalles as $detalle) {
                            if ($detalle['id_detalle_pedido'] == $id_detalle) {
                                $subtotal += ($detalle['precio_unitario'] * $cantidad);
                                break;
                            }
                        }
                    }
                }

                if ($subtotal > 0) {
                    $iva = $subtotal * 0.12;
                    $propina = floatval($cuenta['propina']);
                    $total = $subtotal + $iva + $propina;

                    // Crear venta
                    $sqlVenta = "INSERT INTO Ventas (id_mesa, id_usuario, total, metodo_pago, estado) 
                                 VALUES (?, ?, ?, ?, 'pagada')";
                    $stmtVenta = $conn->prepare($sqlVenta);
                    $stmtVenta->bind_param("iids", $id_mesa, $id_usuario, $total, $cuenta['metodo_pago']);
                    $stmtVenta->execute();
                    $id_venta = $stmtVenta->insert_id;

                    // Crear factura
                    $numeroFactura = 'FAC-' . date('Ymd-His') . '-' . $id_venta . '-C' . $numeroCuenta;
                    $sqlFactura = "INSERT INTO Facturas (id_venta, id_pedido, numero_factura, subtotal, iva, propina, total, metodo_pago, cliente_nombre, cliente_nit) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtFactura = $conn->prepare($sqlFactura);
                    $stmtFactura->bind_param("iisdddddss", $id_venta, $id_pedido, $numeroFactura, $subtotal, $iva, $propina, $total, $cuenta['metodo_pago'], $cuenta['cliente_nombre'], $cuenta['cliente_nit']);
                    $stmtFactura->execute();

                    $facturas[] = $numeroFactura;

                    // Crear cuenta separada
                    $sqlCuenta = "INSERT INTO Cuentas_Separadas (id_pedido, numero_cuenta, cliente_nombre, cliente_nit, subtotal, iva, propina, total, metodo_pago) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtCuenta = $conn->prepare($sqlCuenta);
                    $stmtCuenta->bind_param("iissddddss", $id_pedido, $numeroCuenta, $cuenta['cliente_nombre'], $cuenta['cliente_nit'], $subtotal, $iva, $propina, $total, $cuenta['metodo_pago']);
                    $stmtCuenta->execute();
                    $id_cuenta = $stmtCuenta->insert_id;

                    // Guardar detalles de la cuenta
                    foreach ($cuenta['items'] as $id_detalle => $cantidad) {
                        if ($cantidad > 0) {
                            $sqlDetalleCuenta = "INSERT INTO Detalle_Cuenta (id_cuenta, id_detalle_pedido, cantidad_asignada) 
                                                 VALUES (?, ?, ?)";
                            $stmtDetalle = $conn->prepare($sqlDetalleCuenta);
                            $stmtDetalle->bind_param("iii", $id_cuenta, $id_detalle, $cantidad);
                            $stmtDetalle->execute();
                        }
                    }

                    // Crear detalle de venta
                    foreach ($cuenta['items'] as $id_detalle => $cantidad) {
                        if ($cantidad > 0) {
                            foreach ($detalles as $detalle) {
                                if ($detalle['id_detalle_pedido'] == $id_detalle) {
                                    $sqlDV = "INSERT INTO Detalle_Venta (id_venta, id_menu, cantidad, precio_unitario) 
                                              VALUES (?, ?, ?, ?)";
                                    $stmtDV = $conn->prepare($sqlDV);
                                    $stmtDV->bind_param("iiid", $id_venta, $detalle['id_menu'], $cantidad, $detalle['precio_unitario']);
                                    $stmtDV->execute();
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            // Actualizar estado del pedido y mesa
            $this->pedidoDAO->actualizarEstadoPedido($id_pedido, 'finalizado');
            $this->pedidoDAO->actualizarEstadoMesa($id_mesa, 'libre');

            $conn->commit();
            return $facturas;

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * Genera el HTML de la factura
     */
    public function generarFacturaHTML($numeroFactura) {
        // Aquí implementarías la generación del PDF o HTML de la factura
        // Por ahora devolvemos un HTML básico
        return "
        <div class='factura'>
            <h2>🍽️ Factura Electrónica - Restaurante El Adobe</h2>
            <p><strong>Número de Factura:</strong> {$numeroFactura}</p>
            <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
            <div class='factura-detalles'>
                <!-- Los detalles de la factura se cargarían aquí -->
            </div>
            <button onclick='window.print()' class='btn-imprimir'>🖨️ Imprimir Factura</button>
        </div>";
    }
}

// Manejo de peticiones
if ($_POST) {
    if (isset($_POST['accion']) && $_POST['accion'] === 'procesar_finalizacion') {
        $controlador = new FinalizacionControlador();
        
        $resultado = $controlador->procesarFinalizacion(
            intval($_POST['id_pedido']),
            intval($_POST['id_mesa']),
            intval($_POST['id_usuario']),
            $_POST
        );

        if ($resultado) {
            if (is_array($resultado)) {
                // Múltiples facturas (cuentas separadas)
                $mensaje = "✅ Cuentas procesadas correctamente. Facturas generadas: " . implode(', ', $resultado);
            } else {
                // Una factura (cuenta única)
                $mensaje = "✅ Cuenta procesada correctamente. Factura: {$resultado}";
            }
            
            echo "<script>
                alert('{$mensaje}');
                window.location.href = '../vista/index.php';
            </script>";
        } else {
            echo "<script>
                alert('❌ Error al procesar la finalización');
                window.history.back();
            </script>";
        }
    }
}
?>