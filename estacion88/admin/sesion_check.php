<?php

// 🚫 Evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$tiempo_limite = 600;

// Si no existe tiempo de actividad
if (!isset($_SESSION['ultima_actividad'])) {
    $_SESSION['ultima_actividad'] = time();
}

if ((time() - $_SESSION['ultima_actividad']) > $tiempo_limite) {
    session_unset();
    session_destroy();

    $mensaje = urlencode("Su sesión fue cerrada por inactividad. Han transcurrido más de 10 minutos sin actividad.");
    header("Location: login.php?mensaje=$mensaje");
    exit;
}

$_SESSION['ultima_actividad'] = time();