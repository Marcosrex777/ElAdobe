<?php
include '../conectar_bd.php';

// Si el archivo de conexión no crea $conexion, lo generamos aquí
if (!isset($conexion) || !$conexion) {
    if (isset($host, $user, $password, $database)) {
        $conexion = mysqli_connect($host, $user, $password, $database);
        if (!$conexion) {
            http_response_code(500);
            echo json_encode(["error" => "Error de conexión: " . mysqli_connect_error()]);
            exit;
        }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Configuración de conexión no encontrada."]);
        exit;
    }
}

// Asegurar codificación
mysqli_set_charset($conexion, "utf8mb4");

// Leer parámetro id y validarlo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "ID de proveedor inválido o no especificado."]);
    mysqli_close($conexion);
    exit;
}

// Consulta SQL normal
$sql = "SELECT * FROM proveedores WHERE id = $id";

// Ejecutar consulta
$result = mysqli_query($conexion, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la consulta: " . mysqli_error($conexion)]);
    mysqli_close($conexion);
    exit;
}

// Obtener fila
$fila = mysqli_fetch_assoc($result);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($fila ?: []);

mysqli_free_result($result);
mysqli_close($conexion);
?>
