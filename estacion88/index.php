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
body{
    background: linear-gradient(135deg,#0f172a,#1e293b);
    color:white;
    font-family: Arial;
}

.container{
    margin-top:50px;
}

.title{
    text-align:center;
    margin-bottom:40px;
    font-weight:800;
}

/* CARD */
.event-card{
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px;
    overflow:hidden;
    transition:0.3s;
    backdrop-filter: blur(10px);
    cursor:pointer;
}

.event-card:hover{
    transform: translateY(-8px);
    border: 1px solid #38bdf8;
}

/* IMAGEN */
.event-img{
    height:180px;
    background:#111;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

/* CUERPO */
.event-body{
    padding:20px;
}

/* BADGES */
.badge-activo{
    background:#22c55e;
}

.badge-proximo{
    background:#64748b;
}

/* BOTÓN */
.btn-event{
    background:#38bdf8;
    border:none;
    width:100%;
    padding:10px;
    border-radius:10px;
    color:black;
    font-weight:bold;
    transition:0.2s;
}

.btn-event:hover{
    background:#0ea5e9;
}
</style>
</head>

<body>
<section class="container my-5">

    <div class="card shadow border-0 rounded-4">

        <div class="card-body p-4 text-center">

            <h2 class="fw-bold mb-3">
                🔎 Consultar mi inscripción
            </h2>

            <p class="text-muted">
                Ingresa tu DNI para buscar tus tickets registrados.
            </p>

            <form action="buscar_ticket.php" method="GET">

                <div class="row justify-content-center">

                    <div class="col-md-6">

                        <input
                            type="text"
                            name="dni"
                            class="form-control form-control-lg"
                            maxlength="8"
                            pattern="[0-9]{8}"
                            placeholder="Ingrese su DNI"
                            required>

                    </div>

                    <div class="col-md-2 mt-3 mt-md-0">

                        <button
                            type="submit"
                            class="btn btn-success btn-lg w-100">

                            Buscar

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</section>
<div class="container">

<h1 class="title">🎟 Eventos Disponibles</h1>

<div class="row g-4">

<?php while($ev = mysqli_fetch_assoc($result)){ ?>

<div class="col-md-4">

    <div class="event-card">

        <!-- 🔵 CLICK A DETALLE -->
        <a href="evento.php?id=<?php echo $ev['id']; ?>" style="text-decoration:none;color:inherit;">

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
                <?php echo $ev['nombre']; ?>
            </h4>

            <p style="font-size:14px;opacity:0.8;">
                <?php echo substr($ev['descripcion'],0,90); ?>...
            </p>

            <p>📅 <?php echo $ev['fecha_evento']; ?></p>

        </div>

        </a>

        <!-- 🔴 BOTÓN INSCRIPCIÓN -->
        <a href="inscripcion.php?evento_id=<?php echo $ev['id']; ?>">
            <button class="btn-event">Inscribirme</button>
        </a>

    </div>

</div>

<?php } ?>

</div>

</div>

</body>
</html>