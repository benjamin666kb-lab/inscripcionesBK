<?php
session_start();
include("../db.php");
include("admin/csrf.php");

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Método no permitido");
}

validar_csrf($_POST['csrf_token'] ?? '');

// 🔥 DATOS DEL FORMULARIO
$evento_id = (int) ($_POST['evento_id'] ?? 0);
$kit_id    = (int) ($_POST['kit_id'] ?? 0);

$nombre    = trim($_POST['nombre'] ?? '');
$dni       = trim($_POST['dni'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$correo    = trim($_POST['correo'] ?? '');

$club_equipo = trim($_POST['club_equipo'] ?? '');
$acepta_responsabilidad = isset($_POST['acepta_responsabilidad']) ? 1 : 0;

$edad      = (int) ($_POST['edad'] ?? 0);
$distancia = trim($_POST['distancia'] ?? '');
$talla     = trim($_POST['talla'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');

// 🔥 VALIDAR KIT
$kit_query = $conn->prepare("
    SELECT nombre_kit, precio 
    FROM eventos_kits 
    WHERE id = ? 
    AND evento_id = ?
    LIMIT 1
");

$kit_query->bind_param("ii", $kit_id, $evento_id);
$kit_query->execute();

$result = $kit_query->get_result();

if(!$result || $result->num_rows == 0){
    die("Kit no válido");
}

$kit_data = $result->fetch_assoc();

$kit   = $kit_data['nombre_kit'];
$monto = $kit_data['precio'];

// 🔥 OBTENER NOMBRE DEL EVENTO
$evento_query = $conn->prepare("
    SELECT nombre 
    FROM eventos 
    WHERE id = ?
    LIMIT 1
");

$evento_query->bind_param("i", $evento_id);
$evento_query->execute();

$result_evento = $evento_query->get_result();

if(!$result_evento || $result_evento->num_rows == 0){
    die("Evento no válido");
}

$evento_data = $result_evento->fetch_assoc();
$evento_nombre = $evento_data['nombre'];

// 🔥 TOMAR 3 PRIMERAS LETRAS LIMPIAS
$prefijo = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $evento_nombre), 0, 3));

// 🔥 GENERAR CÓDIGO FINAL
$codigo = $prefijo . "-" . date("Y") . "-" . strtoupper(substr(md5(uniqid()), 0, 6));

$dni = preg_replace('/\D/', '', trim($dni));
$telefono = preg_replace('/\D/', '', trim($telefono));
if(strlen($dni) != 8){
    die("DNI inválido");
}

if(strlen($telefono) != 9){
    die("Teléfono inválido");
}

// 🔥 INSERT INSCRIPCIÓN
$sql = "INSERT INTO inscritos
(
    evento_id,
    codigo,
    nombre,
    dni,
    telefono,
    correo,
    club_equipo,
    acepta_responsabilidad,
    edad,
    distancia,
    talla,
    categoria,
    kit_id,
    kit,
    monto,
    estado_pago,
    fecha_registro
)
VALUES
(
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    'PENDIENTE',
    NOW()
)";

$stmt = $conn->prepare($sql);

if(!$stmt){
    die("ERROR PREPARE: " . $conn->error);
}

// 🔥 TIPOS (IMPORTANTE)
$stmt->bind_param(
    "issssssiisssisd",
    $evento_id,
    $codigo,
    $nombre,
    $dni,
    $telefono,
    $correo,
    $club_equipo,
    $acepta_responsabilidad,
    $edad,
    $distancia,
    $talla,
    $categoria,
    $kit_id,
    $kit,
    $monto
);

// 🔥 EJECUTAR
try {

    $stmt->execute();

    $id_inscrito = $conn->insert_id;

    if($monto == 0){

        $upd = $conn->prepare("
            UPDATE inscritos
            SET estado_pago='LIBRE'
            WHERE id=?
        ");

        $upd->bind_param("i", $id_inscrito);
        $upd->execute();

        header("Location: confirmacion?codigo=".$codigo);
        exit;
    }

    header("Location: checkout?id=".$id_inscrito);
    exit;

} catch (mysqli_sql_exception $e) {

    // 🔥 DUPLICADO (UNIQUE KEY)
    if($e->getCode() == 1062){

        $_SESSION['error'] = "⚠️ Ya tienes una inscripción activa con este DNI o celular.";
        header("Location: inscripcion?evento_id=".$evento_id);
        exit;
    }

    // 🔥 OTROS ERRORES
    error_log($e->getMessage());

    $_SESSION['error'] = "Ocurrió un error inesperado. Intenta nuevamente.";
    header("Location: inscripcion?evento_id=".$evento_id);
    exit;
}
