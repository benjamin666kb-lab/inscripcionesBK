<?php

// 🚫 Evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$tiempo_limite = 1200;

// Si no existe sesión de actividad
if (!isset($_SESSION['ultima_actividad'])) {
    $_SESSION['ultima_actividad'] = time();
}

// Expiración por inactividad
if ((time() - $_SESSION['ultima_actividad']) > $tiempo_limite) {
    session_unset();
    session_destroy();

    $mensaje = urlencode("Su sesión fue cerrada por inactividad. Han transcurrido más de 10 minutos sin actividad.");
    header("Location: login?mensaje=$mensaje");
    exit;
}

// Actualizar actividad
$_SESSION['ultima_actividad'] = time();

/* =========================
   CSRF TOKEN GLOBAL ADMIN
========================= */
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}