<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// 🚫 Evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

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