<?php

$path = __DIR__ . '/elsecret/.env';

if (!file_exists($path)) {
    die("No se encontró .env en: " . $path);
}

$env = parse_ini_file($path);

if (!$env) {
    die("Error leyendo .env (revisa formato)");
}

define('CULQI_PUBLIC_KEY', $env['CULQI_PUBLIC_KEY'] ?? '');
define('CULQI_SECRET_KEY', $env['CULQI_SECRET_KEY'] ?? '');
