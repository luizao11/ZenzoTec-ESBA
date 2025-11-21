<?php
// Iniciar sesión
session_start();

// 1. Destruir sesión en el servidor
session_unset();
session_destroy();

// 2. Eliminar cookie de sesión del cliente
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// 3. Headers para evitar caché
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 4. Redirigir a una página que limpie el frontend
header("Location: logout-cleanup.html");
exit;
?>