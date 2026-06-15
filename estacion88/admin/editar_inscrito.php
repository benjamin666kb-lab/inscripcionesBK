<?php

session_start();
include("sesion_check.php");
include("csrf.php");
if(!isset($_SESSION['id_admin'])){
    header("Location: login.php");
    exit;
}

include("../../db.php");

if(!isset($_GET['id'])){
    die("ID no válido");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM inscritos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();
$resultado = $stmt->get_result();
$inscrito = $resultado->fetch_assoc();

if($_SERVER["REQUEST_METHOD"]=="POST"){

    validar_csrf($_POST['csrf_token']);

    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $edad = (int)$_POST['edad'];

    $sql = "UPDATE inscritos SET nombre=?, telefono=?, correo=?, edad=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $nombre, $telefono, $correo, $edad, $id);

    if($stmt->execute()){
        header("Location: inscritos.php?msg=editado");
    }else{
        echo "Error al actualizar";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Inscrito</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

<h3>✏️ Editar Inscrito</h3>

<form method="POST" class="card p-3">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= $_SESSION['csrf_token'] ?>">

<input class="form-control mb-2" name="nombre" value="<?= $inscrito['nombre'] ?>" placeholder="Nombre">

<input class="form-control mb-2" name="telefono" value="<?= $inscrito['telefono'] ?>" placeholder="Teléfono">

<input class="form-control mb-2" name="correo" value="<?= $inscrito['correo'] ?>" placeholder="Correo">

<input class="form-control mb-2" name="edad" value="<?= $inscrito['edad'] ?>" placeholder="Edad">

<button class="btn btn-primary">Guardar cambios</button>

</form>

</div>

</body>
</html>