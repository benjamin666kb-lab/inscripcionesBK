<?php

include("../db.php");
include("admin/rate_limit.php");

// Máximo 20 búsquedas por minuto
verificarRateLimit($conn, "buscar_ticket", 25, 60);

$dni = trim($_GET['dni'] ?? '');

if(empty($dni)){
    die("DNI no válido");
}
// Registrar búsqueda válida
registrarIntento($conn, "buscar_ticket");
$sql = "
SELECT
    id,
    codigo,
    nombre,
    dni,
    estado_pago,
    kit,
    distancia,
    fecha_registro
FROM inscritos
WHERE dni = ?
ORDER BY fecha_registro DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();

$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Mis Inscripciones</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>

*{
    font-family:'Poppins',sans-serif;
}

/* =========================
   FONDO GENERAL (ESTACIÓN88)
========================= */

body{
    background:linear-gradient(135deg,#0f172a,#1e293b);
    min-height:100vh;
    padding:40px 0;
}

/* =========================
   CARD PRINCIPAL
========================= */

.card-busqueda{

    background:var(--gris-card);
    border-radius:25px;
    overflow:hidden;

    border:1px solid rgba(255,255,255,.08);

    box-shadow:0 20px 50px rgba(0,0,0,.35);

    transition:.3s;
}

.card-busqueda:hover{
    transform:translateY(-4px);
    border-color:rgba(227,27,35,.35);
}

/* =========================
   HEADER (IDENTIDAD ROJA EST88)
========================= */

.header-card{

    background:linear-gradient(135deg,#e31b23,#b31217);
    color:white;

    text-align:center;
    padding:35px;

    position:relative;
    overflow:hidden;
}

/* brillo sutil */
.header-card::after{
    content:"";
    position:absolute;
    top:0;
    left:-50%;
    width:200%;
    height:100%;
    background:rgba(255,255,255,.05);
    transform:skewX(-20deg);
}

.header-card h1{
    margin:0;
    font-weight:900;
}

.header-card p{
    margin-top:8px;
    opacity:.9;
}

/* =========================
   CONTENIDO
========================= */

.contenido{
    padding:30px;
}

/* =========================
   TABLA
========================= */

.table{
    vertical-align:middle;
    margin-bottom:0;
    color:white;
}

/* encabezado tabla */
thead{
    background:#111827;
    color:white;
}

/* filas más limpias */
tbody tr{
    border-color:rgba(255,255,255,.06);
}

/* =========================
   BADGES (ESTILO SISTEMA EST88)
========================= */

.badge-pagado{
    background:rgba(34,197,94,.15);
    color:#22c55e;
    padding:8px 12px;
    border-radius:20px;
    font-weight:700;
}

.badge-pendiente{
    background:rgba(234,179,8,.15);
    color:#eab308;
    padding:8px 12px;
    border-radius:20px;
    font-weight:700;
}

.badge-libre{
    background:rgba(59,130,246,.15);
    color:#3b82f6;
    padding:8px 12px;
    border-radius:20px;
    font-weight:700;
}

/* =========================
   BOTONES (IDENTIDAD ROJA)
========================= */

.btn-ticket{
    border-radius:30px;
    font-weight:700;
    background:#e31b23; /* rojo fuerte visible */
    border:none;
    color:white;
    transition:.3s;
    box-shadow:0 8px 20px rgba(227,27,35,0.35);
}

.btn-ticket:hover{
    background:var(--rojo-hover);
    transform:translateY(-2px);
}

.btn-volver{
    border-radius:30px;
    font-weight:600;
    border:1px solid rgba(241, 16, 16, 0.8);
    color:white;
    background:rgba(16, 205, 230, 0.92);;
    transition:.3s;
}

.btn-volver:hover{
    background:rgba(255,255,255,.08);
}

/* =========================
   EFECTO SUAVE TABLA
========================= */

tbody tr:hover{
    background:rgba(255,255,255,.03);
}
.swipe-hint{
    color:#fff;
    background:rgba(27, 197, 227, 0.38);
    border:1px solid rgba(227,27,35,.35);

    padding:10px;
    border-radius:12px;

    text-align:center;
    font-size:13px;
    font-weight:600;

    margin-bottom:10px;

    animation:pulseHint 2s infinite;
    transition:
        opacity .8s ease,
        transform .8s ease;

    opacity:1;
    transform:translateY(0);
}
.swipe-hint.ocultar{
    opacity:0;
    transform:translateY(-10px);
}
@keyframes pulseHint{
    0%,100%{
        opacity:1;
    }
    50%{
        opacity:.5;
    }
}

</style>

</head>
<body>

<div class="container">

<div class="row justify-content-center">

<div class="col-lg-10">

<div class="card-busqueda">

<div class="header-card">

<h1>🎫 Mis Inscripciones</h1>

<p>
Resultados encontrados para el DNI:
<strong><?= htmlspecialchars($dni) ?></strong>
</p>

</div>

<div class="contenido">

<?php if($resultado->num_rows == 0){ ?>

<div class="alert alert-warning">
No se encontraron inscripciones para ese DNI.
</div>


<?php } else { ?>
<div class="swipe-hint d-md-none">
👉 Desliza la tabla hacia la izquierda para ver más información
</div>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>
<th>Código</th>
<th>Nombre</th>
<th>Estado</th>
<th>Distancia</th>
<th>Kit</th>
<th>Acción</th>
</tr>

</thead>

<tbody>

<?php while($row = $resultado->fetch_assoc()){ ?>

<tr>

<td><?= htmlspecialchars($row['codigo']) ?></td>

<td><?= htmlspecialchars($row['nombre']) ?></td>

<td>

<?php
$estado = strtoupper(trim($row['estado_pago']));

if($estado == "PAGADO"){
    echo '<span class="badge-pagado">PAGADO</span>';
}
elseif($estado == "PENDIENTE"){
    echo '<span class="badge-pendiente">PENDIENTE</span>';
}
else{
    echo '<span class="badge-libre">LIBRE</span>';
}
?>

</td>

<td><?= htmlspecialchars($row['distancia']) ?></td>

<td><?= htmlspecialchars($row['kit']) ?></td>

<td>

<a
href="ticket.php?codigo=<?= urlencode($row['codigo']) ?>"
class="btn btn-primary btn-ticket">

Ver Ticket

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php } ?>

<a href="index.php" class="btn btn-secondary btn-volver mt-3">
⬅ Volver
</a>

</div>
</div>
</div>
</div>
</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", ()=>{

    const tabla = document.querySelector(".table-responsive");
    const mensaje = document.querySelector(".swipe-hint");

    if(tabla && mensaje){

        let ocultado = false;

        tabla.addEventListener("scroll", ()=>{

            if(tabla.scrollLeft > 20 && !ocultado){

                ocultado = true;

                mensaje.classList.add("ocultar");

                setTimeout(()=>{
                    mensaje.remove();
                },800);

            }

        });

    }

});
</script>

</body>
</html>