<?php
session_start();

include("sesion_check.php");
include("csrf.php");
include("../../db.php");

// 🔐 SOLO ADMIN PUEDE ENTRAR
if(!isset($_SESSION['id_admin']) || strtoupper($_SESSION['rol']) !== 'ADMIN'){
    die("Acceso denegado");
}

if(isset($_POST['guardar'])){
    
    validar_csrf($_POST['csrf_token']);

    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_evento = trim($_POST['fecha_evento']);
    $estado = trim($_POST['estado']);
    $distancia = trim($_POST['distancia']);
    
    // 📌 NUEVOS CAMPOS
    $detalles_evento = trim($_POST['detalles_evento']);
    $info_importante = trim($_POST['info_importante']);

    /* 🔥 IMAGEN */
    $imagen = "";
}

if(!empty($_FILES['imagen']['name'])){

    // Extensiones permitidas
    $permitidas = ['jpg','jpeg','png','webp'];

    $ext = strtolower(
        pathinfo(
            $_FILES['imagen']['name'],
            PATHINFO_EXTENSION
        )
    );

    if(!in_array($ext, $permitidas)){
        die("Solo se permiten imágenes JPG, JPEG, PNG o WEBP.");
    }

    // Validar MIME real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    $mime = finfo_file(
        $finfo,
        $_FILES['imagen']['tmp_name']
    );

    finfo_close($finfo);

    $mimePermitidos = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if(!in_array($mime, $mimePermitidos)){
        die("Archivo inválido.");
    }

    // Máximo 5 MB
    if($_FILES['imagen']['size'] > 5 * 1024 * 1024){
        die("La imagen supera los 5 MB.");
    }

    // Nombre aleatorio
    $imagen = uniqid('evento_', true) . "." . $ext;

    if(!move_uploaded_file(
    $_FILES['imagen']['tmp_name'],
    "../../uploads/" . $imagen
    )){
    die("Error al subir la imagen.");
    }

    /* 🔥 INSERT EVENTO */
    $stmt = $conn->prepare("
        INSERT INTO eventos
        (nombre, descripcion, fecha_evento, estado, distancia, imagen_portada, detalles_evento, info_importante)
        VALUES (?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "ssssssss",
        $nombre,
        $descripcion,
        $fecha_evento,
        $estado,
        $distancia,
        $imagen,
        $detalles_evento,
        $info_importante
    );

    if($stmt->execute()){

        $evento_id = $stmt->insert_id; // 🔥 ID del evento creado

        /* 🔥 INSERT KITS */
        if(!empty($_POST['kits_nombre'])){

            $kits_nombre = $_POST['kits_nombre'];
            $kits_precio = $_POST['kits_precio'];

            $stmtKit = $conn->prepare("
                INSERT INTO eventos_kits
                (evento_id, nombre_kit, precio)
                VALUES (?,?,?)
            ");

            for($i = 0; $i < count($kits_nombre); $i++){

                if(trim($kits_nombre[$i]) == '') continue;

                $nombre_kit = $kits_nombre[$i];
                $precio = floatval($kits_precio[$i]);

                $stmtKit->bind_param(
                    "isd",
                    $evento_id,
                    $nombre_kit,
                    $precio
                );

                $stmtKit->execute();
            }
        }

        header("Location: crear_evento?ok=1");
        exit;

    }else{
        die("Error al guardar evento: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Evento</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

    body{
    background: #f4f6f9;
    font-family: Arial;
    }

    .card{
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-top: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    h2{
    font-weight: 800;
    color:#1f2937;
    }

    label{
    font-weight: 600;
    color:#111827;
    margin-bottom:5px;
    }

    .form-control, .form-select{
    border-radius: 12px;
    padding: 10px;
    font-size: 14px;
    color:#111;
    }

    .form-control:focus{
    border-color:#22c55e;
    box-shadow:0 0 0 0.2rem rgba(34,197,94,0.15);
    }

    .kit-box{
    background:#f8fafc;
    padding:15px;
    border-radius:15px;
    margin-bottom:10px;
    border:1px solid #e5e7eb;
    }

    .btn-success{
    padding:12px;
    font-weight:700;
    border-radius:12px;
    }

    .btn-warning{
    font-weight:600;
    border-radius:12px;
    }

    hr{
    margin:25px 0;
    }
    .top-actions{
    display:flex;
    justify-content:space-between;
    margin-bottom:20px;
    gap:10px;
    }

    .btn-back,
.btn-logout{

    flex:1;
    text-align:center;

    padding:12px 18px;

    border-radius:14px;

    font-weight:700;
    font-size:14px;

    text-decoration:none;

    transition:all .25s ease;
}

/* =========================
   VOLVER
========================= */
.btn-back{

    background:linear-gradient(
        180deg,
        #ffffff,
        #f3f4f6
    );

    color:#1f2937;

    border:1px solid #d1d5db;

    box-shadow:
        0 4px 12px rgba(0,0,0,.06);
}

.btn-back:hover{

    color:#111827;
    text-decoration:none;

    transform:translateY(-2px);

    background:linear-gradient(
        180deg,
        #ffffff,
        #e5e7eb
    );

    box-shadow:
        0 8px 20px rgba(0,0,0,.10);
}

/* =========================
   SALIR
========================= */
.btn-logout{

    background:linear-gradient(
        135deg,
        #ff6b6b,
        #e31b23
    );

    color:white;

    border:1px solid rgba(227,27,35,.15);

    box-shadow:
        0 6px 16px rgba(227,27,35,.25);
}

.btn-logout:hover{

    color:white;
    text-decoration:none;

    transform:translateY(-2px);

    background:linear-gradient(
        135deg,
        #ff7b7b,
        #d91c24
    );

    box-shadow:
        0 10px 24px rgba(227,27,35,.35);
}
</style>
</head>

<body>

<div class="container">
    <div class="top-actions">

    <!-- 🔙 VOLVER -->
    <a href="dashboard" class="btn-back">
        🔙 Volver
    </a>

    <!-- 🚪 SALIR -->
    <a href="logout" class="btn-logout">
        🚪 Salir
    </a>

</div>

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card">

<h2>🎟 Crear Nuevo Evento</h2>

<?php if(isset($_GET['ok'])){ ?>
<div class="alert alert-success mt-3">
    Evento creado correctamente
</div>
<?php } ?>

<form method="POST" enctype="multipart/form-data" class="mt-3">
<input type="hidden"
       name="csrf_token"
       value="<?= $_SESSION['csrf_token'] ?>">
<!-- EVENTO -->
<div class="mb-3">
<label>Nombre del Evento</label>
<input type="text"
       name="nombre"
       class="form-control"
       required>
</div>

<div class="mb-3">
<label>Descripción</label>
<textarea name="descripcion" class="form-control" rows="5" required></textarea>
</div>
<div class="mb-3">
<label>📌 Detalles del evento  • @ # ✔ °</label>
<textarea name="detalles_evento" class="form-control" rows="8"></textarea>
</div>

<div class="mb-3">
<label>🎯 Información importante  • @ # ✔ °</label>
<textarea name="info_importante" class="form-control" rows="8"></textarea>
</div>
<div class="row">

<div class="col-md-6 mb-3">
<label>Fecha del Evento</label>
<input type="date" name="fecha_evento" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label>Distancia Base</label>
<select name="distancia" class="form-select">
    <option value="5K">5K</option>
    <option value="10K">10K</option>
    <option value="21K">21K</option>
</select>
</div>

</div>

<div class="mb-3">
<label>Estado</label>
<select name="estado" class="form-select" required>
    <option value="activo">Activo</option>
    <option value="inactivo">Inactivo</option>
</select>
</div>

<div class="mb-3">
<label>Imagen del Evento</label>
<input type="file" name="imagen" class="form-control">
</div>

<hr>

<h4 style="font-weight:700;">🎽 Kits del Evento</h4>

<div id="kits">

<div class="kit-box">
    <input type="text" name="kits_nombre[]" class="form-control mb-2" placeholder="Nombre del kit">
    <input type="number" name="kits_precio[]" class="form-control" placeholder="Precio">
</div>

</div>

<button type="button" class="btn btn-warning mt-2" onclick="addKit()">
➕ Agregar Kit
</button>

<hr>

<button type="submit" name="guardar" class="btn btn-success w-100">
🚀 Crear Evento
</button>

</form>

</div>

</div>

</div>

</div>

<script>
function addKit(){

    let div = document.createElement('div');
    div.classList.add('kit-box');

    div.innerHTML = `
        <input type="text" name="kits_nombre[]" class="form-control mb-2" placeholder="Nombre del kit">
        <input type="number" name="kits_precio[]" class="form-control" placeholder="Precio">
    `;

    document.getElementById('kits').appendChild(div);
}
</script>

</body>
</html>