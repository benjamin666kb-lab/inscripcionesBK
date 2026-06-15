<?php

include("../db.php");
include("admin/rate_limit.php");

// Máximo 30 consultas por minuto por IP
verificarRateLimit($conn, "verificar_dni", 60, 60);

$dni = trim($_POST['dni'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$evento_id = intval($_POST['evento_id'] ?? 0);

$sql = "
SELECT id, codigo
FROM inscritos
WHERE evento_id = ?
AND (dni = ? OR telefono = ?)
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $evento_id, $dni, $telefono);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){

    $fila = $result->fetch_assoc();

    echo "EXISTE|" . $fila['codigo'];

}else{

    echo "OK";

}

// Registrar la consulta realizada
registrarIntento($conn, "verificar_dni");