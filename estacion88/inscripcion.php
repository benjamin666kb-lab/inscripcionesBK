<?php
require_once("../db.php");

$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;

if($evento_id <= 0){
    die("Evento inválido");
}

/* 🔥 Obtener evento */
$stmt = $conn->prepare("
SELECT *
FROM eventos
WHERE id = ?
LIMIT 1
");

$stmt->bind_param("i", $evento_id);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if(!$evento){
    die("Evento no encontrado");
}

/* 🔥 Obtener kits del evento */
$stmt2 = $conn->prepare("
SELECT id, nombre_kit, precio, descripcion
FROM eventos_kits
WHERE evento_id = ?
");

$stmt2->bind_param("i", $evento_id);
$stmt2->execute();
$kits = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Inscripción Oficial</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>

*{
    font-family:'Poppins',sans-serif;
}

body{
    background: linear-gradient(135deg,#00c853,#43a047,#1b5e20);
    min-height:100vh;
    padding:50px 0;
}

.card-evento{
    background:#fff;
    border-radius:30px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
}

.header-evento{
    background: linear-gradient(135deg,#ff9800,#ff5722);
    color:white;
    padding:40px;
    text-align:center;
}

.header-evento h1{
    font-size:3rem;
    font-weight:900;
    margin-bottom:10px;
}

.header-evento p{
    font-size:1.1rem;
    margin-bottom:0;
}

.formulario{
    padding:40px;
}

.form-label{
    font-weight:600;
}

.form-control,
.form-select{
    border-radius:15px;
    padding:12px;
}

.btn-inscribirse{
    background: linear-gradient(135deg,#00c853,#43a047);
    border:none;
    width:100%;
    padding:15px;
    font-size:1.2rem;
    font-weight:700;
    border-radius:50px;
    transition:.3s;
    color:white;
}

.btn-inscribirse:hover{
    transform:translateY(-3px);
}

.badge-evento{
    background:#fff3cd;
    padding:12px;
    border-radius:15px;
    font-weight:600;
    color:#856404;
    display:inline-block;
    margin-top:10px;
}

.info-box{
    background:#f8f9fa;
    padding:20px;
    border-radius:20px;
    margin-bottom:25px;
}
.btn-volver{
    position:fixed;
    top:20px;
    left:20px;
    background:rgba(255,255,255,0.15);
    color:white;
    padding:8px 14px;
    border-radius:30px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    backdrop-filter: blur(10px);
    border:1px solid rgba(255,255,255,0.2);
    transition:.3s;
    z-index:999;
}

.btn-volver:hover{
    transform:translateY(-2px);
    background:rgba(255,255,255,0.25);
    color:white;
}
</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card-evento">

<div class="header-evento">

<a href="index.php" style="text-decoration:none; color:inherit;">
  <h1>🏃 ESTACION88.COM</h1>
</a>
<p>
<?php echo $evento['nombre']; ?>
</p>

<div class="badge-evento">
📅 <?php echo $evento['fecha_evento']; ?>
</div>

</div>

<div class="formulario">

<div class="info-box">

<h5>🎯 Completa tu inscripción</h5>

<p class="mb-0">
Llena tus datos correctamente para participar oficialmente en el evento.
</p>

</div>

<form action="guardar_inscripcion.php" method="POST">

<input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Nombre Completo</label>
<input type="text" name="nombre" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">DNI</label>
<input type="text" name="dni" maxlength="8" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Teléfono</label>
<input type="text" name="telefono" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Correo Electrónico</label>
<input type="email" name="correo" class="form-control" required>
</div>

<!-- 🔥 KITS DINÁMICOS -->
<div class="col-12 mb-4">

<label class="form-label">Tipo de Inscripción (Kit)</label>

<select name="kit_id" class="form-select" required>

<option value="">Seleccione un kit</option>

<?php while($k = $kits->fetch_assoc()){ ?>

<option value="<?php echo $k['id']; ?>">
<?php echo $k['nombre_kit']; ?> (S/ <?php echo $k['precio']; ?>)
</option>

<?php } ?>

</select>

</div>

<div class="col-md-4 mb-3">
<label class="form-label">Edad</label>
<input type="number" name="edad" min="1" max="100" class="form-control" required>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Distancia</label>
<select name="distancia" class="form-select" required>
<option value="">Seleccione</option>
<option value="5K">🏃 5K</option>
<option value="10K">🔥 10K</option>
<option value="10K">🔥 15K</option>
<option value="10K">🔥🏃 20K</option>
</select>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Talla Polo</label>
<select name="talla" class="form-select" required>
<option value="">Seleccione</option>
<option>S</option>
<option>M</option>
<option>L</option>
<option>XL</option>
</select>
</div>

</div>

<div class="col-12">

<button type="submit" class="btn-inscribirse">
🚀 CONTINUAR INSCRIPCIÓN
</button>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
<a href="javascript:history.back()" class="btn-volver">
← Volver
</a>
</html>