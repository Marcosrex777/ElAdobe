<?php
require_once("../Controlador/MenuControlador.php");
require_once("../Controlador/PedidoControlador.php");
require_once("../Modelo/MesaDAO.php");

$menuControlador = new MenuControlador();
$pedidoControlador = new PedidoControlador();
$mesaDAO = new MesaDAO();

$mesas = $mesaDAO->listarMesas();
$id_usuario = 2; // ejemplo: ID del mesero logueado

$id_mesa = isset($_GET['mesa']) ? intval($_GET['mesa']) : null;

// Cerrar cuenta (generar venta)
if (isset($_GET['accion']) && $_GET['accion'] === 'cerrar' && $id_mesa) {
    $pedidoControlador->cerrarCuenta($id_mesa, $id_usuario);
}
// Agregar platillo
if (isset($_GET['accion']) && $_GET['accion'] === 'agregar' && $id_mesa) {
    $id_menu = intval($_GET['id_menu']);
    $cantidad = intval($_GET['cantidad']);
    $precio = floatval($_GET['precio']);
    $pedidoControlador->agregarPlatillo($id_mesa, $id_usuario, $id_menu, $cantidad, $precio);
}
// Enviar pedido
if (isset($_GET['accion']) && $_GET['accion'] === 'enviar' && $id_mesa) {
    $pedidoControlador->enviarPedido($id_mesa);
}
if ($id_mesa) {
    echo "
        <form method='GET'>
            <input type='hidden' name='mesa' value='{$id_mesa}'>
            <input type='hidden' name='accion' value='cerrar'>
            <button id='btn-cerrar' type='submit' style='background-color:#8b5e3c; color:white; margin-top:5px; border:none; border-radius:8px; padding:10px; width:100%;'>
                💰 Cerrar cuenta
            </button>
        </form>
    ";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos - Restaurante El Adobe</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <h1>Restaurante El Adobe</h1>
    <nav>
        <a href="#">Inicio</a>
        <a href="#">Pedidos</a>
        <a href="#">Salir</a>
    </nav>
</header>

<main>
    <!-- Sección Mesas -->
    <section>
        <h2>Mesas</h2>
        <div id="contenedor-mesas">
            <?php foreach ($mesas as $m): ?>
                <form method="GET" style="display:inline;">
                    <input type="hidden" name="mesa" value="<?= $m['id_mesa'] ?>">
                    <button 
                        type="submit" 
                        class="<?= ($m['estado'] === 'ocupada') ? 'ocupada' : '' ?>"
                        <?= ($m['estado'] === 'ocupada') ? '' : '' ?>>
                        Mesa <?= $m['numero'] ?><br>(<?= $m['estado'] ?>)
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Sección Pedido -->
    <section>
        <h2>Pedido actual</h2>
        <div id="pedido">
            <?php 
            if ($id_mesa) {
                echo $pedidoControlador->mostrarPedidoMesa($id_mesa, $id_usuario);
                echo "
                    <form method='GET'>
                        <input type='hidden' name='mesa' value='{$id_mesa}'>
                        <input type='hidden' name='accion' value='enviar'>
                        <button id='btn-enviar' type='submit'>Enviar Pedido</button>
                    </form>
                ";
            } else {
                echo "<p>Selecciona una mesa para ver su pedido.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Sección Menú -->
    <section>
        <h2>Menú</h2>
        <div id="menu">
            <?php
            echo $menuControlador->obtenerMenu();
            ?>
        </div>
    </section>
</main>

<footer>

</footer>

<script>
document.querySelectorAll('.agregar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id_menu = btn.dataset.id;
        const precio = btn.dataset.precio;
        const cantidad = prompt("Ingrese cantidad:", 1);
        if (cantidad > 0 && <?= $id_mesa ?: '0' ?>) {
            window.location.href = `index.php?mesa=<?= $id_mesa ?>&accion=agregar&id_menu=${id_menu}&cantidad=${cantidad}&precio=${precio}`;
        } else if (!<?= $id_mesa ?: '0' ?>) {
            alert("Por favor, selecciona una mesa antes de agregar un platillo.");
        }
    });
});
</script>

</body>
</html>
