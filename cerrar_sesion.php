
<?php
session_start();

// Eliminar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Regresar al acceso
header("Location: acceso.php");
exit;
?>
