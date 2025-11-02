<?php
// guardar_proveedor.php
// Usa include para traer la configuración/variables de conexión.
// Asegúrate que ../conectar_bd.php define al menos $host, $user, $password, $database
include '../conectar_bd.php';

// Si conectar_bd.php ya crea $conexion (mysqli), lo respetamos.
// Si sólo define las variables, creamos la conexión aquí.
if (!isset($conexion) || !$conexion) {
    if (isset($host, $user, $password, $database)) {
        $conexion = mysqli_connect($host, $user, $password, $database);
        if (!$conexion) {
            http_response_code(500);
            echo "Error de conexión: " . mysqli_connect_error();
            exit;
        }
    } else {
        http_response_code(500);
        echo "No hay configuración de conexión disponible.";
        exit;
    }
}

// Asegurar uso de UTF-8
mysqli_set_charset($conexion, "utf8mb4");

// Leer y sanear datos (usando mysqli_real_escape_string para SQL "normal")
$id_raw       = $_POST['id'] ?? null;
$id           = ($id_raw !== null && $id_raw !== '') ? intval($id_raw) : null;
$nombre       = mysqli_real_escape_string($conexion, trim($_POST['nombre_proveedor'] ?? ''));
$contacto     = mysqli_real_escape_string($conexion, trim($_POST['persona_contacto'] ?? ''));
$telefono     = mysqli_real_escape_string($conexion, trim($_POST['telefono'] ?? ''));
$correo       = mysqli_real_escape_string($conexion, trim($_POST['correo'] ?? ''));
$direccion    = mysqli_real_escape_string($conexion, trim($_POST['direccion'] ?? ''));
$categoria    = mysqli_real_escape_string($conexion, trim($_POST['categoria'] ?? ''));
$estado       = mysqli_real_escape_string($conexion, trim($_POST['estado'] ?? ''));

// Validaciones mínimas (opcional, ajusta según tu lógica)
if ($nombre === '') {
    http_response_code(400);
    echo "El nombre del proveedor es obligatorio.";
    mysqli_close($conexion);
    exit;
}

// Construir y ejecutar SQL según si hay id (update) o no (insert)
if ($id) {
    // Actualizar proveedor existente
    $sql = "UPDATE proveedores SET
                nombre_proveedor = '$nombre',
                persona_contacto = '$contacto',
                telefono = '$telefono',
                correo = '$correo',
                direccion = '$direccion',
                categoria = '$categoria',
                estado = '$estado'
            WHERE id = $id";

    if (mysqli_query($conexion, $sql)) {
        echo "Proveedor actualizado correctamente.";
    } else {
        http_response_code(500);
        echo "Error al actualizar el proveedor: " . mysqli_error($conexion);
    }

} else {
    // Insertar nuevo proveedor
    $sql = "INSERT INTO proveedores
            (nombre_proveedor, persona_contacto, telefono, correo, direccion, categoria, estado)
            VALUES
            ('$nombre', '$contacto', '$telefono', '$correo', '$direccion', '$categoria', '$estado')";

    if (mysqli_query($conexion, $sql)) {
        // opcional: obtener id insertado
        $new_id = mysqli_insert_id($conexion);
        echo "Proveedor guardado correctamente. ID: " . $new_id;
    } else {
        http_response_code(500);
        echo "Error al guardar el proveedor: " . mysqli_error($conexion);
    }
}

// Cerrar statement si lo hubiera y cerrar conexión
// (en esta versión no usamos $stmt)
mysqli_close($conexion);
?>
