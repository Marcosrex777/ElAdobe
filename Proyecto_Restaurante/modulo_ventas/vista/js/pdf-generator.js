// pdf-generator.js - Versión con botón de imprimir mejorado
class PDFGenerator {
    constructor() {
        this.restauranteInfo = {
            nombre: 'Restaurante El Adobe',
            direccion: 'Ciudad, Dirección del restaurante',
            telefono: '+502 1234-5678',
            nit: '1234567-8'
        };
        console.log('✅ PDFGenerator inicializado');
    }

    /**
     * Genera una factura en PDF
     */
    async generarFactura(numeroFactura) {
        try {
            console.log('🔨 Iniciando generación de factura:', numeroFactura);
            
            // Obtener datos de la factura
            console.log('📡 Obteniendo datos de la factura...');
            const datosFactura = await this.obtenerDatosFactura(numeroFactura);
            
            if (!datosFactura) {
                throw new Error('No se pudieron obtener los datos de la factura');
            }

            console.log('✅ Datos de factura obtenidos:', datosFactura);

            // Obtener detalles de la factura
            console.log('📡 Obteniendo detalles de la factura...');
            const detallesFactura = await this.obtenerDetallesFactura(datosFactura.id_factura);
            console.log('✅ Detalles obtenidos:', detallesFactura);
            
            // Crear y mostrar la factura
            console.log('🎨 Generando interfaz de factura...');
            this.mostrarFacturaEnVentana(datosFactura, detallesFactura);
            console.log('✅ Factura generada exitosamente');

        } catch (error) {
            console.error('❌ Error generando PDF:', error);
            alert('Error al generar la factura PDF: ' + error.message);
        }
    }

    /**
     * Obtiene datos de la factura del servidor
     */
    async obtenerDatosFactura(numeroFactura) {
        try {
            console.log('🌐 Solicitando datos al servidor...');
            const url = `../controlador/FacturaControlador.php?accion=obtener_datos_factura&numero_factura=${encodeURIComponent(numeroFactura)}`;
            console.log('URL:', url);
            
            const response = await fetch(url);
            console.log('Respuesta HTTP:', response.status);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Datos recibidos:', data);
            return data;
            
        } catch (error) {
            console.error('❌ Error obteniendo datos factura:', error);
            return null;
        }
    }

    /**
     * Obtiene detalles de la factura del servidor
     */
    async obtenerDetallesFactura(idFactura) {
        try {
            console.log('🌐 Solicitando detalles al servidor...');
            const url = `../controlador/FacturaControlador.php?accion=obtener_detalles_factura&id_factura=${idFactura}`;
            console.log('URL:', url);
            
            const response = await fetch(url);
            console.log('Respuesta HTTP:', response.status);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Detalles recibidos:', data);
            return data;
            
        } catch (error) {
            console.error('❌ Error obteniendo detalles factura:', error);
            return [];
        }
    }

    /**
     * Muestra la factura en una nueva ventana
     */
    mostrarFacturaEnVentana(datos, detalles) {
        console.log('🪟 Creando ventana para factura...');
        
        // Crear ventana
        const ventana = window.open('', '_blank', 'width=900,height=700,scrollbars=yes,resizable=yes');
        if (!ventana) {
            alert('⚠️ Por favor permite ventanas emergentes para generar la factura');
            return;
        }

        // Generar contenido HTML
        const html = this.generarHTMLFactura(datos, detalles);
        
        ventana.document.write(html);
        ventana.document.close();
        
        console.log('✅ Ventana de factura creada');
        
        // Enfocar la ventana
        ventana.focus();
    }

