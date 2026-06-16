<?php

session_start();
include("sesion_check.php");
if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}
include("csrf.php");
include("../../db.php");

// 🔐 ROLES NORMALIZADOS
$rol = strtoupper($_SESSION['rol']);

$esAdmin = ($rol === "ADMIN");
$esOperador = ($rol === "OPERADOR");
$esLector = ($rol === "LECTOR");

// 📦 CONSULTA
$sql = "
SELECT *
FROM inscritos
ORDER BY id DESC
";

$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Inscritos - Estación 88</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

    *{
    font-family:'Poppins',sans-serif;
    }

    body{
    background:#f5f6fa;
    }
    .topbar{
    background:
    linear-gradient(
        90deg,
        #000000 0%,        
        #e31b23 50%,        
        #198754 100%
    );

    color:white;

    padding:22px 35px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    border-bottom:3px solid rgba(255,255,255,.1);

    box-shadow:
        0 5px 20px rgba(0,0,0,.35);
    }
    .topbar h1,
    .topbar h2,
    .topbar h3{
    margin:0;
    font-size:2rem;
    font-weight:900;
    letter-spacing:1px;
    color:#fff;
    }
    .container-fluid{
    max-width: 1200px;
    }

    .card{
    border:none;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    }

    .titulo{
    font-weight:800;
    font-size:25px;
    }

    table{
    font-size:14px;
    }

    .table td,
    .table th{
    padding:4px 6px !important;
    vertical-align: middle;
    }

    .badge{
    font-size:11px;
    padding:4px 6px;
    }

    .btn-sm{
    font-size:11px;
    padding:3px 8px;
    }

    .card-body{
    padding:10px;
    }
    .fade-msg{
    animation: fadeOut 4s forwards;
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
<div class="topbar">

<div>
🏃 Estación88 Admin
</div>

<div>
<?php echo $_SESSION['nombre']; ?> |
<?php echo $_SESSION['rol']; ?> |

<a href="logout" class="btn btn-light btn-sm">
Salir
</a>

</div>

</div>
<div class="container-fluid mt-4">
<?php if(isset($_GET['msg'])){ ?>

<div class="alert alert-success text-center fade-msg">

<?php
switch($_GET['msg']){

    case 'eliminado':
        echo "🗑 Inscrito eliminado correctamente";
        break;

    case 'editado':
        echo "✏ Inscrito actualizado correctamente";
        break;

    case 'creado':
        echo "✅ Inscrito registrado correctamente";
        break;
}
?>

</div>

<?php } ?>
<div class="d-flex justify-content-between align-items-center mb-4">

    <h2 class="titulo">
        👥 Inscritos Estacion88
    </h2>

    <a href="dashboard" class="btn btn-success">
        ← Dashboard
    </a>

</div>

<div class="card">

<div class="card-body">

<div class="table-responsive">

<table id="tablaInscritos" class="table table-striped table-hover align-middle">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Código</th>
<th>Nombre</th>
<th>DNI</th>
<th>Teléfono</th>
<th>Correo</th>
<th>Edad</th>
<th>Distancia</th>
<th>Talla</th>
<th>Categoría</th>
<th>Kit</th>
<th>Monto</th>
<th>Estado</th>
<th>Fecha</th>
<th>club</th>
<th>Acciones</th>
</tr>

</thead>

<tbody>

<?php while($row = $resultado->fetch_assoc()){ ?>

<tr>

<td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['dni'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['correo'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['edad'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['distancia'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?= htmlspecialchars($row['talla'], ENT_QUOTES, 'UTF-8'); ?></td>

<td>
    <?php if(!empty($row['categoria'])){ ?>
        <?= htmlspecialchars($row['categoria'], ENT_QUOTES, 'UTF-8'); ?>
    <?php } ?>
</td>

<td><?= htmlspecialchars($row['kit'], ENT_QUOTES, 'UTF-8'); ?></td>

<td>S/ <?= number_format((float)$row['monto'],2); ?></td>

<td>
<?php
$estado = strtoupper(trim($row['estado_pago'] ?? 'LIBRE'));

if($estado === "PAGADO"){ ?>
    <span class="badge bg-success">PAGADO</span>
<?php } elseif($estado === "PENDIENTE"){ ?>
    <span class="badge bg-warning text-dark">PENDIENTE</span>
<?php } else { ?>
    <span class="badge bg-primary">LIBRE</span>
<?php } ?>
</td>

<td><?= htmlspecialchars($row['fecha_registro'], ENT_QUOTES, 'UTF-8'); ?></td>

<td><?= htmlspecialchars($row['club_equipo'], ENT_QUOTES, 'UTF-8'); ?></td>

<td>

<?php if($esLector){ ?>

    <span class="badge bg-secondary">Solo lectura</span>
    <!-- VER -->
    <a href="detalle_inscrito?id=<?= $row['id'] ?>" class="btn btn-info btn-sm mb-1">
        Ver
    </a>

<?php } else { ?>

    <!-- VER -->
    <a href="detalle_inscrito.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm mb-1">
        Ver
    </a>

    <?php if($esOperador || $esAdmin){ ?>

        <!-- EDITAR -->
        <a href="editar_inscrito.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mb-1">
            Editar
        </a>

    <?php } ?>

    <?php if($esAdmin){ ?>

        <!-- ELIMINAR -->
        <form method="POST" 
      action="eliminar_inscrito"
      style="display:inline;">

    <input
        type="hidden"
        name="id"
        value="<?php echo $row['id']; ?>">  

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo $_SESSION['csrf_token']; ?>">

    <button
        type="submit"
        class="btn btn-danger btn-sm mb-1"
        onclick="return confirm('¿Seguro que deseas eliminar este inscrito?');">
        Eliminar

    </button>

</form>

    <?php } ?>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>

$(document).ready(function(){

$('#tablaInscritos').DataTable({

language:{
url:'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
},

pageLength:10

});

});

</script>

</body>
</html>