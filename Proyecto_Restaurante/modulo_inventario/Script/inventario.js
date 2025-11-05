/* ============================================
   SCRIPT GLOBAL DEL SISTEMA DE INVENTARIO
   ============================================ */

document.addEventListener("DOMContentLoaded", function() {
    // Inicialización de DataTables con coloración de stock bajo
    if (document.getElementById('tabla-mobiliario')) {
        const table = $('#tabla-mobiliario').DataTable();
        
        // Función para colorear filas con stock bajo
        function marcarStockBajo() {
            table.rows().every(function() {
                const rowData = this.data();
                const stock = parseInt(rowData[4]); // Columna de stock
                const stockMin = parseInt(rowData[5]); // Columna de stock mínimo
                
                if (!isNaN(stock) && !isNaN(stockMin) && stock <= stockMin) {
                    $(this.node()).css('background-color', '#ffe5e5')
                              .attr('title', 'Stock bajo o igual al mínimo permitido');
                }
            });
        }

        // Aplicar coloración después de cada redibujado de la tabla
        table.on('draw', marcarStockBajo);
        // Aplicar coloración inicial
        marcarStockBajo();
    }
    /* ======= FUNCIÓN GLOBAL PARA MENSAJES ======= */
    function mostrarMensaje(tipo, texto) {
        let contenedor = document.querySelector(".mensaje-form");
        if (!contenedor) {
            contenedor = document.createElement("div");
            contenedor.className = "mensaje-form";
            document.querySelector(".contenedor").prepend(contenedor);
        }

        contenedor.innerText = texto;
        contenedor.className = `mensaje-form ${tipo}`;
        contenedor.style.display = "block";
        contenedor.style.opacity = "1";

        setTimeout(() => {
            contenedor.style.opacity = "0";
            setTimeout(() => contenedor.style.display = "none", 400);
        }, 2500);
    }

    /* ======= VALIDACIÓN DEL FORMULARIO DE PRODUCTOS ======= */
    const form = document.querySelector("form");
    if (form && document.getElementById("nombre")) {
        form.addEventListener("submit", function(event) {
            const nombre = document.getElementById("nombre");
            const tipo = document.getElementById("tipo");
            const categoria = document.getElementById("categoria");
            const unidad = document.getElementById("unidad_medida");
            const precio = document.getElementById("precio_unitario");
            const stock = document.getElementById("stock");
            const stockMinimo = document.getElementById("stock_minimo");

            const textoValido = /^[a-zA-ZÁÉÍÓÚáéíóúñÑ0-9\s.,-]+$/;
            const palabrasBloqueadas = ["SELECT", "INSERT", "UPDATE", "DELETE", "DROP", "ALTER", "CREATE", "--", ";", "/*", "*/"];

            if (!nombre.value.trim() || !textoValido.test(nombre.value)) {
                mostrarMensaje("error", "El nombre del producto contiene caracteres no permitidos.");
                event.preventDefault(); return;
            }

            if (!tipo.value) {
                mostrarMensaje("error", "Seleccione un tipo de producto válido (Comestible o Mobiliario).");
                event.preventDefault(); return;
            }

            if (!categoria.value.trim() || !textoValido.test(categoria.value)) {
                mostrarMensaje("error", "Ingrese una categoría válida sin caracteres especiales.");
                event.preventDefault(); return;
            }

            if (!unidad.value.trim() || !textoValido.test(unidad.value)) {
                mostrarMensaje("error", "La unidad de medida contiene caracteres no válidos.");
                event.preventDefault(); return;
            }

            if (precio.value === "" || parseFloat(precio.value) < 0) {
                mostrarMensaje("error", "Ingrese un precio unitario válido (mayor o igual a 0).");
                event.preventDefault(); return;
            }

            if (stock.value === "" || parseInt(stock.value) < 0) {
                mostrarMensaje("error", "El stock debe ser un número mayor o igual a 0.");
                event.preventDefault(); return;
            }

            if (stockMinimo.value === "" || parseInt(stockMinimo.value) < 0) {
                mostrarMensaje("error", "El stock mínimo debe ser un número mayor o igual a 0.");
                event.preventDefault(); return;
            }

            if (parseInt(stockMinimo.value) > parseInt(stock.value)) {
                mostrarMensaje("error", "El stock mínimo no puede ser mayor que el stock actual.");
                event.preventDefault(); return;
            }

            const textoConcatenado = (nombre.value + " " + unidad.value + " " + categoria.value).toUpperCase();
            for (let palabra of palabrasBloqueadas) {
                if (textoConcatenado.includes(palabra)) {
                    mostrarMensaje("error", "Entrada no permitida: posible instrucción SQL detectada.");
                    event.preventDefault(); return;
                }
            }

            mostrarMensaje("exito", "Validación exitosa, enviando datos...");
        });
    }

    /* ======= VALIDACIÓN DEL FORMULARIO DE RETIROS ======= */
    if (form && document.getElementById("razon")) {
        form.addEventListener("submit", function(event) {
            const producto = document.getElementById("id_producto");
            const cantidad = document.getElementById("cantidad");
            const razon = document.getElementById("razon");

            if (!producto.value) {
                mostrarMensaje("error", "Debe seleccionar un producto.");
                event.preventDefault(); return;
            }

            if (!cantidad.value || parseInt(cantidad.value) <= 0) {
                mostrarMensaje("error", "La cantidad debe ser mayor que 0.");
                event.preventDefault(); return;
            }

            if (!razon.value.trim()) {
                mostrarMensaje("error", "Debe indicar la razón del retiro.");
                event.preventDefault(); return;
            }

            if (razon.value.length < 3) {
                mostrarMensaje("error", "La razón del retiro es demasiado corta.");
                event.preventDefault(); return;
            }
        });
    }

    // Guardar la página actual como "lastPage" cuando el usuario hace clic en un enlace de registrar
    const botonesRegistrar = document.querySelectorAll('.btn-registrar');
    if (botonesRegistrar) {
        botonesRegistrar.forEach(btn => {
            btn.addEventListener('click', (e) => {
                try { sessionStorage.setItem('lastPage', window.location.href); } catch (err) { /* ignore storage errors */ }
            });
        });
    }
});

