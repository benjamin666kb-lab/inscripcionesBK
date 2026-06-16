<?php

session_start();

include("sesion_check.php");
include("csrf.php");

if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}

include("../../db.php");

/* SOLO ACEPTAR POST */
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Método no permitido");
}

/* VALIDAR TOKEN CSRF */
validar_csrf($_POST['csrf_token']);

$id = intval($_POST['id']);

if($id <= 0){
    die("ID no válido");
}

$sql = "DELETE FROM inscritos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);

if($stmt->execute()){

    header("Location: inscritos?msg=eliminado");
    exit;

}else{

    echo "Error al eliminar";
}