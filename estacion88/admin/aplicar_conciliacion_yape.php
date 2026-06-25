<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include("sesion_check.php");

if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}

include("csrf.php");
include("../../db.php");
require_once("enviar_correo_confirmacion.php");

$rol = strtoupper($_SESSION['rol'] ?? '');
if(!in_array($rol, ['ADMIN', 'OPERADOR'], true)){
    die("Acceso denegado.");
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: dashboard");
    exit;
}

validar_csrf($_POST['csrf_token'] ?? '');

$ids = $_POST['confirmar'] ?? [];
if(!is_array($ids) || empty($ids)){
    header("Location: dashboard?msg=sin_conciliacion");
    exit;
}

$confirmados = 0;

/* =========================
   UPDATE PAGO
========================= */
$stmt = $conn->prepare("
    UPDATE inscritos
    SET
        estado_pago='PAGADO',
        metodo_pago='YAPE',
        fecha_pago=NOW()
    WHERE id=?
      AND estado_pago='YAPE_PENDIENTE'
");

if(!$stmt){
    die("No se pudo preparar la confirmacion.");
}

/* =========================
   WHATSAPP URL STORAGE
========================= */
$whatsapp_urls = [];

/* =========================
   LOOP
========================= */
foreach($ids as $id){

    $id = intval($id);
    if($id <= 0){
        continue;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    if($stmt->affected_rows > 0){

        $confirmados++;

        /* CORREO */
        enviarCorreoConfirmacion($id, $conn);

        /* WHATSAPP DATA */
        $stmtW = $conn->prepare("
            SELECT nombre, telefono, codigo
            FROM inscritos
            WHERE id=?
            LIMIT 1
        ");

        if($stmtW){
            $stmtW->bind_param("i", $id);
            $stmtW->execute();
            $inscrito = $stmtW->get_result()->fetch_assoc();
            $stmtW->close();

            if(!empty($inscrito['telefono'])){

                $telefono = preg_replace('/[^0-9]/', '', $inscrito['telefono']);

                if(substr($telefono, 0, 1) === "9"){
                    $telefono = "51" . $telefono;
                }

                $mensaje = "Hola {$inscrito['nombre']}, tu inscripción fue CONFIRMADA. Código: {$inscrito['codigo']}";

                $whatsapp_urls[] = "https://wa.me/$telefono?text=" . urlencode($mensaje);
            }
        }
    }
}

$stmt->close();

/* =========================
   GUARDAR EN SESION
========================= */
$_SESSION['whatsapp_urls'] = $whatsapp_urls;

/* =========================
   REDIRECCION
========================= */
$_SESSION['whatsapp_ids'] = $ids;

header("Location: notificar_whatsapp.php?msg=ok&total=" . $confirmados);
exit;
