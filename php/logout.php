<?php
session_start();
session_unset(); // Eliminar todas las variables de sesión
session_destroy(); // Destruir la sesión

// Redirigir a la página principal
header('Location: ../index.php');
exit;
?>
