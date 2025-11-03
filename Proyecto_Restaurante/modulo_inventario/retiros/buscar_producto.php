<?php
include __DIR__ . '/../../conectar_bd.php';

$term = $_GET['term'] ?? '';
$tipo = $_GET['tipo'] ?? ''; // nuevo parámetro

if ($term === '' || $tipo === '') {
    echo json_encode([]);
    exit;
}

if ($tipo === 'comestible') {
    $sql = "
    SELECT p.id_producto, p.nombre, c.categoria, 'comestible' AS tipo,
           c.stock, c.stock_minimo
    FROM productos p
    JOIN comestibles c ON p.id_producto = c.id_producto
    WHERE p.nombre LIKE ? OR c.categoria LIKE ?
    ORDER BY p.nombre ASC
    LIMIT 10
    ";
} elseif ($tipo === 'mobiliario') {
    $sql = "
    SELECT p.id_producto, p.nombre, m.categoria, 'mobiliario' AS tipo,
           m.stock, m.stock_minimo
    FROM productos p
    JOIN mobiliario_equipo m ON p.id_producto = m.id_producto
    WHERE p.nombre LIKE ? OR m.categoria LIKE ?
    ORDER BY p.nombre ASC
    LIMIT 10
    ";
} else {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare($sql);
$like = '%' . $term . '%';
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = [
        'id' => $row['id_producto'],
        'nombre' => $row['nombre'],
        'categoria' => $row['categoria'],
        'tipo' => $row['tipo'],
        'stock' => $row['stock'],
        'stock_minimo' => $row['stock_minimo']
    ];
}

echo json_encode($productos);
