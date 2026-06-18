<?php if(isset($_GET['deleted'])){ ?>
<div class="alert alert-success text-dark">
    ✔ Evento eliminado correctamente
</div>
<?php } ?>
<?php

 

include("sesion_check.php");
include("csrf.php");
include("../../db.php");

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Método no permitido");
}

validar_csrf($_POST['csrf_token']);

$id = intval($_POST['id']);

if($id <= 0){
    die("ID inválido");
}

// 🔥 DEBUG opcional (descomenta si falla)
// echo "ID recibido: " . $id;

// 🔥 1. eliminar inscritos
$stmt1 = $conn->prepare("DELETE FROM inscritos WHERE evento_id = ?");
$stmt1->bind_param("i", $id);
$stmt1->execute();

// 🔥 2. eliminar kits
$stmt2 = $conn->prepare("DELETE FROM eventos_kits WHERE evento_id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();

// 🔥 3. eliminar evento
$stmt3 = $conn->prepare("DELETE FROM eventos WHERE id = ?");
$stmt3->bind_param("i", $id);

if($stmt3->execute()){
    header("Location: eventos_lista?deleted=1");
    exit;
}else{
    die("Error al eliminar evento: " . $stmt3->error);
}