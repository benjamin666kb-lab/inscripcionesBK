<?php

include("../db.php");
require_once("../config_culqi.php");
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Método no permitido.");
}

if(!isset($_POST['id']) || !isset($_POST['token'])){
    die("Datos incompletos.");
}

$id = intval($_POST['id']);
$token = trim($_POST['token']);

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
$correo = trim($inscrito['correo']);

$data = [
    "amount" => $monto,
    "currency_code" => "PEN",
    "email" => $correo,
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
        "Authorization: Bearer " . CULQI_SECRET_KEY
    ]
]);

$response = curl_exec($curl);

curl_close($curl);

if(!$response){

    $update = $conn->prepare("
        UPDATE inscritos
        SET estado_pago='PENDIENTE'
        WHERE id=?
    ");

    $update->bind_param("i", $id);
    $update->execute();

    die("Error de conexión con Culqi.");
}
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
        cargo_culqi=?,
        fecha_pago=NOW()
    WHERE id=?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "si",    
        $cargo,
        $id
    );

    $stmt->execute();

    header(
        "Location: confirmacion?codigo=".$inscrito['codigo']
    );

    exit;

}else{

    $update = $conn->prepare("
        UPDATE inscritos
        SET estado_pago='PENDIENTE'
        WHERE id=?
    ");

    $update->bind_param("i", $id);
    $update->execute();

    echo "<h2>Pago rechazado</h2>";

    echo "<pre>";
    print_r($respuesta);
    echo "</pre>";

}