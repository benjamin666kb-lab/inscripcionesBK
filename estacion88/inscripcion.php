<?php
include("admin/csrf.php");
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

:root{
    --rojo:#e31b23;
    --rojo-hover:#ff3038;
    --negro:#000000;
    --gris:#f5f5f5;
    --texto:#222;
}

/* =========================
   GENERAL
========================= */

*{
    font-family:'Poppins',sans-serif;
    box-sizing:border-box;
}

body{
    background:
    linear-gradient(
        180deg,
        #181818 0%,
        #111111 100%
    );

    min-height:100vh;
    padding:50px 0;
}

/* =========================
   TARJETA PRINCIPAL
========================= */

.card-evento{

    background:white;

    border-radius:24px;

    overflow:hidden;

    box-shadow:
    0 15px 50px rgba(0,0,0,.35);
}

/* =========================
   CABECERA
========================= */

.header-evento{

    background:
    linear-gradient(
        135deg,
        #e31b23,
        #c1121f
    );

    color:white;

    padding:45px 35px;

    text-align:center;
}

.header-evento h1{

    font-size:clamp(2rem,5vw,3.5rem);

    font-weight:900;

    margin-bottom:10px;
}

.header-evento p{

    font-size:1rem;

    opacity:.95;

    margin-bottom:0;
}

/* =========================
   FORMULARIO
========================= */

.formulario{
    padding:40px;
}

/* =========================
   LABELS
========================= */

.form-label{
    font-weight:700;
    color:#222;
}

/* =========================
   INPUTS
========================= */

.form-control,
.form-select{

    border:2px solid #e5e5e5;

    border-radius:12px;

    padding:13px 15px;

    transition:.3s;
}

.form-control:focus,
.form-select:focus{

    border-color:var(--rojo);

    box-shadow:
    0 0 0 .15rem rgba(227,27,35,.15);

}

/* =========================
   BOTON
========================= */

.btn-inscribirse{

    background:var(--rojo);
    border:none;
    width:100%;
    padding:16px;
    font-size:1.1rem;
    font-weight:700;
    border-radius:50px;
    transition:.3s;
    color:white;
}

.btn-inscribirse:hover{

    background:var(--rojo-hover);
    transform:translateY(-2px);
    box-shadow:
    0 10px 25px rgba(227,27,35,.30);
}

/* =========================
   BADGE
========================= */

.badge-evento{

    display:inline-flex;
    align-items:center;
    gap:8px;
    background:#fff;
    color:#e31b23;
    padding:12px 22px;
    border-radius:50px;
    font-size:14px;
    font-weight:700;
    border:2px solid rgba(227,27,35,.15);
    box-shadow:
    0 4px 15px rgba(0,0,0,.08);
    margin-top:15px;
    transition:.3s;
}

.badge-evento:hover{

    transform:translateY(-2px);

    box-shadow:
    0 8px 20px rgba(227,27,35,.15);
}

/* =========================
   CAJAS INFO
========================= */

.info-box{

    background:#ffffff;

    border-left:6px solid var(--rojo);

    padding:20px;

    border-radius:14px;

    margin-bottom:25px;

    box-shadow:
    0 5px 15px rgba(0, 0, 0, 0.25);
}

/* =========================
   BOTON VOLVER
========================= */

.btn-volver{

    position:fixed;

    top:20px;
    left:20px;

    background:
    rgba(0,0,0,.65);

    color:white;

    padding:10px 16px;

    border-radius:50px;

    text-decoration:none;

    font-size:13px;

    font-weight:700;

    border:
    1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(8px);

    transition:.3s;

    z-index:999;
}

.btn-volver:hover{

    background:var(--rojo);

    color:white;

    transform:translateY(-2px);
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:768px){

    body{
        padding:20px 10px;
    }

    .header-evento{
        padding:35px 20px;
    }

    .formulario{
        padding:25px 20px;
    }

    .header-evento h1{
        font-size:2rem;
    }
    }
.consent-box{

    background:#fff8e1;

    border:1px solid #ffe082;

    border-left:5px solid #f59e0b;

    border-radius:14px;

    padding:15px;

    margin-top:15px;
}

.consent-title{

    font-size:14px;

    font-weight:700;

    color:#92400e;

    margin-bottom:10px;
}

.consentimiento-texto{

    font-size:13px;

    line-height:1.5;

    color:#4b5563;
}

.form-check-input{

    margin-top:4px;
}
</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card-evento">

<div class="header-evento">

<a href="https://www.estacion88.com/" style="text-decoration:none; color:inherit;">
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
<!-- 🔥 MENSAJE DE ERROR -->
<?php
session_start();
if(isset($_SESSION['error'])){
?>
<div class="alert alert-danger">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php } ?>
<div class="info-box">

<h5>🎯 Completa tu inscripción</h5>

<p class="mb-0">
Llena tus datos correctamente para participar oficialmente en el evento.
</p>

</div>

<form action="guardar_inscripcion" method="POST">
<input
    type="hidden"
    name="csrf_token"
    value="<?= $_SESSION['csrf_token']; ?>">
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
<!--MENSAJED DE ALERTA!!--><!--MENSAJED DE ALERTA!!--><!--MENSAJED DE ALERTA!!-->
<div id="mensaje-alerta" style="color:red;font-weight:600;margin-bottom:10px;"></div>

<div class="col-12">

<a
id="btn-ticket"
href="#"
class="btn btn-primary mt-3 w-100"
style="display:none; border-radius:50px; padding:15px; font-weight:700;">
🎫 VER MI TICKET
</a>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Correo Electrónico</label>
<input type="email" name="correo" class="form-control" required>
</div>
<!-- 🔥 GRUPO_CLUB -->
<div class="mb-3">
    <label class="form-label">
        🏃 Nombre del Club - Team - Grupo - Colectivo (si pertenece)
    </label>

    <input
        type="text"
        name="club_equipo"
        class="form-control"
        maxlength="255"
        placeholder="Ejemplo: Team Estacion88 Running">
