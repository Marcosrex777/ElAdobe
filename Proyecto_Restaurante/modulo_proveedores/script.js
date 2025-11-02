document.addEventListener("DOMContentLoaded", () => {
    const traerBtn = document.getElementById("traerBtn");
    const guardarBtn = document.getElementById("guardarBtn");
    const tabla = document.getElementById("tablaProveedores");
    const tbody = tabla.querySelector("tbody");
    const thead = tabla.querySelector("thead");

    // Ocultar encabezado al inicio
    thead.style.display = "none";

    // Traer proveedores
    traerBtn.addEventListener("click", async () => {
        try {
            const res = await fetch("traer_proveedor.php");
            const data = await res.json();
            tbody.innerHTML = "";

            if (!Array.isArray(data) || data.length === 0) {
                thead.style.display = "none";
                tbody.innerHTML = "<tr><td colspan='8'>No hay proveedores registrados.</td></tr>";
                return;
            }

            thead.style.display = "table-header-group";

            data.forEach(p => {
                const id = p.id ?? p.id_proveedor ?? "";
                const nombre = p.nombre_proveedor ?? p.nombre ?? "";
                const contacto = p.persona_contacto ?? p.contacto ?? "";
                const telefono = p.telefono ?? "";
                const correo = p.correo ?? p.email ?? "";
                const direccion = p.direccion ?? p.dir ?? p.address ?? "";
                const categoria = p.categoria ?? "";
                const estado = p.estado ?? "";

                const fila = document.createElement("tr");
                fila.innerHTML = `
                    <td>${nombre}</td>
                    <td>${contacto}</td>
                    <td>${telefono}</td>
                    <td>${correo}</td>
                    <td>${direccion}</td>
                    <td>${categoria}</td>
                    <td>${estado}</td>
                    <td>
                        <button class="editarBtn"
                            data-id="${escapeHtml(id)}"
                            data-nombre="${escapeHtml(nombre)}"
                            data-contacto="${escapeHtml(contacto)}"
                            data-telefono="${escapeHtml(telefono)}"
                            data-correo="${escapeHtml(correo)}"
                            data-direccion="${escapeHtml(direccion)}"
                            data-categoria="${escapeHtml(categoria)}"
                            data-estado="${escapeHtml(estado)}"
                        >Editar</button>
                    </td>
                `;
                tbody.appendChild(fila);
            });

            // Evento editar
            document.querySelectorAll(".editarBtn").forEach(btn => {
                btn.addEventListener("click", (e) => {
                    const b = e.currentTarget;
                    document.getElementById("id_proveedor").value = b.dataset.id ?? "";
                    document.getElementById("nombre_proveedor").value = b.dataset.nombre ?? "";
                    document.getElementById("persona_contacto").value = b.dataset.contacto ?? "";
                    document.getElementById("telefono").value = b.dataset.telefono ?? "";
                    document.getElementById("correo").value = b.dataset.correo ?? "";
                    document.getElementById("direccion").value = b.dataset.direccion ?? "";
                    document.getElementById("categoria").value = b.dataset.categoria ?? "";
                    document.getElementById("estado").value = b.dataset.estado ?? "";
                    guardarBtn.textContent = "Actualizar Proveedor";
                });
            });

        } catch (err) {
            console.error("Error al traer proveedores:", err);
            tbody.innerHTML = "<tr><td colspan='9'>Error al cargar proveedores (ver consola).</td></tr>";
        }
    });

    // Guardar o actualizar
    guardarBtn.addEventListener("click", async () => {
        const id = document.getElementById("id_proveedor").value;
        const nombre = document.getElementById("nombre_proveedor").value.trim();
        const contacto = document.getElementById("persona_contacto").value.trim();
        const telefono = document.getElementById("telefono").value.trim();
        const correo = document.getElementById("correo").value.trim();
        const direccion = document.getElementById("direccion").value.trim();
        const categoria = document.getElementById("categoria").value.trim();
        const estado = document.getElementById("estado").value;

        if (!nombre) {
            alert("Ingrese nombre del proveedor.");
            return;
        }

        const fd = new FormData();
        fd.append("id", id);
        fd.append("nombre_proveedor", nombre);
        fd.append("persona_contacto", contacto);
        fd.append("telefono", telefono);
        fd.append("correo", correo);
        fd.append("direccion", direccion);
        fd.append("categoria", categoria);
        fd.append("estado", estado);

        try {
            const res = await fetch("guardar_proveedor.php", {
                method: "POST",
                body: fd
            });
            const text = await res.text();
            alert(text);
            traerBtn.click();
            limpiarFormulario();
        } catch (err) {
            console.error("Error al guardar:", err);
            alert("Error al guardar (ver consola).");
        }
    });

    function limpiarFormulario() {
        document.getElementById("id_proveedor").value = "";
        document.getElementById("nombre_proveedor").value = "";
        document.getElementById("persona_contacto").value = "";
        document.getElementById("telefono").value = "";
        document.getElementById("correo").value = "";
        document.getElementById("direccion").value = "";
        document.getElementById("categoria").value = "";
        document.getElementById("estado").value = "Activo";
        guardarBtn.textContent = "Guardar Datos";
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");
    }
});
