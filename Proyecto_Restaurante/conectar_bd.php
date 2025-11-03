<?php
<<<<<<< Updated upstream
// Conexión a la base de datos (Laragon)
// Nota: en Laragon el servidor MySQL/MariaDB suele correr en TCP 127.0.0.1 y, dependiendo
// de la configuración, puede usar el puerto 3306 o 3307. Aquí usamos 3307 porque el proyecto
// lo esperaba. Ajusta el puerto/credenciales si tu Laragon tiene otra configuración.
$host = '127.0.0.1';     // usar 127.0.0.1 fuerza TCP (evita problemas con sockets)
$port = 3307;            // Puerto (cambia a 3306 si tu Laragon usa ese puerto)
$user = 'root';          // Usuario por defecto
$password = '';          // En Laragon por defecto root NO tiene contraseña
$database = 'eladobe';   // Nombre de la base de datos
=======
$host = "localhost";     // Servidor local en Laragon
$user = "root";          // Usuario por defecto en MySQL
$password = "luiscarlos2004";          // En Laragon, root NO tiene contraseña
$database = "eladobe"; // Nombre de la base que creaste en Workbench
>>>>>>> Stashed changes

// Reportar errores de mysqli como excepciones para manejar mejor fallos
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Crear conexión usando puerto explícito
    $conn = new mysqli($host, $user, $password, $database, $port);
    // Establecer charset
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Mensaje claro para desarrollo. En producción evita mostrar detalles sensibles.
    http_response_code(500);
    die('Error en la conexión a la base de datos. Verifica host, puerto y credenciales. Detalle: ' . $e->getMessage());
}
?>
