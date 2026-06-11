<?php

include("../db.php");

$dni = $_POST['dni'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$evento_id = $_POST['evento_id'] ?? 0;

$sql = "SELECT id, codigo
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