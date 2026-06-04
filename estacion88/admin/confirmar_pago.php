<?php

session_start();

if(!isset($_SESSION['id_admin'])){
    header("Location: login.php");
    exit;
}
include("session_check.php");
include("../../db.php");

if(!isset($_GET['id'])){
    die("ID no válido");
}

$id = intval($_GET['id']);

// Cambiar estado a PAGADO
$sql = "UPDATE inscritos SET estado_pago='PAGADO' WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if($stmt->execute()){
    header("Location: detalle_inscrito.php?id=".$id."&msg=ok");
    exit;
}else{
    echo "Error al actualizar pago";
}