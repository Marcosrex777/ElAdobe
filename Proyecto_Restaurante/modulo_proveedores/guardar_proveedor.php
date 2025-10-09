<?php
// guardar_proveedor.php
include_once("../conectar_bd.php"); // Asegúrate de tener este archivo con la conexión a la BD

// Inicializamos mensaje
$mensaje = "";
$claseMensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre    = trim($_POST['nombre']);
    $contacto  = trim($_POST['contacto']);
    $telefono  = trim($_POST['telefono']);
    $email     = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);
    $categoria = trim($_POST['categoria']);

    // Validación mínima
    if (!empty($nombre) && !empty($contacto) && !empty($telefono)) {
        $sql = "INSERT INTO proveedores (nombre, contacto, telefono, email, direccion, categoria) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssss", $nombre, $contacto, $telefono, $email, $direccion, $categoria);

            if ($stmt->execute()) {
                $mensaje = "Proveedor guardado con éxito.";
                $claseMensaje = "success-message";
            } else {
                $mensaje = "Error al guardar el proveedor: " . $stmt->error;
                $claseMensaje = "error-message";
            }

            $stmt->close();
        } else {
            $mensaje = "Error en la preparación de la consulta: " . $conn->error;
            $claseMensaje = "error-message";
        }
    } else {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $claseMensaje = "error-message";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guardar Proveedor</title>
    <link rel="stylesheet" href="proveedores.css">
</head>
<body>

    <div class="form-container">
        <h2>Resultado</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo $claseMensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <a href="proveedores.php">
            <button>Volver al formulario</button>
        </a>
    </div>

</body>
</html>
