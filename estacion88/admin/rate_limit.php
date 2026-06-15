<?php

function verificarRateLimit($conn, $accion, $maxIntentos, $segundos)
{
    $ip = $_SERVER['REMOTE_ADDR'];

    // Limpiar registros antiguos
    $stmt = $conn->prepare("
        DELETE FROM rate_limit
        WHERE fecha < DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");

    $stmt->bind_param("i", $segundos);
    $stmt->execute();

    // Contar intentos recientes
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM rate_limit
        WHERE ip = ?
        AND accion = ?
        AND fecha >= DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");

    $stmt->bind_param("ssi", $ip, $accion, $segundos);
    $stmt->execute();

    $resultado = $stmt->get_result()->fetch_assoc();

    if($resultado['total'] >= $maxIntentos){

        http_response_code(429);

        die("
        <div style='
            font-family:Arial;
            text-align:center;
            margin-top:50px;
        '>
            <h3>⛔ Demasiados intentos</h3>
            <p>Por seguridad, espera unos minutos e inténtalo nuevamente.</p>
        </div>
        ");
    }
}

function registrarIntento($conn, $accion)
{
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("
        INSERT INTO rate_limit
        (
            ip,
            accion
        )
        VALUES
        (
            ?,
            ?
        )
    ");

    $stmt->bind_param("ss", $ip, $accion);
    $stmt->execute();
}