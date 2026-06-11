<?php

include("../db.php");

$dni = trim($_GET['dni'] ?? '');

if(empty($dni)){
    die("DNI no válido");
}

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

</head>
<body>

<div class="container mt-5">

<h2 class="mb-4">
🎫 Resultado de búsqueda
</h2>

<?php if($resultado->num_rows == 0){ ?>

<div class="alert alert-warning">
No se encontraron inscripciones para ese DNI.
</div>

<?php } else { ?>

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

<td><?= htmlspecialchars($row['estado_pago']) ?></td>

<td><?= htmlspecialchars($row['distancia']) ?></td>

<td><?= htmlspecialchars($row['kit']) ?></td>

<td>

<a
href="ticket.php?codigo=<?= urlencode($row['codigo']) ?>"
class="btn btn-primary btn-sm">

Ver Ticket

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php } ?>

<a href="index.php" class="btn btn-secondary mt-3">
⬅ Volver
</a>

</div>

</body>
</html>