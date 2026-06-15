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

:root{
    --bg-main:#000000;
    --bg-section:#0d0505;
    --bg-card:#e0e0b6cc;

    --primary:#e31b23;
    --primary-hover:#ff3038;

    --text:#ffffff;
    --text-soft:#cfcfcf;

    --border:rgba(255,255,255,.08);
}

/* =========================
   GENERAL
========================= */
body{
    margin:0;
    background:var(--bg-main);
    color:var(--text);
    font-family:'Segoe UI',sans-serif;
    overflow-x:hidden;
}

/* =========================
   BOTON VOLVER
========================= */
.btn-volver{
    position:fixed;
    top:20px;
    left:20px;

    background:rgba(0,0,0,.65);
    color:white;

    padding:10px 16px;

    border-radius:50px;

    text-decoration:none;

    font-size:13px;
    font-weight:700;

    border:1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(8px);

    transition:.3s;

    z-index:999;
}

.btn-volver:hover{
    background:var(--primary);
    color:white;
    transform:translateY(-2px);
}

/* =========================
   HERO
========================= */
.hero{
    min-height:75vh;

    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;

    text-align:center;

    padding:80px 20px;

    position:relative;

    background:
    linear-gradient(
        rgba(0,0,0,.75),
        rgba(0,0,0,.75)
    );
}

.hero::before{
    content:"";

    position:absolute;
    inset:0;

    background:
    radial-gradient(
        circle at center,
        rgba(227,27,35,.15),
        transparent 70%
    );

    pointer-events:none;
}

.hero h1{
    font-size:clamp(2.5rem,6vw,5rem);
    word-wrap:break-word;
    overflow-wrap:break-word;
    font-weight:900;
    margin-bottom:20px;
    line-height:1.1;
    z-index:2;
}

.hero p{
    max-width:900px;
    color:var(--text-soft);
    font-size:1.1rem;
    line-height:1.8;
    z-index:2;
}
.hero p,
.content-text{
    overflow-wrap:break-word;
    word-break:break-word;
}

/* =========================
   BOTON PRINCIPAL
========================= */
.btn-inscribirme{
    margin-top:30px;

    background:var(--primary);
    color:white;

    border:none;

    padding:16px 40px;

    border-radius:50px;

    font-size:15px;
    font-weight:700;

    text-decoration:none;

    transition:.3s;

    box-shadow:
    0 10px 25px rgba(227,27,35,.30);

    z-index:2;
}

.btn-inscribirme:hover{
    background:var(--primary-hover);

    color:white;

    transform:translateY(-4px);

    box-shadow:
    0 15px 35px rgba(227,27,35,.45);
}

/* =========================
   CONTENIDO
========================= */
.content{
    padding:80px 20px;
    background:var(--bg-section);
}

/* =========================
   BADGE
========================= */
.badge-event{
    background:var(--primary);

    color:white;

    padding:10px 18px;

    border-radius:50px;

    display:inline-block;

    margin-bottom:20px;

    font-size:13px;
    font-weight:700;

    letter-spacing:.5px;
}

/* =========================
   CARDS
========================= */
.card-info{
    background: rgba(209, 248, 248, 0.61);

    border:1px solid var(--border);

    border-radius:24px;
    border-top:8px solid #e31b23;

    padding:30px;

    height:100%;

    transition:.3s;
}

.card-info:hover{
    transform:translateY(-6px);

    border-color:
    rgba(240, 16, 24, 0.9);

    box-shadow:
    0 15px 40px rgba(99, 75, 75, 0.72);
}
.card-info:hover h3,
.card-info:hover h4,
.card-info:hover h5{
    transform: skew(2deg) scale(1.03);
    letter-spacing:1px;
}
/* =========================
   TITULOS DE TARJETA
========================= */
.card-info h3,
.card-info h4,
.card-info h5{
    color:#000; /* texto negro */

    font-weight:900;

    /* borde/sombra para contraste */
    text-shadow:
        -1px -1px 0 #f7f7f7,
         1px -1px 0 #e0e0e0,
        -1px  1px 0 #ffffff,
         1px  1px 0 #e9e9e9;

    /* sensación de energía/movimiento */
    letter-spacing:0.5px;
    transform: skew(-2deg);

    transition:0.3s ease;
}

/* =========================
   TEXTO INTERNO
========================= */
.content-text{
    white-space:pre-line;
    line-height:1.5;
    font-size:16px;

    color:#000; /* Negro puro */

    font-weight:500;

    letter-spacing:0.3px;

    font-family:"Segoe UI","Arial",sans-serif;

    transform: rotate(-0.2deg);

    /* sombra/borde MUY sutil */
    text-shadow:
        0.3px 0.3px 0 rgba(0, 0, 0, 0.75),
       -0.3px -0.3px 0 rgba(0, 0, 0, 0.75);
}

/* =========================
   LINEA DECORATIVA
========================= */
.section-divider{
    width:80px;
    height:4px;

    background:var(--primary);

    border-radius:20px;

    margin:0 auto 30px auto;
}

/* =========================
   RESPONSIVE
========================= */
@media(max-width:768px){

    .hero{
        min-height:65vh;
        padding:70px 20px;
    }

    .hero h1{
        font-size:2.3rem;
    }

    .hero p{
        font-size:1rem;
    }

    .card-info{
        padding:22px;
    }

    .btn-inscribirme{
        width:100%;
        max-width:320px;
    }

    .btn-volver{
        top:15px;
        left:15px;
    }
}

</style>

</head>

<body>


<!-- 🔥 HERO -->
<div class="hero" style="
    background:
        linear-gradient(rgba(0,0,0,.6), rgba(0,0,0,.7)),
        url('../uploads/<?php echo $ev['imagen_portada']; ?>');
    background-size: cover;
    background-position: center;
">
    
    <span class="badge-event">🎟 Evento Oficial</span>

    <h1><?php echo $ev['nombre']; ?></h1>

    <p><?php echo nl2br(htmlspecialchars($ev['descripcion'])); ?></p>

    <p>📅 <?php echo $ev['fecha_evento']; ?></p>

    <a href="inscripcion?evento_id=<?php echo $ev['id']; ?>">
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
<div class="content" style="text-align: center;">
<a href="inscripcion?evento_id=<?php echo $ev['id']; ?>">
        <button class="btn-inscribirme">🚀 INSCRIBIRME AHORA</button>
    </a>
</div>
</div>

</body>
<a href="javascript:history.back()" class="btn-volver">
← Volver
</a>
</html>