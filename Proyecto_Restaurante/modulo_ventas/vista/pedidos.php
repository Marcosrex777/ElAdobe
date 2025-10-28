<?php
require_once("../Controlador/MenuControlador.php");
require_once("../Controlador/PedidoControlador.php");
require_once("../Modelo/MesaDAO.php");

$menuControlador = new MenuControlador();
$pedidoControlador = new PedidoControlador();
$mesaDAO = new MesaDAO();

$mesas = $mesaDAO->listarMesas();
$id_usuario = 2; // Ejemplo: mesero con ID 2
?>

<h2>Gestión de Pedidos</h2>

<form method="GET">
    <label>Mesa:</label>
    <select name="mesa" onchange="this.form.submit()">
        <option value="">Seleccione una mesa</option>
        <?php foreach ($mesas as $m): ?>
            <option value="<?= $m['id_mesa'] ?>" <?= isset($_GET['mesa']) && $_GET['mesa'] == $m['id_mesa'] ? 'selected' : '' ?>>
                Mesa <?= $m['numero'] ?> (<?= $m['estado'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (isset($_GET['mesa'])): 
    $id_mesa = $_GET['mesa']; ?>

    <div class="pedido-actual">
        <?= $pedidoControlador->mostrarPedidoMesa($id_mesa, $id_usuario) ?>
    </div>

    <h3>Menú disponible</h3>
    <?= $menuControlador->obtenerMenu() ?>

    <button onclick="enviarPedido(<?= $id_mesa ?>)">Enviar Pedido</button>

    <script>
    // Ejemplo de agregar platillos (puedes mejorar con fetch/AJAX)
    document.querySelectorAll('.agregar-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id_menu = btn.dataset.id;
            const cantidad = prompt("Ingrese cantidad:", 1);
            if (cantidad > 0) {
                window.location.href = `pedidos.php?mesa=<?= $id_mesa ?>&accion=agregar&id_menu=${id_menu}&cantidad=${cantidad}`;
            }
        });
    });
    </script>
<?php endif; ?>
