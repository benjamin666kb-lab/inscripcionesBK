<?php
session_start();
include("session_check.php");
include("../../db.php");

// 🔐 SOLO ADMIN
if(!isset($_SESSION['id_admin'])){
    header("Location: login.php");
    exit;
}

// 🔥 ID DEL EVENTO
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();

if(!$evento){
    die("Evento no encontrado");
}

/* 📌 NUEVOS CAMPOS */
$detalles_evento = $evento['detalles_evento'];
$info_importante = $evento['info_importante'];

// 🔥 ACTUALIZAR EVENTO
if(isset($_POST['actualizar'])){

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $fecha_evento = $_POST['fecha_evento'];
    $estado = $_POST['estado'];
    $distancia = $_POST['distancia'];

    // 📌 NUEVOS CAMPOS
    $detalles_evento = $_POST['detalles_evento'];
    $info_importante = $_POST['info_importante'];

    // 🔥 IMAGEN
    $imagen = $evento['imagen_portada'];

    if(!empty($_FILES['imagen']['name'])){

        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen = time() . "." . $ext;

        move_uploaded_file($_FILES['imagen']['tmp_name'], "../uploads/" . $imagen);
    }

    $update = $conn->prepare("
        UPDATE eventos 
        SET nombre=?, descripcion=?, fecha_evento=?, estado=?, distancia=?, imagen_portada=?, detalles_evento=?, info_importante=?
        WHERE id=?
    ");

    $update->bind_param(
        "ssssssssi",
        $nombre,
        $descripcion,
        $fecha_evento,
        $estado,
        $distancia,
        $imagen,
        $detalles_evento,
        $info_importante,
        $id
    );

    if($update->execute()){
        header("Location: eventos_lista.php?editado=1");
        exit;
    } else {
        die("Error al actualizar: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Evento</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f4f6f9;
    font-family: Arial;
}

.card{
    background:white;
    border-radius:20px;
    padding:30px;
    margin-top:40px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

h2{
    font-weight:800;
}

label{
    font-weight:600;
}
.top-actions{
    display:flex;
    justify-content:space-between;
    margin-bottom:20px;
    gap:10px;
}

.btn-back, .btn-logout{
    flex:1;
    text-align:center;
    padding:10px;
    border-radius:50px;
    font-weight:700;
    text-decoration:none;
    transition:.3s;
    font-size:14px;
}

.btn-back{
    background:#e0f2f1;
    color:#00695c;
    border:1px solid #b2dfdb;
}

.btn-back:hover{
    background:#b2dfdb;
}

.btn-logout{
    background:#ffebee;
    color:#c62828;
    border:1px solid #ffcdd2;
}

.btn-logout:hover{
    background:#ffcdd2;
}
</style>
</head>

<body>
    <div class="top-actions">

    <!-- 🔙 VOLVER -->
    <a href="javascript:history.back()" class="btn-back">
        🔙 Volver
    </a>

    <!-- 🚪 SALIR -->
    <a href="logout.php" class="btn-logout">
        🚪 Salir
    </a>

</div>

<div class="container">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card">

<h2>✏️ Editar Evento</h2>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
<label>Nombre</label>
<input type="text" name="nombre" class="form-control"
value="<?php echo $evento['nombre']; ?>" required>
</div>

<div class="mb-3">
<label>Descripción</label>
<textarea name="descripcion" class="form-control" required><?php echo $evento['descripcion']; ?></textarea>
</div>
<div class="mb-3">
<label>📌 Detalles del evento</label>
<textarea name="detalles_evento" class="form-control" rows="4"><?php echo $detalles_evento; ?></textarea>
</div>

<div class="mb-3">
<label>🎯 Información importante</label>
<textarea name="info_importante" class="form-control" rows="4"><?php echo $info_importante; ?></textarea>
</div>

<div class="row">

<div class="col-md-6 mb-3">
<label>Fecha</label>
<input type="date" name="fecha_evento" class="form-control"
value="<?php echo $evento['fecha_evento']; ?>" required>
</div>

<div class="col-md-6 mb-3">
<label>Distancia</label>
<select name="distancia" class="form-control">
    <option value="5K" <?php if($evento['distancia']=="5K") echo "selected"; ?>>5K</option>
    <option value="10K" <?php if($evento['distancia']=="10K") echo "selected"; ?>>10K</option>
    <option value="21K" <?php if($evento['distancia']=="21K") echo "selected"; ?>>21K</option>
</select>
</div>

</div>

<div class="mb-3">
<label>Estado</label>
<select name="estado" class="form-control">
    <option value="activo" <?php if($evento['estado']=="activo") echo "selected"; ?>>Activo</option>
    <option value="inactivo" <?php if($evento['estado']=="inactivo") echo "selected"; ?>>Inactivo</option>
</select>
</div>

<div class="mb-3">
<label>Imagen actual</label><br>

<?php if(!empty($evento['imagen_portada'])){ ?>
    <img src="../uploads/<?php echo $evento['imagen_portada']; ?>" width="150">
<?php } ?>

</div>

<div class="mb-3">
<label>Cambiar imagen</label>
<input type="file" name="imagen" class="form-control">
</div>

<button type="submit" name="actualizar" class="btn btn-primary w-100">
💾 Guardar Cambios
</button>

</form>

</div>

</div>

</div>

</div>

</body>
</html>