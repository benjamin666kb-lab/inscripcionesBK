<?php
include("sesion_check.php");
include("../../db.php");

$ids = $_SESSION['whatsapp_ids'] ?? [];
unset($_SESSION['whatsapp_ids']);

if(empty($ids)){
    header("Location: inscritos.php");
    exit;
}

/* =========================
   CONSULTA SEGURA IN
========================= */
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "SELECT id, nombre, telefono, codigo FROM inscritos WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);

$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);

$stmt->execute();
$result = $stmt->get_result();

/* =========================
   LINK WEB
========================= */
$web = "https://inscripcionesbk.free.nf/estacion88/";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Notificar WhatsApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>📲 Notificar WhatsApp a Inscritos</h3>

<div class="alert alert-info">
Selecciona y envía mensajes manualmente (sin bloqueos del navegador)
</div>

<!-- BOTÓN ENVIAR TODOS -->
<?php
$all_messages = [];

while($row = $result->fetch_assoc()){

    $telefono = preg_replace('/[^0-9]/','',$row['telefono']);

    if(substr($telefono,0,1)=='9'){
        $telefono = "51".$telefono;
    }

    $mensaje = "Hola {$row['nombre']}, tu inscripción fue CONFIRMADA. Código: {$row['codigo']}. Puedes buscar tus tickets aquí: $web usando tu DNI.";

    $all_messages[] = "https://wa.me/$telefono?text=" . urlencode($mensaje);

    $rows[] = [
        'nombre' => $row['nombre'],
        'telefono' => $row['telefono'],
        'url' => "https://wa.me/$telefono?text=" . urlencode($mensaje)
    ];
}
?>

<a class="btn btn-primary mb-3" target="_blank"
   href="https://wa.me/?text=<?= urlencode('Hola 👋 tus inscripciones fueron confirmadas. Puedes buscar tus tickets en nuestra web usando tu DNI: ' . $web) ?>">
    📲 Enviar mensaje general
</a>

<table class="table table-bordered">

<thead>
<tr>
    <th>Nombre</th>
    <th>Teléfono</th>
    <th>Acción</th>
</tr>
</thead>

<tbody>

<?php foreach($rows as $r){ ?>

<tr>
    <td><?= htmlspecialchars($r['nombre']) ?></td>
    <td><?= htmlspecialchars($r['telefono']) ?></td>
    <td>
        <a class="btn btn-success btn-sm" target="_blank" href="<?= $r['url'] ?>">
            Enviar WhatsApp
        </a>
    </td>
</tr>

<?php } ?>

</tbody>

</table>

<a href="inscritos.php" class="btn btn-secondary">← Volver</a>

</body>
</html>