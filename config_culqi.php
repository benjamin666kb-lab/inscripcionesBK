<?php

$path = __DIR__ . '/elsecret/.env';

if (!file_exists($path)) {
    error_log("No se encontró archivo .env");
    http_response_code(500);
    exit("Error interno del servidor.");
}

$env = parse_ini_file($path);

if ($env === false) {
    error_log("Error leyendo archivo .env");
    http_response_code(500);
    exit("Error interno del servidor.");
}

define('CULQI_PUBLIC_KEY', $env['CULQI_PUBLIC_KEY'] ?? '');
define('CULQI_SECRET_KEY', $env['CULQI_SECRET_KEY'] ?? '');