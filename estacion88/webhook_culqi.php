<?php

include("../db.php");
require_once("../config_culqi.php");

/* =========================
   LEER JSON
========================= */
$input = file_get_contents("php://input");

$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    exit("JSON inválido");
}

// file_put_contents("culqi_log.txt", $input . PHP_EOL, FILE_APPEND);

/* =========================
   FILTRAR EVENTOS
========================= */
$event = $data['event'] ?? '';

$eventPermitidos = [
    "charge.capture",
    "charge.succeeded"
];

if (!in_array($event, $eventPermitidos)) {
    http_response_code(200);
    exit("Evento ignorado");
}

/* =========================
   VALIDAR ID DEL CARGO
========================= */
if (!isset($data['data']['id'])) {
    http_response_code(400);
    exit("No data");
}

$cargo_id = trim($data['data']['id']);

if (
    empty($cargo_id) ||
    strlen($cargo_id) > 100 ||
    !preg_match('/^[a-zA-Z0-9_-]+$/', $cargo_id)
) {
    http_response_code(400);
    exit("Cargo inválido");
}

/* =========================
   CONSULTA REAL A CULQI
========================= */
$ch = curl_init("https://api.culqi.com/v2/charges/$cargo_id");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . CULQI_SECRET_KEY,
    "Content-Type: application/json"
]);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    exit("Error consultando Culqi");
}

$result = json_decode($response, true);

curl_close($ch);

if (!is_array($result)) {
    http_response_code(500);
    exit("Respuesta inválida de Culqi");
}

/* =========================
   ESTADO REAL
========================= */
$estado_pago = $result['data']['paid'] ?? false;

/* =========================
   BUSCAR INSCRITO
========================= */
$sql = "
SELECT *
FROM inscritos
WHERE cargo_culqi = ?
   OR token_culqi = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ss",
    $cargo_id,
    $cargo_id
);

$stmt->execute();

$res = $stmt->get_result();

if ($res->num_rows == 0) {
    http_response_code(200);
    exit("No encontrado");
}

$inscrito = $res->fetch_assoc();

/* =========================
   ACTUALIZAR ESTADO
========================= */
if ($estado_pago === true) {

    $update = $conn->prepare("
        UPDATE inscritos
        SET
            estado_pago='PAGADO',
            fecha_pago=NOW()
        WHERE id=?
    ");

    $update->bind_param(
        "i",
        $inscrito['id']
    );

    $update->execute();

} else {

    $update = $conn->prepare("
        UPDATE inscritos
        SET estado_pago='REVIEW'
        WHERE id=?
    ");

    $update->bind_param(
        "i",
        $inscrito['id']
    );

    $update->execute();
}

http_response_code(200);
echo "OK";