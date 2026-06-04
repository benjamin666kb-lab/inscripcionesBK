<?php

session_start();
include("sesion_check.php");
if(!isset($_SESSION['id_admin'])){
    header("Location: login.php");
    exit;
}

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

</style>

</head>

<body>

<div class="container-fluid mt-4">

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2 class="titulo">
        👥 Inscritos Estacion88
    </h2>

    <a href="dashboard.php" class="btn btn-success">
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
<th>Acciones</th>
</tr>

</thead>

<tbody>

<?php while($row = $resultado->fetch_assoc()){ ?>

<tr>

<td><?= $row['id']; ?></td>
<td><?= $row['codigo']; ?></td>
<td><?= $row['nombre']; ?></td>
<td><?= $row['dni']; ?></td>
<td><?= $row['telefono']; ?></td>
<td><?= $row['correo']; ?></td>
<td><?= $row['edad']; ?></td>
<td><?= $row['distancia']; ?></td>
<td><?= $row['talla']; ?></td>
<td>
    <?php if(!empty($row['categoria'])){ ?>
        <?= $row['categoria']; ?>
    <?php } ?>
</td>
<td><?= $row['kit']; ?></td>

<td>S/ <?= number_format($row['monto'],2); ?></td>

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

<td><?= $row['fecha_registro']; ?></td>

<td>

<?php if($esLector){ ?>

    <span class="badge bg-secondary">Solo lectura</span>
    <!-- VER -->
    <a href="detalle_inscrito.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm mb-1">
        Ver
    </a>

<?php } else { ?>

    <!-- VER -->
    <a href="detalle_inscrito.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm mb-1">
        Ver
    </a>

    <?php if($esOperador || $esAdmin){ ?>

        <!-- PAGAR -->
        <a href="marcar_pagado.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm mb-1">
            Pagado
        </a>

        <!-- EDITAR -->
        <a href="editar_inscrito.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mb-1">
            Editar
        </a>

    <?php } ?>

    <?php if($esAdmin){ ?>

        <!-- ELIMINAR -->
        <a href="eliminar_inscrito.php?id=<?= $row['id'] ?>"
        class="btn btn-danger btn-sm mb-1"
        onclick="return confirm('¿Deseas eliminar este inscrito?')">
            Eliminar
        </a>

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