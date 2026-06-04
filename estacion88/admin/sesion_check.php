<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🚫 Evitar que el navegador guarde páginas protegidas
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$tiempo_limite = 600; // 10 minutos

// si no existe tiempo de actividad
if (!isset($_SESSION['ultima_actividad'])) {
    $_SESSION['ultima_actividad'] = time();
}

// tiempo transcurrido
$inactividad = time() - $_SESSION['ultima_actividad'];

if ($inactividad > $tiempo_limite) {

    // destruir sesión
    session_unset();
    session_destroy();

    header("Location: login.php?expirado=1");
    exit;
}

// actualizar tiempo cada vez que navega
$_SESSION['ultima_actividad'] = time();
?>