/* =================================================
   MÓDULO DE AUTOCOMPLETADO PARA REGISTRAR RETIRO
   ================================================= */


/* =====================================================
   AUTOCOMPLETADO DE PRODUCTOS EN REGISTRAR RETIRO
   ===================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const inputProducto = document.getElementById("nombre_producto");
    const listaSugerencias = document.getElementById("lista-sugerencias");
    const inputIdProducto = document.getElementById("id_producto");

    // Si no existe el campo (no estamos en registrar_retiro.php), no ejecutar
    if (!inputProducto || !listaSugerencias) return;

    inputProducto.addEventListener("input", () => {
        const termino = inputProducto.value.trim();
        listaSugerencias.innerHTML = "";
        inputIdProducto.value = "";

        if (termino.length < 2) return; // espera mínimo 2 letras

        fetch(`buscar_producto.php?term=${encodeURIComponent(termino)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    listaSugerencias.innerHTML = "<li class='no-encontrado'>Sin resultados</li>";
                    return;
                }

                data.forEach(item => {
                    const li = document.createElement("li");
                    li.textContent = `${item.nombre} (${item.categoria} - ${item.tipo})`;
                    li.dataset.id = item.id;
                    li.dataset.tipo = item.tipo;

                    li.addEventListener("click", () => {
                        inputProducto.value = item.nombre;
                        inputIdProducto.value = item.id;
                        document.getElementById("tipo").value = item.tipo;
                        listaSugerencias.innerHTML = "";
                    });

                    listaSugerencias.appendChild(li);
                });
            })
            .catch(err => console.error("Error en autocompletado:", err));
    });

    // Cerrar la lista al hacer clic fuera
    document.addEventListener("click", (e) => {
        if (!e.target.closest("#nombre_producto")) {
            listaSugerencias.innerHTML = "";
        }
    });
});

/* =====================================================
   🔸 AUTOCOMPLETADO DE PRODUCTOS SEGÚN CATEGORÍA ELEGIDA
   ===================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const tipo = document.getElementById("tipo");
    const inputProducto = document.getElementById("nombre_producto");
    const listaSugerencias = document.getElementById("lista-sugerencias");
    const inputIdProducto = document.getElementById("id_producto");
    const inputStock = document.getElementById("stock_disponible");

    if (!tipo || !inputProducto || !listaSugerencias) return;

    // 1️⃣ Desbloquear campo producto solo cuando el tipo se selecciona
    tipo.addEventListener("change", () => {
        if (tipo.value === "") {
            inputProducto.disabled = true;
            inputProducto.value = "";
            listaSugerencias.innerHTML = "";
            inputStock.value = "Seleccione un tipo de inventario";
        } else {
            inputProducto.disabled = false;
            inputProducto.focus();
            inputStock.value = "Escriba para buscar un producto...";
        }
    });

    // 2️⃣ Autocompletado AJAX
    inputProducto.addEventListener("input", () => {
        const termino = inputProducto.value.trim();
        listaSugerencias.innerHTML = "";
        inputIdProducto.value = "";
        inputStock.value = "Cargando...";

        if (termino.length < 2 || tipo.value === "") return;

        fetch(`buscar_producto.php?term=${encodeURIComponent(termino)}&tipo=${encodeURIComponent(tipo.value)}`)
            .then(res => res.json())
            .then(data => {
                listaSugerencias.innerHTML = "";

                if (data.length === 0) {
                    listaSugerencias.innerHTML = "<li class='no-encontrado'>Sin resultados</li>";
                    inputStock.value = "No se encontraron coincidencias";
                    return;
                }

                data.forEach(item => {
                    const li = document.createElement("li");
                    li.textContent = `${item.nombre} (${item.categoria})`;
                    li.dataset.id = item.id;
                    li.dataset.stock = item.stock;
                    li.dataset.minimo = item.stock_minimo;

                    li.addEventListener("click", () => {
                        inputProducto.value = item.nombre;
                        inputIdProducto.value = item.id;
                        listaSugerencias.innerHTML = "";
                        inputStock.value = `${item.stock} unidades (mínimo ${item.stock_minimo})`;
                        inputStock.style.color =
                            parseInt(item.stock) <= parseInt(item.stock_minimo)
                                ? "#d9534f" // rojo si bajo
                                : "#28a745"; // verde si suficiente
                    });

                    listaSugerencias.appendChild(li);
                });
            })
            .catch(err => {
                console.error("Error en autocompletado:", err);
                inputStock.value = "Error al cargar datos";
            });
    });

    // 3️⃣ Cerrar lista al hacer clic fuera
    document.addEventListener("click", (e) => {
        if (!e.target.closest("#nombre_producto")) {
            listaSugerencias.innerHTML = "";
        }
    });

    /* =====================================================
       🔸 REACTIVAR FORMULARIO DE RETIRO TRAS REGISTRO
       ===================================================== */
    const formRetiroElement = document.getElementById("form-retiro");
    const productoInput = document.getElementById("nombre_producto");
    const productoId = document.getElementById("id_producto");
    const tipoInput = document.getElementById("tipo");
    const cantidadInput = document.getElementById("cantidad");
    const razonInput = document.getElementById("razon");
    const stockInput = document.getElementById("stock_disponible");

    if (!formRetiroElement) return;

    // Detecta si hay mensaje de éxito
    const mensajeExito = document.querySelector(".mensaje-form.exito");
    if (mensajeExito) {
        // Limpiar el formulario visualmente luego de unos segundos
        setTimeout(() => {
            productoInput.value = "";
            productoId.value = "";
            tipoInput.value = "";
            cantidadInput.value = "";
            razonInput.value = "";
            stockInput.value = "Seleccione un producto";
            stockInput.style.color = "#000";
            productoInput.disabled = true; // se reactiva cuando el usuario elija tipo de nuevo
            mensajeExito.style.opacity = "0";
            setTimeout(() => mensajeExito.remove(), 800); // quita el mensaje
        }, 1500); // 1.5 segundos de pausa para que el usuario vea el mensaje
    }
});
