<?php

$servername = "sql100.infinityfree.com";
$username = "if0_41738969";
$password = "CpoU4rp4mp4o";
$dbname = "if0_41738969_evelyn_bd";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida");
}

/* 🔥 CAMBIO IMPORTANTE (ARREGLA EMOJIS) */
$conn->set_charset("utf8mb4");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>