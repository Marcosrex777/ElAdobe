<?php
// test-rutas.php - Para verificar todas las rutas
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test de Rutas PDF</title>
</head>
<body>
    <h1>Test de Rutas para PDF Generator</h1>
    
    <h2>Rutas a verificar:</h2>
    <ul>
        <li><a href="js/pdf-generator.js" target="_blank">js/pdf-generator.js</a></li>
        <li><a href="/ElAdobe/Proyecto_Restaurante/modulo_ventas/vista/js/pdf-generator.js" target="_blank">Ruta absoluta completa</a></li>
    </ul>
    
    <h2>Verificación automática:</h2>
    <div id="resultados"></div>
    
    <script>
        function verificarRuta(ruta, nombre) {
            return new Promise((resolve) => {
                const script = document.createElement('script');
                script.src = ruta;
                script.onload = () => resolve({ruta, nombre, estado: '✅ CARGADO'});
                script.onerror = () => resolve({ruta, nombre, estado: '❌ ERROR'});
                document.head.appendChild(script);
            });
        }
        
        async function verificarTodasLasRutas() {
            const rutas = [
                {ruta: 'js/pdf-generator.js', nombre: 'Relativa desde vista'},
                {ruta: '/ElAdobe/Proyecto_Restaurante/modulo_ventas/vista/js/pdf-generator.js', nombre: 'Absoluta completa'}
            ];
            
            const resultados = document.getElementById('resultados');
            resultados.innerHTML = '<p>Verificando rutas...</p>';
            
            for (const test of rutas) {
                const resultado = await verificarRuta(test.ruta, test.nombre);
                resultados.innerHTML += `<p><strong>${resultado.nombre}:</strong> ${resultado.estado} (${resultado.ruta})</p>`;
            }
            
            // Verificar funciones
            resultados.innerHTML += `<p><strong>Función generarFacturaPDF:</strong> ${typeof generarFacturaPDF !== 'undefined' ? '✅ DISPONIBLE' : '❌ NO DISPONIBLE'}</p>`;
            resultados.innerHTML += `<p><strong>Clase PDFGenerator:</strong> ${typeof PDFGenerator !== 'undefined' ? '✅ DISPONIBLE' : '❌ NO DISPONIBLE'}</p>`;
        }
        
        // Ejecutar verificación
        verificarTodasLasRutas();
    </script>
</body>
</html>