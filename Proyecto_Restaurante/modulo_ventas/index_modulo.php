
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Ventas - Restaurante</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="header-content">
            <div class="header-logo">
                <span class="logo-text">El Adobe</span>
            </div>
        </div>
    </header>

    <main>
        <!-- Ventas -->
        <section id="ventas">
            <h2>Módulo de Ventas</h2>

            <!-- Selección de mesa -->
            <div class="mesa-selector">
                <label for="mesaSelect">Selecciona una mesa:</label>
                <select id="mesaSelect">
                    <option value="">-- Selecciona --</option>
                    <option value="1">Mesa 1</option>
                    <option value="2">Mesa 2</option>
                    <option value="3">Mesa 3</option>
                    <option value="4">Mesa 4</option>
                </select>
            </div>

            <!-- Menú disponible -->
            <div class="menu-category">
                <h3>Menú</h3>
                <div class="menu-list" id="menuList">
                    <div class="menu-item">
                        <h4>Pizza Margarita</h4>
                        <p>Clásica con queso y albahaca fresca.</p>
                        <span>$8</span>
                        <button class="btn-add" data-name="Pizza Margarita" data-price="8">Agregar</button>
                    </div>
                    <div class="menu-item">
                        <h4>Hamburguesa Especial</h4>
                        <p>Con queso, bacon y salsa de la casa.</p>
                        <span>$6</span>
                        <button class="btn-add" data-name="Hamburguesa Especial" data-price="6">Agregar</button>
                    </div>
                    <div class="menu-item">
                        <h4>Pasta Alfredo</h4>
                        <p>Con crema, pollo y parmesano.</p>
                        <span>$7</span>
                        <button class="btn-add" data-name="Pasta Alfredo" data-price="7">Agregar</button>
                    </div>
                </div>
            </div>

            <!-- Pedido actual -->
            <div class="pedido-section">
                <h3>Pedido de Mesa <span id="mesaTitle">-</span></h3>
                <table class="pedido-tabla">
                    <thead>
                        <tr>
                            <th>Platillo</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="pedidoBody"></tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="../script.js"></script>
</body>
</html>
