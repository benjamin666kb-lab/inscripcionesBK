<?php

include("sesion_check.php");

if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}

include("csrf.php");
include("../../db.php");

$rol = strtoupper($_SESSION['rol'] ?? '');
if(!in_array($rol, ['ADMIN', 'OPERADOR'], true)){
    die("Acceso denegado.");
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: dashboard");
    exit;
}

validar_csrf($_POST['csrf_token'] ?? '');

$ids = $_POST['confirmar'] ?? [];
if(!is_array($ids) || empty($ids)){
    header("Location: dashboard?msg=sin_conciliacion");
    exit;
}

$confirmados = 0;
$stmt = $conn->prepare("
    UPDATE inscritos
    SET
        estado_pago='PAGADO',
        metodo_pago='YAPE',
        fecha_pago=NOW()
    WHERE id=?
      AND estado_pago='YAPE_PENDIENTE'
");

if(!$stmt){
    die("No se pudo preparar la confirmacion.");
}

foreach($ids as $id){
    $id = intval($id);
    if($id <= 0){
        continue;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $confirmados += $stmt->affected_rows > 0 ? 1 : 0;
}

header("Location: inscritos.php?msg=conciliados&total=" . $confirmados);
exit;

