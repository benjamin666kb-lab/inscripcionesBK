<?php
session_start();
include("../db.php");

// 🔥 Obtener todos los eventos activos
$sql = "SELECT * FROM eventos WHERE estado='activo' ORDER BY fecha_evento DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Eventos</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<style>

:root{
    --rojo:#e31b23;
    --rojo-hover:#ff3038;

    --negro:#000;
    --gris-oscuro:#111;
    --gris-card:#161616;

    --texto:#fff;
    --texto-soft:#cfcfcf;
}

/* =========================
   BODY
========================= */

body{
    background:
    linear-gradient(
        180deg,
        #111111f3 0%,
        #2e2e2ef1 100%
    );

    padding-top:80px;
    color:white;
    font-family:'Poppins',sans-serif;
}

/* =========================
   CONTAINER
========================= */

.container{
    margin-top:70px;
    margin-bottom:60px;
}

/* =========================
   TITULO
========================= */

.title{
    text-align:center;
    margin-bottom:15px;
    font-weight:900;
    font-size:clamp(2rem,5vw,3rem);
}

.subtitle{

    text-align:center;

    color:#bdbdbd;

    margin-bottom:50px;

    max-width:700px;

    margin-left:auto;
    margin-right:auto;
}

/* =========================
   CARD
========================= */

.event-card{

    background:var(--gris-card);

    border-radius:22px;

    overflow:hidden;

    border:1px solid rgba(255,255,255,.08);

    transition:.35s;

    height:100%;
}

.event-card:hover{

    transform:translateY(-8px);

    border-color:
    rgba(227,27,35,.40);

    box-shadow:
    0 20px 40px rgba(0,0,0,.40);
}

/* =========================
   IMAGEN
========================= */

.event-img{

    height:220px;

    overflow:hidden;

    position:relative;
}

.event-img img{

    width:100%;
    height:100%;

    object-fit:cover;

    transition:.5s;
}

.event-card:hover img{

    transform:scale(1.05);
}

/* =========================
   BODY CARD
========================= */

.event-body{

    padding:24px;
}

.event-body h5{

    font-weight:800;

    margin-bottom:10px;

    color:white;
}

.event-body p{

    color:var(--texto-soft);

    margin-bottom:15px;
}

/* =========================
   FECHA
========================= */

.event-date{

    color:#ff6b6b;

    font-size:14px;

    font-weight:700;

    margin-bottom:12px;
}

/* =========================
   BADGES
========================= */

.badge-activo{

    background:#e31b23;

    color:white;

    padding:8px 14px;

    border-radius:50px;

    font-size:12px;
}

.badge-proximo{

    background:#444;

    color:white;

    padding:8px 14px;

    border-radius:50px;

    font-size:12px;
}

/* =========================
   BOTON
========================= */

.btn-event{

    background:var(--rojo);

    border:none;

    width:100%;

    padding:12px;

    border-radius:12px;

    color:white;

    font-weight:700;

    transition:.3s;
}

.btn-event:hover{

    background:var(--rojo-hover);

    transform:translateY(-2px);
}

/* =========================
   EFECTO PREMIUM
========================= */

.event-card::after{

    content:"";

    display:block;

    height:4px;

    background:var(--rojo);

    width:0;

    transition:.35s;
}

.event-card:hover::after{

    width:100%;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:768px){

    .event-img{
        height:200px;
    }

    .event-body{
        padding:20px;
    }

}

/* =========================
   CONTENEDOR GENERAL (LIGHT PREMIUM)
========================= */

.search-ticket-card{

    max-width:750px;
    
    margin:auto;

    background:#ffffff;
    border:1px solid rgba(0,0,0,.08);
    border-radius:24px;

    padding:40px 30px;

    box-shadow:0 15px 35px rgba(0,0,0,.08);

    position:relative;
    overflow:hidden;

    transition:.3s;
}

.search-ticket-card:hover{
    transform:translateY(-5px);
    box-shadow:0 25px 50px rgba(0,0,0,.12);
}

/* línea superior roja elegante */
.search-ticket-card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:4px;
    background:linear-gradient(90deg, var(--rojo), #ff6b6b);
}

/* =========================
   HEADER
========================= */

.search-header{
    text-align:center;
    margin-bottom:25px;
}

.search-header h2{
    font-weight:900;
    color:#1e293b;
    margin-bottom:8px;
    font-size:clamp(1.5rem,3vw,2.2rem);
}

.search-header p{
    color:#64748b;
    margin:0;
    font-size:15px;
}

/* =========================
   SEARCH BOX
========================= */

.search-box{
    display:flex;
    gap:12px;
    justify-content:center;
    align-items:center;
}

/* INPUT (CLARO MODERNO) */
.search-box input{

    flex:1;

    padding:14px 16px;
    border-radius:14px;

    border:1px solid #e2e8f0;

    background:#f8fafc;
    color:#0f172a;

    font-size:16px;

    outline:none;

    transition:.25s;
}

.search-box input:focus{
    border-color:var(--rojo);
    background:#ffffff;
    box-shadow:0 0 0 3px rgba(227,27,35,.12);
}

/* BUTTON */
.search-box button{

    padding:14px 22px;

    border:none;
    border-radius:14px;

    background:var(--rojo);
    color:white;

    font-weight:800;

    cursor:pointer;

    transition:.3s;

    min-width:120px;
}

.search-box button:hover{
    background:var(--rojo-hover);
    transform:translateY(-2px);
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:768px){

    .search-box{
        flex-direction:column;
    }

    .search-box button{
        width:100%;
    }
}
/* =========================
   NAVBAR
========================= */

