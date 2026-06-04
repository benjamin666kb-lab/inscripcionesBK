<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tiempo_limite = 300; // 5 minutos = 300 segundos

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