    /**
     * Genera el HTML completo de la factura
     */
    generarHTMLFactura(datos, detalles) {
        console.log('📝 Generando HTML de la factura...');
        
        const fecha = new Date(datos.fecha_emision || datos.fecha_venta || new Date());
        const subtotal = parseFloat(datos.subtotal || 0);
        const iva = parseFloat(datos.iva || 0);
        const propina = parseFloat(datos.propina || 0);
        const total = parseFloat(datos.total || 0);

        return `
<!DOCTYPE html>
<html>
<head>
    <title>Factura ${datos.numero_factura}</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 0; 
            padding: 20px;
            background: white;
            color: #333;
        }
        .factura-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #8b5e3c;
            padding: 25px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header { 
            text-align: center; 
            border-bottom: 3px double #8b5e3c;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #8b5e3c;
            margin: 0;
            font-size: 28px;
        }
        .header h2 {
            color: #333;
            margin: 10px 0;
            font-size: 20px;
        }
        .info-factura {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .info-cliente, .info-restaurante {
            width: 48%;
            min-width: 300px;
            margin-bottom: 15px;
        }
        .info-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #8b5e3c;
        }
        .info-section h3 {
            margin-top: 0;
            color: #8b5e3c;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #8b5e3c;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totales {
            text-align: right;
            margin-top: 25px;
        }
        .total-table {
            width: 300px;
            margin-left: auto;
            border: 2px solid #8b5e3c;
        }
        .total-final {
            font-size: 1.2em;
            font-weight: bold;
            color: #8b5e3c;
            background-color: #fffaf3 !important;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            border-top: 2px solid #8b5e3c;
            padding-top: 15px;
            color: #666;
            font-style: italic;
        }
        .no-print { 
            display: block; 
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .factura-container { 
                border: none; 
                box-shadow: none;
                max-width: 100%;
                padding: 10px;
            }
        }
        .detalle-vacio {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        .botones-accion {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        .btn-imprimir {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            margin: 0 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-imprimir:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        .btn-cerrar {
            background: #6c757d;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            margin: 0 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-cerrar:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        .instrucciones {
            text-align: center;
            margin: 10px 0;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="factura-container">
        <!-- Cabecera -->
        <div class="header">
            <h1>🍽️ ${this.restauranteInfo.nombre}</h1>
            <h2>FACTURA ELECTRÓNICA</h2>
            <p><strong>NIT:</strong> ${this.restauranteInfo.nit} | <strong>Teléfono:</strong> ${this.restauranteInfo.telefono}</p>
            <p>${this.restauranteInfo.direccion}</p>
        </div>

        <!-- Información del cliente y factura -->
        <div class="info-factura">
            <div class="info-cliente">
                <div class="info-section">
                    <h3>DATOS DEL CLIENTE</h3>
                    <p><strong>Nombre:</strong> ${datos.cliente_nombre || 'Cliente General'}</p>
                    <p><strong>NIT:</strong> ${datos.cliente_nit || 'CF'}</p>
                    <p><strong>Dirección:</strong> ${datos.direccion || 'No especificada'}</p>
                </div>
            </div>
            <div class="info-restaurante">
                <div class="info-section">
                    <h3>DATOS DE LA FACTURA</h3>
                    <p><strong>No. Factura:</strong> ${datos.numero_factura}</p>
                    <p><strong>Fecha:</strong> ${fecha.toLocaleDateString()} ${fecha.toLocaleTimeString()}</p>
                    <p><strong>Mesa:</strong> ${datos.numero_mesa || datos.id_mesa}</p>
                    <p><strong>Mesero:</strong> ${datos.mesero || 'No asignado'}</p>
                    <p><strong>Método Pago:</strong> ${datos.metodo_pago || 'Efectivo'}</p>
                </div>
            </div>
        </div>

        <!-- Detalles de la factura -->
        <h3>DETALLES DEL CONSUMO</h3>
        ${this.generarTablaDetalles(detalles)}

        <!-- Totales -->
        ${this.generarSeccionTotales(subtotal, iva, propina, total)}

        <!-- Pie de página -->
        <div class="footer">
            <p><strong>¡Gracias por su visita!</strong></p>
            <p>Esta factura es un documento electrónico generado automáticamente</p>
            <p><strong>${this.restauranteInfo.nombre}</strong> - ${new Date().getFullYear()}</p>
        </div>
    </div>

    <!-- BOTONES DE ACCIÓN MEJORADOS -->
    <div class="botones-accion no-print">
        <div class="instrucciones">
            <p><strong>💡 Para guardar como PDF:</strong> Haz clic en "Imprimir PDF" y selecciona "Guardar como PDF" en el diálogo de impresión</p>
        </div>
        <button class="btn-imprimir" onclick="imprimirFactura()">
            🖨️ IMPRIMIR PDF
        </button>
        <button class="btn-cerrar" onclick="cerrarVentana()">
            ❌ CERRAR VENTANA
        </button>
    </div>

    <script>
        function imprimirFactura() {
            console.log('🖨️ Iniciando impresión de factura...');
            window.print();
        }
        
        function cerrarVentana() {
            console.log('❌ Cerrando ventana de factura...');
            window.close();
        }
        
        // Auto-enfocar el botón de imprimir para mejor UX
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Factura cargada - Lista para imprimir');
        });
    </script>
</body>
</html>`;
    }

    /**
     * Genera la tabla de detalles
     */
    generarTablaDetalles(detalles) {
        if (!detalles || detalles.length === 0) {
            return '<div class="detalle-vacio">No hay detalles disponibles para esta factura</div>';
        }

        let tablaHTML = `
        <table>
            <thead>
                <tr>
                    <th width="15%">Cantidad</th>
                    <th width="45%">Descripción</th>
                    <th width="20%">Precio Unitario</th>
                    <th width="20%">Subtotal</th>
                </tr>
            </thead>
            <tbody>`;

        detalles.forEach(detalle => {
            tablaHTML += `
                <tr>
                    <td>${detalle.cantidad}</td>
                    <td>${detalle.nombre}</td>
                    <td>Q${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                    <td>Q${parseFloat(detalle.subtotal).toFixed(2)}</td>
                </tr>`;
        });

        tablaHTML += `</tbody></table>`;
        return tablaHTML;
    }

    /**
     * Genera la sección de totales
     */
    generarSeccionTotales(subtotal, iva, propina, total) {
        return `
        <div class="totales">
            <table class="total-table">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td>Q${subtotal.toFixed(2)}</td>
                </tr>
                <tr>
                    <td><strong>IVA (12%):</strong></td>
                    <td>Q${iva.toFixed(2)}</td>
                </tr>
                <tr>
                    <td><strong>Propina:</strong></td>
                    <td>Q${propina.toFixed(2)}</td>
                </tr>
                <tr class="total-final">
                    <td><strong>TOTAL:</strong></td>
                    <td>Q${total.toFixed(2)}</td>
                </tr>
            </table>
        </div>`;
    }
}

// Función global para generar facturas
function generarFacturaPDF(numeroFactura) {
    console.log('🚀 Llamada a generarFacturaPDF:', numeroFactura);
    const generator = new PDFGenerator();
    generator.generarFactura(numeroFactura);
}

// Verificar que el script se cargó
console.log('✅ pdf-generator.js cargado correctamente');