.navbar88{

    position:fixed;
    top:15px;
    left:50%;
    transform:translateX(-50%);

    width:95%;
    max-width:1400px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:15px 35px;

    background:rgba(34,34,34,.23);

    backdrop-filter:blur(10px);

    border:1px solid rgba(255,255,255,.24);

    border-radius:50px;

    z-index:9999;

    box-shadow:0 10px 30px rgba(0,0,0,.35);
}

/* LOGO */

.logo88 a{
    font-size:1.8rem;
    font-weight:800;
    color:white;
    text-decoration:none;
}

.logo88 span{
    color:#e31b23;
}

/* MENU DESKTOP */

.menu88{
    display:flex;
    gap:40px;
}

.menu88 a{

    color:white;

    text-decoration:none;

    font-weight:700;

    position:relative;

    transition:.3s;
}

.menu88 a:hover{
    color:#e31b23;
}

.menu88 a::after{

    content:"";

    position:absolute;

    left:0;
    bottom:-6px;

    width:0;
    height:2px;

    background:#e31b23;

    transition:.3s;
}

.menu88 a:hover::after{
    width:100%;
}

/* BOTON HAMBURGUESA */

.btn-menu88{

    display:none;

    background:none;

    border:none;

    color:white;

    font-size:32px;

    cursor:pointer;

    line-height:1;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .navbar88{

        padding:15px 20px;

        border-radius:25px;
    }

    .logo88 a{
        font-size:1.3rem;
    }

    .btn-menu88{
        display:block;
    }

    .menu88{

        position:absolute;

        top:75px;
        left:0;

        width:100%;

        display:none;

        flex-direction:column;

        align-items:center;

        gap:15px;

        padding:20px;

        background:rgba(34,34,34,.95);

        backdrop-filter:blur(12px);

        border-radius:20px;

        box-shadow:0 15px 30px rgba(0,0,0,.35);
    }

    .menu88.active{
        display:flex;
    }

    .menu88 a{
        width:100%;
        padding:10px 0;
        text-align:center;
    }
}
</style>

</head>

<body>
<header class="navbar88">

    <div class="logo88">
        <a href="https://www.estacion88.com/">🏃 Estación<span>88</span></a>
    </div>

    <button class="btn-menu88" id="btnMenu88">
        ☰
    </button>

    <nav class="menu88" id="menu88">
        <a href="https://www.estacion88.com/events">Carreras & Eventos</a>
        <a href="https://www.estacion88.com/nosotros">Nosotros</a>
        <a href="https://www.estacion88.com/contacto">Contacto</a>
    </nav>

</header>
</div>
<section class="container my-5">

    <div class="search-ticket-card">

        <div class="search-header">
            <h2>🔎 Consultar mi inscripción</h2>
            <p>Ingresa tu DNI para buscar tus tickets registrados</p>
        </div>

        <form action="buscar_ticket" method="GET" class="search-form">

            <div class="search-box">

                <input
                    type="text"
                    name="dni"
                    maxlength="8"
                    pattern="[0-9]{8}"
                    placeholder="Ingrese su DNI"
                    required>

                <button type="submit">
                    Buscar
                </button>

            </div>

        </form>

    </div>

</section>
<div class="container">

<h1 class="title">🎟 Eventos Disponibles</h1>
<p class="subtitle">
Descubre carreras, campeonatos y actividades organizadas.
Inscríbete en línea y recibe tu ticket digital automáticamente.
</p>
<div class="row text-center mb-5">

<div class="col-4">
<h3>1500+</h3>
<small>Participantes</small>
</div>

<div class="col-4">
<h3>25+</h3>
<small>Eventos</small>
</div>

<div class="col-4">
<h3>100%</h3>
<small>Digital</small>
</div>

</div>
<div class="row g-4">

<?php while($ev = mysqli_fetch_assoc($result)){ ?>

<div class="col-md-4">

    <div class="event-card">

        <!-- 🔵 CLICK A DETALLE -->
        <a href="evento?id=<?php echo $ev['id']; ?>" style="text-decoration:none;color:inherit;">

        <div class="event-img">

            <?php if(!empty($ev['imagen_portada'])){ ?>
                <img src="../uploads/<?php echo $ev['imagen_portada']; ?>" 
                     style="width:100%;height:100%;object-fit:cover;">
            <?php } else { ?>
                🎟 Evento
            <?php } ?>

        </div>

        <div class="event-body">

            <!-- BADGE -->
            <?php if($ev['estado'] == 'activo'){ ?>
                <span class="badge badge-activo">Activo</span>
            <?php } else { ?>
                <span class="badge badge-proximo">Próximo</span>
            <?php } ?>

            <h4 class="mt-2">
                <?= htmlspecialchars($ev['nombre'], ENT_QUOTES, 'UTF-8'); ?>
            </h4>

            <p style="font-size:14px;opacity:0.8;">
                <?= htmlspecialchars(substr($ev['descripcion'],0,90), ENT_QUOTES, 'UTF-8'); ?>...
            </p>

            <p>📅 <?= htmlspecialchars($ev['fecha_evento'], ENT_QUOTES, 'UTF-8'); ?></p>

        </div>

        </a>

        <!-- 🔴 BOTÓN INSCRIPCIÓN -->
        <a href="inscripcion?evento_id=<?php echo $ev['id']; ?>">
            <button class="btn-event">Inscribirme</button>
        </a>

    </div>

</div>

<?php } ?>

</div>

</div>
<script>

const btnMenu88 = document.getElementById('btnMenu88');
const menu88 = document.getElementById('menu88');

btnMenu88.addEventListener('click', () => {

    menu88.classList.toggle('active');

    btnMenu88.innerHTML =
        menu88.classList.contains('active')
        ? '✕'
        : '☰';

});

</script>
</body>
</html>