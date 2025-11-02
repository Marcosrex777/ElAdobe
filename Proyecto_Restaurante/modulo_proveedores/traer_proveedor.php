<?php
include '../conectar_bd.php';

// Si el archivo de conexión no crea $conexion, la construimos aquí
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

// Forzar UTF-8
mysqli_set_charset($conexion, "utf8mb4");

// Consulta SQL normal
$sql = "SELECT id, nombre_proveedor, persona_contacto, telefono, correo, direccion, categoria, estado 
        FROM proveedores";

// Ejecutar consulta
$result = mysqli_query($conexion, $sql);

// Inicializar arreglo de resultados
$proveedores = [];

if ($result) {
    while ($fila = mysqli_fetch_assoc($result)) {
        $proveedores[] = $fila;
    }
    mysqli_free_result($result);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error en la consulta: " . mysqli_error($conexion)]);
    mysqli_close($conexion);
    exit;
}

// Enviar datos en formato JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($proveedores, JSON_UNESCAPED_UNICODE);

// Cerrar conexión
mysqli_close($conexion);
?>
