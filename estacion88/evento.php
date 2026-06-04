<?php
include("../db.php");

if(!isset($_GET['id'])){
    die("Evento no encontrado");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM eventos WHERE id=$id LIMIT 1";
$result = mysqli_query($conn, $sql);
$ev = mysqli_fetch_assoc($result);

if(!$ev){
    die("Evento no existe");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo $ev['nombre']; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    background:#0f172a;
    color:white;
    font-family:Arial;
}

/* HERO */
.hero{
    min-height:70vh;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;

    background:
    linear-gradient(rgba(0,0,0,.6),rgba(0,0,0,.7)),
    url('uploads/<?php echo $ev['imagen_portada']; ?>');

    background-size:cover;
    background-position:center;

    padding:40px;
}

.hero h1{
    font-size:3rem;
    font-weight:900;
}

.hero p{
    font-size:1.1rem;
    opacity:.9;
    max-width:900px;
}

/* BOTÓN */
.btn-inscribirme{
    margin-top:20px;
    background:#ffeb3b;
    color:#000;
    border:none;
    padding:15px 40px;
    font-size:1.1rem;
    font-weight:bold;
    border-radius:50px;
    transition:.3s;
}

.btn-inscribirme:hover{
    transform:scale(1.05);
}

/* CONTENIDO */
.content{
    padding:60px 20px;
    background: linear-gradient(135deg,#14532d,#0f172a);
}

/* CARDS */
.card-info{
    background:rgba(255,255,255,0.08);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:20px;
    padding:25px;
    height:100%;
}

/* TEXTO INTERNO */
.content-text{
    white-space: pre-line;
    line-height: 1.6;
    font-size: 15px;
}

/* BADGE */
.badge-event{
    background:#22c55e;
    padding:10px 15px;
    border-radius:15px;
    display:inline-block;
    margin-bottom:15px;
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

<!-- 🔥 HERO -->
<div class="hero">

<span class="badge-event">🎟 Evento Oficial</span>

<h1><?php echo $ev['nombre']; ?></h1>

<p><?php echo $ev['descripcion']; ?></p>

<p>📅 <?php echo $ev['fecha_evento']; ?></p>

<a href="inscripcion.php?evento_id=<?php echo $ev['id']; ?>">
    <button class="btn-inscribirme">🚀 INSCRIBIRME AHORA</button>
</a>

</div>

<!-- 📌 CONTENIDO -->
<div class="content">

<div class="container">

<div class="row g-4">

<!-- 📌 DETALLES -->
<div class="col-md-6">

<div class="card-info">

<h3>📌 Detalles del evento</h3>

<div class="content-text">
<?php 
echo !empty($ev['detalles_evento']) 
    ? htmlspecialchars($ev['detalles_evento'], ENT_QUOTES, 'UTF-8')
    : "✔ Evento oficial organizado por el sistema
✔ Cupos limitados
✔ Certificación y kit de participación
✔ Control de inscripción automatizado";
?>
</div>

</div>

</div>

<!-- 🎯 INFO IMPORTANTE -->
<div class="col-md-6">

<div class="card-info">

<h3>🎯 Información importante</h3>

<div class="content-text">
<?php 
echo !empty($ev['info_importante']) 
    ? htmlspecialchars($ev['info_importante'], ENT_QUOTES, 'UTF-8')
    : "- Llega 30 min antes del evento
- Lleva tu código de inscripción
- Revisa tu kit seleccionado
- No olvides tu DNI";
?>
</div>

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