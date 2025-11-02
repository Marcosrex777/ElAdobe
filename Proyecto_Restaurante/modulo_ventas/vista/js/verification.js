// verification.js - Colócalo en vista/js/verification.js
console.log('✅ Script de verificación cargado correctamente');

function verificarCargaScripts() {
    console.log('📋 Scripts cargados:');
    console.log('- generarFacturaPDF:', typeof generarFacturaPDF);
    console.log('- PDFGenerator:', typeof PDFGenerator);
}

// Ejecutar verificación cuando se cargue la página
document.addEventListener('DOMContentLoaded', verificarCargaScripts);