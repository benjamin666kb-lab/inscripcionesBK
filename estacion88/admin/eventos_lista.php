<?php
session_start();
include("sesion_check.php");
include("csrf.php");
include("../../db.php");

if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}

$sql = "SELECT * FROM eventos ORDER BY fecha_evento DESC";
$result = $conn->query($sql);
?>
<?php if(isset($_GET['editado'])){ ?>
<div class="alert alert-success text-center fade-msg">
    🎉 Cambios guardados con éxito
</div>
<?php } ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de Eventos</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
   BODY (ESTACIÓN88 BASE REAL)
========================= */

body{
    background: linear-gradient(
        180deg,
        #571f1f 0%,
        #0f0f0f 100%
    );

    color: var(--texto);
    font-family:'Poppins',sans-serif;
}

/* =========================
   CONTAINER
========================= */

/* HEADER */
h2{
    font-weight:800;
    margin-bottom:25px;
}

/* CARD MODERNA */
.card-evento{
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius:18px;
    padding:18px 20px;
    margin-bottom:15px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    transition:.3s ease;
}

.card-evento:hover{
    transform: translateY(-3px);
    background: rgba(255,255,255,0.09);
}

/* INFO */
.card-evento h5{
    margin:0;
    font-weight:700;
}

.card-evento small{
    opacity:.8;
}

/* BADGE */
.badge-estado{
    display:inline-block;
    padding:4px 10px;
    border-radius:12px;
    font-size:12px;
    margin-left:8px;
    background:#22c55e;
    color:#000;
    font-weight:700;
}

/* BOTONES */
.btn-editar{
    background: linear-gradient(135deg,#3b82f6,#2563eb);
    color:white;
    padding:8px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    margin-right:6px;
    transition:.3s;
}

.btn-editar:hover{
    transform:scale(1.05);
    color:white;
}

.btn-eliminar{
    background: linear-gradient(135deg,#ef4444,#b91c1c);
    color:white;
    padding:8px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    border:none;
}

.btn-eliminar:hover{
    transform:scale(1.05);
    color:white;
}

/* CONTENEDOR */
.container{
    padding-top:30px;
}
.fade-msg{
    animation: fadeOut 4s forwards;
}

@keyframes fadeOut{
    0%{opacity:1;}
    80%{opacity:1;}
    100%{opacity:0; display:none;}
}
</style>
</head>

<body>

<div class="container">
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">

    <h2 style="margin:0;">🛠️ Eventos para administrar</h2>

    <a href="dashboard" 
       style="
            background: linear-gradient(135deg,#22c55e,#16a34a);
            color:white;
            padding:10px 18px;
            border-radius:12px;
            text-decoration:none;
            font-weight:700;
            transition:.3s;
       "
       onmouseover="this.style.transform='scale(1.05)'"
       onmouseout="this.style.transform='scale(1)'"
    >
        🏠 Dashboard
    </a>

</div>

<?php while($ev = $result->fetch_assoc()){ ?>

<div class="card-evento">

<div>
    <h5>
        <?php echo $ev['nombre']; ?>
        <span class="badge-estado">
            <?php echo $ev['estado']; ?>
        </span>
    </h5>

    <small>📅 <?php echo $ev['fecha_evento']; ?></small>
</div>

<div>
    <a href="editar_evento?id=<?php echo $ev['id']; ?>" class="btn-editar">
        ✏ Editar
    </a>

    <form method="POST"
      action="eliminar_evento"
      style="display:inline;">

    <input
        type="hidden"
        name="id"
        value="<?php echo $ev['id']; ?>">

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo $_SESSION['csrf_token']; ?>">

    <button
        type="submit"
        class="btn-eliminar"
        onclick="return confirm('¿Seguro que deseas eliminar este evento?');">

        🗑 Eliminar

    </button>

</form>
</div>

</div>

<?php } ?>

</div>

</body>
</html>