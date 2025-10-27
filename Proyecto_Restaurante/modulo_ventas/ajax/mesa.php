<?php
require_once("../controlador/MesaControlador.php");

$controlador = new MesaControlador();
echo $controlador->obtenerMesas();
?>
