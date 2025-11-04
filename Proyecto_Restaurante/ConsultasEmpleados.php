<?php
session_start();
require_once "conectar_bd.php"; // conexión

// Variables de filtro
$estado = $_POST['estado'] ?? '';
$identificador = $_POST['identificador'] ?? '';
$id_rol = $_POST['id_rol'] ?? '';

$resultados = [];

// Obtener listas dinámicas de identificadores y roles
$lista_identificadores = [];
$lista_roles = [];

// Cargar identificadores distintos de la tabla Usuarios
$query_ident = "SELECT DISTINCT identificador FROM usuarios ORDER BY identificador";
$result_ident = $conn->query($query_ident);
if ($result_ident) {
    while ($row = $result_ident->fetch_assoc()) {
        $lista_identificadores[] = $row['identificador'];
    }
}

// Cargar roles desde tabla Roles (si existe)
$query_roles = "SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol";
$result_roles = $conn->query($query_roles);
if ($result_roles) {
    while ($row = $result_roles->fetch_assoc()) {
        $lista_roles[] = $row;
    }
}

// Procesar búsqueda
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sql = "SELECT id_usuario, nombre_usuario, nombre_completo, estado, identificador, id_rol, correo, fecha_nacimiento, telefono
            FROM usuarios WHERE 1=1";

    $params = [];
    $types = '';

    if (!empty($estado)) {
        $sql .= " AND estado = ?";
        $types .= "s";
        $params[] = $estado;
    }
   if ($identificador !== '') {  
    $sql .= " AND identificador = ?";
    $types .= "i";
    $params[] = $identificador;
}

    if (!empty($id_rol)) {
        $sql .= " AND id_rol = ?";
        $types .= "i";
        $params[] = $id_rol;
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: auto;
        }

        .filtros {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        select {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            display: block;
            margin: 20px auto;
            padding: 10px 25px;
            background-color: #8C6A5A;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #6d5043;
        }

        table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: center;
        }

        th {
            background-color: #8C6A5A;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Consulta de Usuarios</h2>

<form method="POST">
    <div class="filtros">
        <!-- Campo Estado -->
        <select name="estado">
            <option value="">-- Estado --</option>
            <option value="Activo" <?= $estado == 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $estado == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>

        <!-- Campo Identificador -->
        <select name="identificador">
            <option value="">-- Identificador --</option>
            <?php foreach ($lista_identificadores as $id): ?>
                <option value="<?= $id ?>" <?= $identificador == $id ? 'selected' : '' ?>>
                    <?= $id ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Campo Rol -->
        <select name="id_rol">
            <option value="">-- Rol --</option>
            <?php foreach ($lista_roles as $rol): ?>
                <option value="<?= $rol['id_rol'] ?>" <?= $id_rol == $rol['id_rol'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($rol['nombre_rol']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Botones -->
    <div style="text-align:center; margin-top:15px;">
        <button type="submit">Cargar</button>
        <!-- Botón Limpiar filtros -->
        <button type="button" onclick="window.location='<?= $_SERVER['PHP_SELF'] ?>'">Limpiar filtros</button>
    </div>
</form>




<?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <table>
        <thead>
            <tr>
                <th>ID Usuario</th>
                <th>Nombre Usuario</th>
                <th>Nombre Completo</th>
                <th>Estado</th>
                <th>Identificador</th>
                <th>ID Rol</th>
                <th>Correo</th>
                <th>Fecha Nacimiento</th>
                <th>Teléfono</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($resultados)): ?>
            <?php foreach ($resultados as $fila): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id_usuario']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($fila['estado']) ?></td>
                    <td><?= htmlspecialchars($fila['identificador']) ?></td>
                    <td><?= htmlspecialchars($fila['id_rol']) ?></td>
                    <td><?= htmlspecialchars($fila['correo']) ?></td>
                    <td><?= htmlspecialchars($fila['fecha_nacimiento']) ?></td>
                    <td><?= htmlspecialchars($fila['telefono']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9">No se encontraron resultados</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
