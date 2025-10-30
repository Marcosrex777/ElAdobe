<?php
// Página de selección de inventario (sin logo ni emojis)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Inventario - El Adobe</title>
    <link rel="stylesheet" href="css/inventario.css">
</head>
<body>

    <div class="contenedor">
        <header>
            <h1>El Adobe - Módulo de Inventario</h1>
        </header>

        <main>
            <h2>Seleccione el inventario con el que desea trabajar:</h2>

            <div class="opciones">
                    <a href="inventario/comestibles/listar.php" class="btn btn-comestibles">
                        Inventario de Comestibles
                    </a>

                    <a href="inventario/mobiliario_equipo/listar.php" class="btn btn-mobiliario">
                        Inventario de Mobiliario y Equipo
                    </a>
            </div>
        </main>

        <footer>
            <p>© 2025 El Adobe | Sistema de Inventario</p>
        </footer>
    </div>

</body>
</html>
