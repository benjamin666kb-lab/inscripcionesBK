<?php

include("../db.php");

if(!isset($_GET['codigo'])){

    header("Location:index");
    exit;

}

$codigo = $_GET['codigo'];

$sql = "
SELECT
i.*,
e.nombre AS evento
FROM inscritos i
INNER JOIN eventos e
ON i.evento_id=e.id
WHERE i.codigo=?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s",$codigo);

$stmt->execute();

$resultado = $stmt->get_result();

$inscrito = $resultado->fetch_assoc();
$url_ticket =
"https://inscripcionesbk.free.nf/estacion88/ticket?codigo="
. urlencode($inscrito['codigo']);

$qr_url =
"https://api.qrserver.com/v1/create-qr-code/?size=250x250&data="
. urlencode($url_ticket);

if(!$inscrito){

    die("Inscripción no encontrada");

}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Confirmación</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{

background:
linear-gradient(
135deg,
#43a047,
#66bb6a,
#81c784
);

min-height:100vh;

display:flex;
align-items:center;
justify-content:center;

}

.card-confirmacion{

background:white;

max-width:700px;

width:100%;

padding:40px;

border-radius:25px;

box-shadow:
0 15px 40px rgba(0,0,0,.25);

}

.icono{

font-size:5rem;

text-align:center;

}

.codigo{

background:#f5f5f5;

padding:15px;

border-radius:10px;

font-size:1.3rem;

font-weight:bold;

text-align:center;

}

</style>

</head>
<body>

<div class="card-confirmacion">

<div class="icono">
🏆
</div>

<h2 class="text-center text-success mb-4">
¡INSCRIPCIÓN CONFIRMADA!
</h2>

<div class="alert alert-success text-center">

✅ Tu registro fue procesado correctamente.

</div>

<div class="row">

<div class="col-md-6 mb-3">

<strong>Evento</strong>

<div class="form-control bg-light">

<?php echo $inscrito['evento']; ?>

</div>

</div>

<div class="col-md-6 mb-3">

<strong>Código</strong>

<div class="form-control bg-light">

<?php echo $inscrito['codigo']; ?>

</div>

</div>

<div class="col-md-6 mb-3">

<strong>Participante</strong>

<div class="form-control bg-light">

<?php echo $inscrito['nombre']; ?>

</div>

</div>

<div class="col-md-6 mb-3">

<strong>DNI</strong>

<div class="form-control bg-light">

<?php echo $inscrito['dni']; ?>

</div>

</div>

<div class="col-md-6 mb-3">

<strong>Correo</strong>

<div class="form-control bg-light">

<?php echo $inscrito['correo']; ?>

</div>

</div>

<div class="col-md-6 mb-3">

<strong>Teléfono</strong>

<div class="form-control bg-light">

<?php echo $inscrito['telefono']; ?>

</div>

</div>

<div class="col-md-6 mb-3">

<strong>Kit Seleccionado</strong>

<div class="form-control bg-light">

<?php echo $inscrito['kit']; ?>

</div>

</div>

<div class="col-md-6 mb-3">

<strong>Monto</strong>

<div class="form-control bg-light">

S/ <?php echo number_format($inscrito['monto'],2); ?>

</div>

</div>

<div class="col-md-4 mb-3">

<strong>Distancia</strong>

<div class="form-control bg-light">

<?php echo $inscrito['distancia']; ?>

</div>

</div>

<div class="col-md-4 mb-3">

<strong>Talla</strong>

<div class="form-control bg-light">

<?php echo $inscrito['talla']; ?>

</div>

</div>

<div class="col-md-4 mb-3">

<strong>Estado</strong>

<div class="form-control bg-light">

<?php echo $inscrito['estado_pago']; ?>

</div>

</div>

</div>

<hr>

<div class="text-center">

<h5 class="mb-3">

🎯 Guarda tu código de inscripción

</h5>

<div class="codigo">

<?php echo $inscrito['codigo']; ?>

</div>

</div>
<div class="text-center mt-4">

    <h5>📱 Tu Código QR</h5>

    <img
        src="<?php echo $qr_url; ?>"
        alt="QR Ticket"
        width="220"
        style="border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,.15);">

    <p class="text-muted mt-2">
        Escanea para ver tu ticket
    </p>

</div>
<div class="text-center mt-4">

<a href="index" class="btn btn-success">

Volver al Inicio

</a>

</div>

</div>

</body>
</html>