</div>

<!-- 🔥 KITS DINÁMICOS -->
<div class="col-12 mb-4">

<label class="form-label">Tipo de Inscripción (Kit)</label>

<select id="kit_id" name="kit_id" class="form-select" required>

<option value="">Seleccione un kit</option>

<?php while($k = $kits->fetch_assoc()){ ?>

<option value="<?php echo $k['id']; ?>"
data-precio="<?php echo $k['precio']; ?>">
<?php echo $k['nombre_kit']; ?> (S/ <?php echo $k['precio']; ?>)
</option>

<?php } ?>

</select>

</div>
<!-- 🔥 CATEGORIAS -->
<div class="col-md-4 mb-3">
<label class="form-label">Categoría</label>
<select name="categoria" class="form-select" required>
<option value="">Seleccione</option>
<option value="adolescente">Adolescentes (14 +) TEEN</option>
<option value="joven">Joven (18 +)</option>
<option value="master">Master (40 +)</option>
<option value="super_master">Super Master (50 +)</option>
</select>
</div>

<!-- 🔥 EDAD -->
<div class="col-md-4 mb-3">
<label class="form-label">Edad</label>
<input type="number" name="edad" min="1" max="100" class="form-control" required>
</div>
<!-- 🔥 DISTANCIA  -->
<div class="col-md-4 mb-3">
<label class="form-label">Distancia</label>
<select name="distancia" class="form-select" required>
<option value="">Seleccione</option>
<option value="5K">🏃 5K</option>
<option value="10K">🔥 10K</option>
<option value="15K">🔥 15K</option>
<option value="20K">🔥🏃 20K</option>
</select>
</div>
<!-- 🔥 TALLA -->
<div class="col-md-4 mb-3">
<label class="form-label">Talla Polo</label>
<select id="talla" name="talla" class="form-select" required>
<option value="">Seleccione</option>
<option>S</option>
<option>M</option>
<option>L</option>
<option>XL</option>
</select>
</div>

</div>
<!-- 🔥 CONSENTIMIENTO -->
<div class="consent-box">

    <div class="consent-title">
        ⚠️ Declaración de Responsabilidad
    </div>

    <div class="form-check">

        <input
            class="form-check-input"
            type="checkbox"
            name="acepta_responsabilidad"
            value="1"
            id="acepta_responsabilidad"
            required>

        <label class="form-check-label consentimiento-texto"
               for="acepta_responsabilidad">

            Al participar en este evento, el o la participante declara
            encontrarse en buen estado de salud y asumir bajo su propia
            responsabilidad los riesgos inherentes a la participación,
            liberando de responsabilidad al organizador ante cualquier
            incidente no atribuible a su gestión.

        </label>

    </div>

</div>
<br>
<div class="col-12">

<button type="submit" class="btn-inscribirse" id="btn-submit">
🚀 CONTINUAR INSCRIPCIÓN
</button>
</div>

</form>

<div class="info-box">
<strong>Información del evento</strong><br>
Tu inscripción será validada automáticamente y recibirás un ticket digital con código QR.
</div>
</div>

</div>

</div>

</div>

</div>
<script>

let timeout = null;

function verificarInscripcion(){

    clearTimeout(timeout);

    timeout = setTimeout(() => {

        let dni = document.querySelector('input[name="dni"]').value.trim();
        let telefono = document.querySelector('input[name="telefono"]').value.trim();
        let evento_id = document.querySelector('input[name="evento_id"]').value;

        if(dni.length < 6 && telefono.length < 6){
            return;
        }

        fetch("verificar_dni_celular", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "dni="+dni+"&telefono="+telefono+"&evento_id="+evento_id
        })
        .then(res => res.text())
        .then(data => {

            let msg = document.getElementById("mensaje-alerta");
            let btn = document.getElementById("btn-submit")

            let partes = data.trim().split("|");

if(partes[0] === "EXISTE"){

    let codigo = partes[1];

    msg.innerHTML =
        "⚠️ Ya tienes un ticket creado para este evento.";

    btn.disabled = true;
    btn.style.opacity = "0.5";

    let btnTicket = document.getElementById("btn-ticket");

    btnTicket.style.display = "block";

    btnTicket.href =
        "ticket?codigo=" + codigo;


            }else{
                msg.innerHTML = "";
                btn.disabled = false;
                btn.style.opacity = "1";
                document.getElementById("btn-ticket").style.display = "none";
            }
        });

    }, 400); // 🔥 debounce 400ms
}

// esperar a que cargue todo el DOM
window.addEventListener("DOMContentLoaded", function(){

    document.querySelector('input[name="dni"]').addEventListener("input", verificarInscripcion);
    document.querySelector('input[name="telefono"]').addEventListener("input", verificarInscripcion);

});

</script>
<script>

function validarKitGratis(){

    const kit = document.getElementById("kit_id");
    const talla = document.getElementById("talla");

    const opcionSeleccionada =
        kit.options[kit.selectedIndex];

    const precio =
        parseFloat(
            opcionSeleccionada.dataset.precio || 0
        );

    if(precio === 0){

        talla.value = "";
        talla.disabled = true;
        talla.required = false;

    }else{

        talla.disabled = false;
        talla.required = true;

    }
}

document.addEventListener("DOMContentLoaded", () => {

    document
        .getElementById("kit_id")
        .addEventListener("change", validarKitGratis);

    validarKitGratis();

});

</script>
</body>
<a href="javascript:history.back()" class="btn-volver">
← Volver
</a>
</html>