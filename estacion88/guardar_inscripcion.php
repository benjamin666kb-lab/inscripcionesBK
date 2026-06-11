<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../db.php");

// 🔥 DATOS DEL FORMULARIO
$evento_id = $_POST['evento_id'] ?? 0;
$kit_id    = $_POST['kit_id'] ?? 0;

$nombre    = trim($_POST['nombre']);

$dni       = trim($_POST['dni']);
$telefono  = trim($_POST['telefono']);
$correo    = trim($_POST['correo']);

$edad      = $_POST['edad'];
$distancia = $_POST['distancia'] ?? '';
$talla     = $_POST['talla'] ?? '';

// 🔥 VALIDAR KIT
$kit_query = $conn->query("
    SELECT * 
    FROM eventos_kits 
    WHERE id = $kit_id 
    AND evento_id = $evento_id
    LIMIT 1
");

if(!$kit_query || $kit_query->num_rows == 0){
    die("Kit no válido");
}

$kit_data = $kit_query->fetch_assoc();

$kit   = $kit_data['nombre_kit'];
$monto = $kit_data['precio'];

// 🔥 GENERAR CÓDIGO ÚNICO// 🔥 GENERAR CÓDIGO ÚNICO// 🔥 GENERAR CÓDIGO ÚNICO// 🔥 GENERAR CÓDIGO ÚNICO// 🔥 GENERAR CÓDIGO ÚNICO
// 🔥 OBTENER NOMBRE DEL EVENTO
$evento_query = $conn->query("
    SELECT nombre 
    FROM eventos 
    WHERE id = $evento_id 
    LIMIT 1
");

if(!$evento_query || $evento_query->num_rows == 0){
    die("Evento no válido");
}

$evento_data = $evento_query->fetch_assoc();
$evento_nombre = $evento_data['nombre'];

// 🔥 TOMAR 3 PRIMERAS LETRAS LIMPIAS
$prefijo = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $evento_nombre), 0, 3));

// 🔥 GENERAR CÓDIGO FINAL
$codigo = $prefijo . "-" . date("Y") . "-" . strtoupper(substr(md5(uniqid()), 0, 6));

$dni = trim(preg_replace('/\s+/', '', $dni));
$telefono = trim(preg_replace('/\s+/', '', $telefono));

// 🔥 INSERT INSCRIPCIÓN
$sql = "INSERT INTO inscritos
(
    evento_id,
    codigo,
    nombre,
    dni,
    telefono,
    correo,
    edad,
    distancia,
    talla,
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
    'PENDIENTE',
    NOW()
)";

$stmt = $conn->prepare($sql);

if(!$stmt){
    die("ERROR PREPARE: " . $conn->error);
}

// 🔥 TIPOS (IMPORTANTE)
$stmt->bind_param(
    "isssssissisd",
    $evento_id,
    $codigo,
    $nombre,
    $dni,
    $telefono,
    $correo,
    $edad,
    $distancia,
    $talla,
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

        header("Location: confirmacion.php?codigo=".$codigo);
        exit;
    }

    header("Location: checkout.php?id=".$id_inscrito);
    exit;

} catch (mysqli_sql_exception $e) {

    // 🔥 DUPLICADO (UNIQUE KEY)
    if($e->getCode() == 1062){

        $_SESSION['error'] = "⚠️ Ya tienes una inscripción activa con este DNI o celular.";
        header("Location: inscripcion.php?evento_id=".$evento_id);
        exit;
    }

    // 🔥 OTROS ERRORES
    error_log($e->getMessage());

    $_SESSION['error'] = "Ocurrió un error inesperado. Intenta nuevamente.";
    header("Location: inscripcion.php?evento_id=".$evento_id);
    exit;
}
