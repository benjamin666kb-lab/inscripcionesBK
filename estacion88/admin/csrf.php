<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   GENERAR TOKEN CSRF
========================= */
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   FUNCIÓN VALIDAR CSRF
========================= */
function validar_csrf($token_post) {
    if (!isset($token_post) || !hash_equals($_SESSION['csrf_token'], $token_post)) {
        die("Solicitud inválida (CSRF)");
    }
}