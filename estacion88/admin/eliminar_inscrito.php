<?php

session_start();
include("sesion_check.php");
if(!isset($_SESSION['id_admin'])){
    header("Location: login.php");
    exit;
}

include("../../db.php");

if(!isset($_GET['id'])){
    die("ID no válido");
}

$id = intval($_GET['id']);

$sql = "DELETE FROM inscritos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);

if($stmt->execute()){
    header("Location: inscritos.php?msg=eliminado");
}else{
    echo "Error al eliminar";
}