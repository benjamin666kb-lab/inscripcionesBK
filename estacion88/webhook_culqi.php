<?php

include("../db.php");

// 🔥 Recibir JSON de Culqi
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Guardar log (MUY recomendado)
file_put_contents("culqi_log.txt", $input . PHP_EOL, FILE_APPEND);

// Validar que venga evento
if(!isset($data['data']['id'])){
    http_response_code(400);
    exit("No data");
}

// ID del cargo
$cargo_id = $data['data']['id'];
$estado = $data['data']['status'] ?? '';
$email = $data['data']['email'] ?? '';

// 🔎 Buscar inscripción por token_culqi o cargo
$sql = "SELECT * FROM inscritos WHERE cargo_culqi=? OR token_culqi=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $cargo_id, $cargo_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    http_response_code(200);
    exit("No encontrado");
}

$inscrito = $result->fetch_assoc();

// 🔥 Actualizar según estado real de Culqi
if($estado == "paid"){

    $update = $conn->prepare("
        UPDATE inscritos 
        SET estado_pago='PAGADO', fecha_pago=NOW()
        WHERE id=?
    ");
    $update->bind_param("i", $inscrito['id']);
    $update->execute();

}else if($estado == "declined"){

    $update = $conn->prepare("
        UPDATE inscritos 
        SET estado_pago='RECHAZADO'
        WHERE id=?
    ");
    $update->bind_param("i", $inscrito['id']);
    $update->execute();

}else{

    $update = $conn->prepare("
        UPDATE inscritos 
        SET estado_pago='REVIEW'
        WHERE id=?
    ");
    $update->bind_param("i", $inscrito['id']);
    $update->execute();
}

// Respuesta obligatoria a Culqi
http_response_code(200);
echo "OK";