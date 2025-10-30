/* ============================================================
   Archivo: inventario.js
   Descripción:
   Funciones globales del módulo de inventario (El Adobe)
   ============================================================ */

/* ------------------------------------------------------------
   1. Validación general de formularios
   ------------------------------------------------------------ */
document.addEventListener("DOMContentLoaded", () => {

    const formularios = document.querySelectorAll("form");

    formularios.forEach(form => {
        form.addEventListener("submit", e => {
            let valido = true;

            // Campos requeridos
            form.querySelectorAll("input[required], select[required]").forEach(campo => {
                const valor = campo.value.trim();

                // Campo vacío
                if (valor === "") {
                    alert("Complete todos los campos requeridos.");
                    valido = false;
                }

                // Evitar caracteres peligrosos (intentos de inyección SQL)
                const patronSQL = /('|;|--|#|\/\*|\*\/|xp_|drop|insert|update|delete|create|alter|truncate)/i;
                if (patronSQL.test(valor)) {
                    alert("No se permiten caracteres o palabras reservadas de SQL en los campos.");
                    valido = false;
                }
            });

            // Validar cantidad
            const cantidad = form.querySelector("input[name='cantidad']");
            if (cantidad && parseFloat(cantidad.value) <= 0) {
                alert("La cantidad debe ser mayor que 0.");
                valido = false;
            }

            // Validar coste si existe
            const coste = form.querySelector("input[name='coste']");
            if (coste && parseFloat(coste.value) <= 0) {
                alert("El coste debe ser mayor que 0.");
                valido = false;
            }

            if (!valido) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Inicializaciones adicionales
    inicializarFiltros();
    resaltarStock();
});

/* ------------------------------------------------------------
   2. Confirmación antes de ejecutar acciones críticas
   ------------------------------------------------------------ */
function confirmarAccion(mensaje, url) {
    if (confirm(mensaje)) {
        window.location = url;
    }
}

/* ------------------------------------------------------------
   3. Filtro de búsqueda para tablas
   ------------------------------------------------------------ */
function inicializarFiltros() {
    const buscador = document.getElementById("buscar");
    const tabla = document.getElementById("tabla-inventario");

    if (!buscador || !tabla) return;

    buscador.addEventListener("keyup", () => {
        const filtro = buscador.value.toLowerCase();
        const filas = tabla.getElementsByTagName("tr");

        for (let i = 1; i < filas.length; i++) {
            const texto = filas[i].textContent.toLowerCase();
            filas[i].style.display = texto.includes(filtro) ? "" : "none";
        }
    });
}

/* ------------------------------------------------------------
   4. Resalta filas según el stock
   ------------------------------------------------------------ */
function resaltarStock() {
    document.querySelectorAll("tr.sin-stock").forEach(fila => {
        fila.style.backgroundColor = "#f8d7da"; // rojo claro
    });
    document.querySelectorAll("tr.stock-ok").forEach(fila => {
        fila.style.backgroundColor = "#d4edda"; // verde claro
    });
}

/* ------------------------------------------------------------
   5. Mostrar alertas suaves en pantalla
   ------------------------------------------------------------ */
function mostrarAlerta(mensaje, tipo = "info") {
    const alerta = document.createElement("div");
    alerta.classList.add("alerta", tipo);
    alerta.textContent = mensaje;

    // Estilos básicos inline (para no depender del CSS)
    alerta.style.position = "fixed";
    alerta.style.top = "15px";
    alerta.style.right = "15px";
    alerta.style.padding = "10px 15px";
    alerta.style.backgroundColor = tipo === "error" ? "#dc3545" :
                                   tipo === "exito" ? "#28a745" :
                                   "#17a2b8";
    alerta.style.color = "white";
    alerta.style.borderRadius = "6px";
    alerta.style.boxShadow = "0 2px 6px rgba(0,0,0,0.2)";
    alerta.style.zIndex = "9999";
    alerta.style.fontFamily = "Arial, sans-serif";
    alerta.style.fontSize = "14px";

    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 3000);
}

/* ------------------------------------------------------------
   6. Formatear números con separador decimal (para informes)
   ------------------------------------------------------------ */
function formatearNumero(valor) {
    if (isNaN(valor)) return valor;
    return parseFloat(valor).toLocaleString("es-GT", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/* ------------------------------------------------------------
   7. Evitar pegar código SQL sospechoso en inputs
   ------------------------------------------------------------ */
document.addEventListener("paste", (e) => {
    const textoPegado = (e.clipboardData || window.clipboardData).getData("text");
    const patronSQL = /('|;|--|#|\/\*|\*\/|drop|insert|update|delete|create|alter|truncate|xp_)/i;

    if (patronSQL.test(textoPegado)) {
        e.preventDefault();
        alert("No se permite pegar texto con sentencias SQL.");
    }
});

/* ------------------------------------------------------------
   8. Bloquear teclas peligrosas en inputs (opcional)
   ------------------------------------------------------------ */
document.addEventListener("keydown", (e) => {
    const teclasBloqueadas = ["'", '"', ";"];
    if (teclasBloqueadas.includes(e.key)) {
        e.preventDefault();
        mostrarAlerta("Carácter no permitido.", "error");
    }
});

/* ------------------------------------------------------------
   9. Detectar parámetros en URL para mostrar alertas de éxito/error
   ------------------------------------------------------------ */
document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    if (params.has("exito")) {
        mostrarAlerta("Operación realizada correctamente.", "exito");
        limpiarParametrosURL();
    }
    if (params.has("error")) {
        mostrarAlerta("Ocurrió un error al procesar la solicitud.", "error");
        limpiarParametrosURL();
    }
});

/* ------------------------------------------------------------
   10. Limpiar parámetros de URL (para que no reaparezca el mensaje al recargar)
   ------------------------------------------------------------ */
function limpiarParametrosURL() {
    const url = new URL(window.location);
    url.search = "";
    window.history.replaceState({}, document.title, url);
}
