<?php

include("../db.php");

if(!isset($_GET['codigo'])){
    header("Location: index");
    exit;
}

$codigo = trim($_GET['codigo']);

$stmt = $conn->prepare("SELECT * FROM inscritos WHERE codigo=? LIMIT 1");
$stmt->bind_param("s", $codigo);
$stmt->execute();

$resultado = $stmt->get_result();
$inscrito = $resultado->fetch_assoc();

if(!$inscrito){
    die("Inscripcion no encontrada.");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pago Yape pendiente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#2b1055,#7597de);
    padding:20px;
    font-family:Arial,sans-serif;
}
.card{
    max-width:620px;
    width:100%;
    border:none;
    border-radius:22px;
    box-shadow:0 20px 50px rgba(0,0,0,.25);
}
.code{
    background:#f4f6fb;
    border-radius:12px;
    padding:14px;
    font-weight:800;
    text-align:center;
    letter-spacing:.5px;
}
</style>
</head>
<body>
<div class="card">
    <div class="card-body p-4 p-md-5 text-center">
        <h1 class="h3 mb-3">Pago Yape registrado</h1>
        <p class="text-muted">
            Tu inscripcion quedo pendiente de validacion. Revisaremos la operacion en un plazo de 6h.
        </p>

        <div class="row text-start mt-4">
            <div class="col-md-6 mb-3">
                <strong>Codigo</strong>
                <div class="code"><?= htmlspecialchars($inscrito['codigo'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Operacion Yape</strong>
                <div class="code"><?= htmlspecialchars($inscrito['numero_operacion_yape'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Participante</strong>
                <div class="form-control bg-light"><?= htmlspecialchars($inscrito['nombre'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Monto</strong>
                <div class="form-control bg-light">S/ <?= number_format((float)$inscrito['monto'], 2); ?></div>
            </div>
        </div>

        <a href="index" class="btn btn-success mt-3">Volver al inicio</a>
    </div>
</div>
</body>
</html>

