<?php
session_start();

// Proteger acceso
if (!isset($_SESSION['usuario'])) {
    header("Location: loginEmpleados.php");
    exit();
}

$rol = $_SESSION['nombre_rol']; 
?>





<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Adobe</title>
    <link rel="stylesheet" href="./menu_modulos.css">

</head>
<body>
  
        <header>
 
  <div class="company-name">El Adobe</div> 

 
  <div class="hamburger" id="hamburger">
    <div></div>
    <div></div>
    <div></div>
  </div>

  <a href="logout.php" class="cerrarSesion">Cerrar sesión</a>

 
</header>




    <aside class="sidebar" id="sidebar">
        <ul>
            <li id="menu1" data-url="inicio.html">Inicio</li>


            
           <?php if ($rol === 'Administrador' || $rol === 'Cajero' || $rol === 'Contador' || $rol === 'Editor'): ?>
        <li id="menu2">Compras
            <ul class="submenu">
                <li><a href="./modulo_compras/compras.php">Manejo de Compras</a></li>
                
            </ul>
        </li>
    <?php endif; ?>



<?php if ($rol === 'Administrador' || $rol === 'Contador' || $rol === 'Cajero' || $rol === 'Editor'): ?>

            <li id="menu3">Ventas
                <ul class="submenu" id="submenu3">
                    
                      
                    <li><a href="modulo_ventas/vista/venta.php">Mesero</a></li>
                    <li><a href="modulo_ventas/vista/cocina.php">Cocina</a></li>
                </ul>
            </li>

            <?php endif; ?>


            <?php if ($rol === 'Administrador' || $rol === 'Cajero' || $rol === 'Editor'): ?>
            <li id="menu4">Proveedores
                <ul class="submenu" id="submenu4">
                    <li><a href="./modulo_proveedores/index.php">Manejo de Proveedores</a></li>
                    
                </ul>
            </li>

            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Meseros' || $rol === ''): ?>
            <li id="menu5">Mesas
                <ul class="submenu" id="submenu5">
                    <li data-url="servicio1.html">Servicio 1</li>
                    <li data-url="servicio2.html">Servicio 2</li>
                    <li data-url="servicio3.html">Servicio 3</li>
                </ul>
            </li>

            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Meseros' || $rol === 'Editor'): ?>
            <li id="menu6">Reservaciones
                <ul class="submenu" id="submenu6">
                    <li data-url="servicio1.html">Servicio 1</li>
                    <li data-url="servicio2.html">Servicio 2</li>
                    <li data-url="servicio3.html">Servicio 3</li>
                </ul>
            </li>

            <?php endif; ?>



             <?php if ($rol === 'Administrador' || $rol === 'Encargado de Almacén' || $rol === 'Editor'): ?>
            <li id="menu6">Inventario
                <ul class="submenu" id="submenu7">
                    <li><a href="./modulo_inventario/listar_comestible.php">Comestibles</a></li>
                    <li><a href="./modulo_inventario/listar_mobiliario.php">Mobiliario</a></li>
                    <li><a href="./modulo_inventario/retiros/listar_retiros.php">Retiros de Productos</a></li>
                </ul>
            </li>

            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Encargado de Almacén' || $rol === 'Editor'): ?>
            <li id="menu6">Consulta Inteligente
                <ul class="submenu" id="submenu7">
                    <li><a href="bi_prueba.php">Consulta</a></li>
                </ul>
            </li>

            <?php endif; ?>



 <?php if ($rol === 'Administrador' || $rol === 'Gerente' || $rol === 'Editor'): ?>
            <li id="menu7">Empleados
                <ul class="submenu" id="submenu7">
                    <li data-url="CreacionUsuarioEmpleados.php" href="./CreacionUsuarioEmpleados.php">Creacion de usuarios</li>
                    <li data-url="RecuperacionContraseñaEmpleados.php">Recuperacion de Contraseña</li>
                    <li data-url="ConsultasEmpleados.php">Consultas</li>
                </ul>
            </li>
                 <?php endif; ?>      
                



        </ul>
    </aside>

   <main id="contenido-principal">
    <h1>El Adobe</h1>
    <p>“Servimos con pasión, crecemos con excelencia.”</p>
</main>


    <script src="menu_modulos.js"></script>
</body>
</html>