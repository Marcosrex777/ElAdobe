$(document).ready(function () {
    // Al iniciar, cargar mesas
    cargarMesas();

    /**
     * 🔹 Función para cargar las mesas desde el controlador (AJAX)
     */
    function cargarMesas() {
        $.ajax({
            url: "../controlador/MesaControlador.php",
            method: "GET",
            data: { accion: "listar" },
            dataType: "html",
            success: function (respuesta) {
                $("#contenedor-mesas").html(respuesta);
            },
            error: function (xhr, status, error) {
                console.error("Error al cargar mesas:", error);
                alert("❌ No se pudieron cargar las mesas.");
            }
        });
    }

    /**
     * 🔹 Evento: cuando el usuario selecciona una mesa
     */
    $(document).on("click", ".mesa-btn", function () {
        const idMesa = $(this).data("id");
        $("#label-estado").text("Cargando menú...");
        cargarMenu(idMesa);
        $("#id-mesa-seleccionada").val(idMesa); // guardar en un input oculto
    });

    /**
     * 🔹 Función para cargar el menú según la mesa seleccionada
     */
    function cargarMenu(idMesa) {
        $.ajax({
            url: "../controlador/MenuControlador.php",
            method: "GET",
            data: { accion: "listar", mesa: idMesa },
            dataType: "html",
            success: function (respuesta) {
                $("#contenedor-menu").html(respuesta);
                $("#label-estado").text("Selecciona productos del menú");
            },
            error: function (xhr, status, error) {
                console.error("Error al cargar menú:", error);
                alert("❌ No se pudo cargar el menú.");
            }
        });
    }

    /**
     * 🔹 Agregar producto al pedido
     */
    $(document).on("click", ".agregar-btn", function () {
        const producto = $(this).data("nombre");
        const precio = parseFloat($(this).data("precio")) || 0;

        // Verificar si ya existe en el pedido
        const filaExistente = $(`#tabla-pedido tbody tr[data-producto='${producto}']`);
        if (filaExistente.length > 0) {
            let cantidad = parseInt(filaExistente.find(".cantidad").text());
            cantidad++;
            filaExistente.find(".cantidad").text(cantidad);
            const subtotal = cantidad * precio;
            filaExistente.find(".subtotal").text(`Q${subtotal.toFixed(2)}`);
        } else {
            const fila = `
                <tr data-producto="${producto}">
                    <td>${producto}</td>
                    <td class="cantidad">1</td>
                    <td class="subtotal">Q${precio.toFixed(2)}</td>
                </tr>`;
            $("#tabla-pedido tbody").append(fila);
        }

        actualizarTotal();
    });

    /**
     * 🔹 Actualizar total del pedido
     */
    function actualizarTotal() {
        let total = 0;
        $("#tabla-pedido tbody tr").each(function () {
            const subtotal = parseFloat($(this).find(".subtotal").text().replace("Q", "")) || 0;
            total += subtotal;
        });
        $("#total").text(total.toFixed(2));
    }

    /**
     * 🔹 Enviar pedido al servidor
     */
    $("#btn-enviar").click(function () {
        const idMesa = $("#id-mesa-seleccionada").val();
        if (!idMesa) {
            alert("⚠️ Selecciona una mesa antes de enviar el pedido.");
            return;
        }

        // Construir arreglo con productos del pedido
        const pedido = [];
        $("#tabla-pedido tbody tr").each(function () {
            const producto = $(this).find("td").eq(0).text();
            const cantidad = parseInt($(this).find(".cantidad").text());
            const subtotal = parseFloat($(this).find(".subtotal").text().replace("Q", ""));
            pedido.push({ producto, cantidad, subtotal });
        });

        if (pedido.length === 0) {
            alert("⚠️ No hay productos en el pedido.");
            return;
        }

        $.ajax({
            url: "../controlador/PedidoControlador.php",
            method: "POST",
            data: {
                accion: "guardar",
                mesa: idMesa,
                pedido: JSON.stringify(pedido)
            },
            success: function (respuesta) {
                alert("✅ Pedido enviado correctamente.");
                $("#label-estado").text("Pedido en preparación");
                $("#tabla-pedido tbody").empty();
                $("#total").text("0.00");
            },
            error: function (xhr, status, error) {
                console.error("Error al enviar pedido:", error);
                alert("❌ No se pudo enviar el pedido.");
            }
        });
    });
});
