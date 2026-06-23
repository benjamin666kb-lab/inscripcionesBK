<?php

include("../db.php");

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Metodo no permitido.");
}

if(!isset($_POST['id']) || !isset($_POST['numero_operacion_yape'])){
    die("Datos incompletos.");
}

$id = intval($_POST['id']);
$numeroOperacion = trim($_POST['numero_operacion_yape']);
$numeroOperacion = preg_replace('/\s+/', ' ', $numeroOperacion);

if(!preg_match('/^[A-Za-z0-9\- ]{4,40}$/', $numeroOperacion)){
    die("Numero de operacion no valido.");
}

$sql = "SELECT * FROM inscritos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows == 0){
    die("Inscripcion no encontrada.");
}

$inscrito = $resultado->fetch_assoc();
$estado = strtoupper(trim($inscrito['estado_pago'] ?? ''));

if($estado === 'PAGADO' || $estado === 'LIBRE'){
    die("Esta inscripcion ya fue completada.");
}

$sql = "
UPDATE inscritos
SET
    estado_pago='YAPE_PENDIENTE',
    metodo_pago='YAPE',
    numero_operacion_yape=?,
    fecha_yape=NOW()
WHERE id=?
";

$stmt = $conn->prepare($sql);

if(!$stmt){
    die("Falta aplicar la migracion de campos Yape.");
}

$stmt->bind_param("si", $numeroOperacion, $id);
$stmt->execute();

header("Location: pago_yape_pendiente?codigo=" . urlencode($inscrito['codigo']));
exit;

