<?php
 
include("sesion_check.php");
if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}

include("../../db.php");

if(!isset($_GET['id'])){
    die("Inscrito no encontrado");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM inscritos WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows==0){
    die("Inscrito no encontrado");
}

$inscrito = $resultado->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Detalle del Inscrito</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background: linear-gradient(135deg,#f7f9fc,#eef2f7);
    min-height:100vh;
    font-family: 'Segoe UI', sans-serif;
}

.card-detalle{
    max-width:950px;
    margin:40px auto;
    background:#fff;
    border-radius:25px;
    overflow:hidden;
    box-shadow:0 15px 35px rgba(0,0,0,.08);
}

.header-card{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    padding:25px 30px;
    color:white;
}

.header-card h2{
    margin:0;
    font-size:28px;
    font-weight:700;
}

.contenido{
    padding:30px;
}

.info-box{
    background:#fafbfc;
    border:1px solid #eceff3;
    border-radius:15px;
    padding:15px;
    margin-bottom:18px;
    transition:.25s;
}

.info-box:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,.05);
}

.label{
    font-size:13px;
    text-transform:uppercase;
    color:#888;
    letter-spacing:.5px;
    margin-bottom:5px;
}

.valor{
    font-size:18px;
    font-weight:600;
    color:#222;
    padding-bottom:8px;
    margin-top:3px;
    border-bottom:1px solid #edf1f5;
}

.badge-estado{
    display:inline-block;
    padding:8px 18px;
    border-radius:30px;
    background:#e8f7ed;
    color:#198754;
    font-weight:600;
}

.botones{
    margin-top:25px;
}

.btn{
    padding:10px 22px;
    border-radius:12px;
    font-weight:600;
}

.btn-success{
    background:#198754;
    border:none;
}

.btn-success:hover{
    background:#157347;
}

.btn-secondary{
    background:#6c757d;
    border:none;
}
@media print {

    body{
        background:white !important;
    }

    .botones-accion{
        display:none !important;
    }

    .card-detalle{
        box-shadow:none;
        border:1px solid #ddd;
        margin:0;
        max-width:100%;
    }

    .header-card{
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

}
</style>

</head>
<body>

<div class="container">

<div class="card-detalle">

<div class="header-card">
    <h2>🏃 Detalle del Participante</h2>
    <small>| Comprobante de inscripción</small>
</div>

<div class="contenido">
<div class="row">

<div class="col-md-6 item">
<div class="label">Código</div>
<div class="valor">
<?= htmlspecialchars($inscrito['codigo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Estado</div>
<div class="valor" style="color:red">
<?= htmlspecialchars($inscrito['estado_pago'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Nombre</div>
<div class="valor">
<?= htmlspecialchars($inscrito['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">DNI</div>
<div class="valor">
<?= htmlspecialchars($inscrito['dni'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Teléfono</div>
<div class="valor">
<?= htmlspecialchars($inscrito['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Correo</div>
<div class="valor">
<?= htmlspecialchars($inscrito['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Edad</div>
<div class="valor">
<?= htmlspecialchars($inscrito['edad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Distancia</div>
<div class="valor">
<?= htmlspecialchars($inscrito['distancia'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Talla</div>
<div class="valor">
<?= htmlspecialchars($inscrito['talla'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Categoría</div>
<div class="valor">
<?= htmlspecialchars($inscrito['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Kit</div>
<div class="valor">
<?= htmlspecialchars($inscrito['kit'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Monto</div>
<div class="valor">
S/ <?= number_format((float)$inscrito['monto'], 2); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Metodo de Pago</div>
<div class="valor">
<?= htmlspecialchars($inscrito['metodo_pago'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-6 item">
<div class="label">Operacion Yape</div>
<div class="valor">
<?= htmlspecialchars($inscrito['numero_operacion_yape'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

<div class="col-md-12 item">
<div class="label">Fecha Registro</div>
<div class="valor">
<?= htmlspecialchars($inscrito['fecha_registro'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
</div>
</div>

</div>
<hr>

<div class="d-flex gap-2">
<?php
$estado = strtoupper(trim($inscrito['estado_pago'] ?? ''));

if(
    $_SESSION['rol'] == 'ADMIN'
    && (float)$inscrito['monto'] > 0
    && in_array($estado, ['PENDIENTE','YAPE_PENDIENTE','REVIEW'], true)
){
?>

<form method="POST" action="confirmar_pago" style="display:inline;">

    <input
        type="hidden"
        name="id"
        value="<?= $id ?>">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= $_SESSION['csrf_token'] ?>">

    <button
        type="submit"
        class="btn btn-success"
        onclick="return confirm('¿Confirmar pago manualmente?')">

        Confirmar Pago

    </button>

</form>

<?php } ?>
<button
type="button"
class="btn btn-primary"
onclick="window.print();">
🖨️ Imprimir
</button>
<a
href="inscritos.php"
class="btn btn-secondary"
>
⬅ Volver
</a>

</div>

</div>

</div>
<?php if(isset($_GET['msg']) && $_GET['msg']=="ok"){ ?>
<div class="alert alert-success">
✔ Pago confirmado correctamente
</div>
<?php } ?>
</body>
</html>
