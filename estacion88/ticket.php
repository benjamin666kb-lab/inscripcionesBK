<?php

include("../db.php");

$codigo = $_GET['codigo'] ?? '';

if(empty($codigo)){
    die("Ticket no encontrado");
}

$sql = "
SELECT
    i.*,
    e.nombre AS nombre_evento,
    e.fecha_evento
FROM inscritos i
LEFT JOIN eventos e
ON i.evento_id = e.id
WHERE i.codigo = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$codigo);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows == 0){
    die("Ticket no encontrado");
}

$inscrito = $resultado->fetch_assoc();

$url_ticket =
"https://inscripcionesbk.free.nf/estacion88/ticket?codigo="
. urlencode($inscrito['codigo']);

$qr_url =
"https://api.qrserver.com/v1/create-qr-code/?size=250x250&data="
. urlencode($url_ticket);
$colorEstado = "#dc3545";

$estadoMostrar = strtoupper(trim($inscrito['estado_pago'] ?? ''));

if(empty($estadoMostrar) && $inscrito['monto'] <= 0){
    $estadoMostrar = "LIBRE";
}

$colorEstado = "#dc3545";

if($estadoMostrar=="PAGADO"){
    $colorEstado = "#198754";
}
elseif($estadoMostrar=="PENDIENTE"){
    $colorEstado = "#fd7e14";
}
elseif($estadoMostrar=="LIBRE"){
    $colorEstado = "#0d6efd";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Mi Ticket</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

    body{
    background:#f4f6f9;
    }

    .card-ticket{
    max-width:950px;
    margin:40px auto;
    border:none;
    border-radius:25px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    }

    .header-ticket{
    background:linear-gradient(135deg,#0d6efd,#0a58ca);
    color:white;
    padding:30px;
    }

    .info{
    padding:30px;
    }

    .item{
    margin-bottom:18px;
    }

    .label{
    color:#777;
    font-size:13px;
    text-transform:uppercase;
    }

    .valor{
    font-size:18px;
    font-weight:600;
    }

    @media print{

    .acciones{
        display:none;
    }

    body{
        background:white;
    }

    .card-ticket{
        box-shadow:none;
    }

    }   

</style>

</head>

<body>

<div class="card card-ticket">

<div class="header-ticket">

<h2>
🏃 <?php echo htmlspecialchars($inscrito['nombre_evento']); ?>
</h2>

<p class="mb-0">
Comprobante de inscripción
</p>

</div>

<div class="info">

<div class="row">

<div class="col-md-6 item">
<div class="label">Código</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['codigo'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Estado</div>
<div class="valor" style="color:<?php echo $colorEstado; ?>">
<?php echo htmlspecialchars($estadoMostrar, ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Nombre</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['nombre'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">DNI</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['dni'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Teléfono</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['telefono'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Correo</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['correo'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Edad</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['edad'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Distancia</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['distancia'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Talla</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['talla'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Kit</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['kit'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Monto</div>
<div class="valor">
S/ <?php echo number_format((float)$inscrito['monto'], 2); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Fecha del Evento</div>
<div class="valor">
<?php echo htmlspecialchars($inscrito['fecha_evento'], ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

</div>

<hr>

<div class="acciones">
<?php

$mostrarPago = in_array(
    $inscrito['estado_pago'],
    ['PENDIENTE','PROCESANDO','REVIEW']
);

if($mostrarPago){

?>

<a
href="checkout?id=<?php echo $inscrito['id']; ?>"
class="btn btn-warning">

💳 Continuar Pago

</a>

<?php } ?>
<button
onclick="window.print();"
class="btn btn-primary">
🖨️ Imprimir Ticket
</button>

<button type="button"
        class="btn btn-secondary"
        onclick="if(document.referrer){history.back();}else{window.location='index';}">
    ⬅ Volver
</button>

</div>
<div class="text-center mt-4">

    <h5>📱 Código QR del Participante</h5>

    <img
        src="<?php echo $qr_url; ?>"
        alt="QR Ticket"
        width="250">

    <p class="mt-2 text-muted">
        Escanea este código para abrir el ticket.
    </p>

</div>
</div>

</div>

</body>
<hr>

<footer class="text-center mt-3">
    
    <small class="text-muted">
        <strong>Información importante:</strong><br>
        Si necesita corregir o actualizar datos de su inscripción
        (nombre, DNI, teléfono, talla u otros), comuníquese con soporte.
        <br>
        
        📞 Soporte: <strong>Juan Pérez</strong>
        |
        
        <a href="https://wa.me/51999999999"
           target="_blank"
           style="text-decoration:none;">
            +51 999 999 999
        </a>
    </small>

</footer>
</html>