
<?php

// Cargar las dependencias de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Cargar las variables del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Obtener las variables de entorno
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$base_datos = $_ENV['DB_NAME'] ?? 'raiz_local';
$usuario = $_ENV['DB_USER'] ?? 'root';
$contrasena = $_ENV['DB_PASSWORD'] ?? '';

try {

    // Establecer conexión con MySQL
    $conexion = new PDO(
        "mysql:host=$host;dbname=$base_datos;charset=utf8mb4",
        $usuario,
        $contrasena
    );

    // Configurar el manejo de errores
    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Configurar el formato de los resultados
    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    // Registrar el error sin mostrar información sensible
    error_log($e->getMessage());

    die("Error de conexión con la base de datos.");

}

?>
