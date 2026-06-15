<?php

include("../db.php");

if(!isset($_GET['id']) || !isset($_GET['token'])){
    die("Datos incompletos.");
}

$id = intval($_GET['id']);
$token = trim($_GET['token']);

$sql = "SELECT * FROM inscritos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows==0){
    die("Inscripción no encontrada.");
}

$inscrito = $resultado->fetch_assoc();

// 🔴 EVITAR DOBLE PAGO// 🔴 EVITAR DOBLE PAGO// 🔴 EVITAR DOBLE PAGO// 🔴 EVITAR DOBLE PAGO
if($inscrito['estado_pago'] == 'PAGADO'){
    die("Esta inscripción ya fue pagada.");
}

// 🔵 bloquea mientras se procesa// 🔵 bloquea mientras se procesa// 🔵 bloquea mientras se procesa
$update = $conn->prepare("UPDATE inscritos SET estado_pago='PROCESANDO' WHERE id=?");
$update->bind_param("i", $id);
$update->execute();

$monto = intval($inscrito['monto'] * 100);

$data = [
    "amount" => $monto,
    "currency_code" => "PEN",
    "email" => "accept@culqi.com",
    "source_id" => $token
];

$curl = curl_init();

curl_setopt_array($curl,[
    CURLOPT_URL => "https://api.culqi.com/v2/charges",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer sk_test_DQgiTt1Zu3hMARAk"
    ]
]);

$response = curl_exec($curl);

curl_close($curl);

$respuesta = json_decode($response,true);

if(
    isset($respuesta['outcome']['type']) &&
    $respuesta['outcome']['type'] == 'venta_exitosa'
){

    $cargo = $respuesta['id'];

    $sql = "
    UPDATE inscritos
    SET
        estado_pago='PAGADO',
        token_culqi=?,
        cargo_culqi=?,
        fecha_pago=NOW()
    WHERE id=?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssi",
        $token,
        $cargo,
        $id
    );

    $stmt->execute();

    header(
        "Location: confirmacion?codigo=".$inscrito['codigo']
    );

    exit;

}else{

    echo "<h2>Pago rechazado</h2>";

    echo "<pre>";
    print_r($respuesta);
    echo "</pre>";

}