// --- Lógica de Ventas ---
const pedidos = {}; // { mesa: [ {nombre, precio, estado} ] }

document.getElementById('mesaSelect').addEventListener('change', () => {
    const mesa = document.getElementById('mesaSelect').value;
    document.getElementById('mesaTitle').textContent = mesa || "-";
    renderPedido();
});

document.querySelectorAll('.btn-add').forEach(btn => {
    btn.addEventListener('click', () => {
        const mesa = document.getElementById('mesaSelect').value;
        if (!mesa) {
            alert("Selecciona una mesa primero.");
            return;
        }
        if (!pedidos[mesa]) pedidos[mesa] = [];
        pedidos[mesa].push({
            nombre: btn.dataset.name,
            precio: parseFloat(btn.dataset.price),
            estado: "Pendiente"
        });
        renderPedido();
    });
});

function renderPedido() {
    const mesa = document.getElementById('mesaSelect').value;
    const tbody = document.getElementById('pedidoBody');
    tbody.innerHTML = "";

    if (!mesa || !pedidos[mesa]) return;

    pedidos[mesa].forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.nombre}</td>
            <td>$${item.precio.toFixed(2)}</td>
            <td>
                <select class="estado-select" data-mesa="${mesa}" data-index="${index}">
                    <option ${item.estado==="Pendiente"?"selected":""}>Pendiente</option>
                    <option ${item.estado==="Preparando"?"selected":""}>Preparando</option>
                    <option ${item.estado==="Listo"?"selected":""}>Listo</option>
                    <option ${item.estado==="Entregado"?"selected":""}>Entregado</option>
                </select>
            </td>
            <td><button class="btn-delete" data-mesa="${mesa}" data-index="${index}">Eliminar</button></td>
        `;
        tbody.appendChild(tr);
    });

    // actualizar estados
    document.querySelectorAll('.estado-select').forEach(sel => {
        sel.addEventListener('change', e => {
            const mesa = e.target.dataset.mesa;
            const idx = e.target.dataset.index;
            pedidos[mesa][idx].estado = e.target.value;
        });
    });

    // eliminar item
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', e => {
            const mesa = e.target.dataset.mesa;
            const idx = e.target.dataset.index;
            pedidos[mesa].splice(idx, 1);
            renderPedido();
        });
    });
}

