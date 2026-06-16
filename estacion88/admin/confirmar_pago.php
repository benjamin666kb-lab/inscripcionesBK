<?php

session_start();
include("sesion_check.php");
include("../../db.php");

/* =========================
   VALIDACIÓN DE SESIÓN
========================= */
if (!isset($_SESSION['id_admin']) || !isset($_SESSION['rol'])) {
    header("Location: login");
    exit;
}

$rol = strtoupper($_SESSION['rol']);

if (!in_array($rol, ['ADMIN', 'OPERADOR'])) {
    die("Acceso denegado");
}

/* =========================
   CSRF TOKEN
========================= */
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   VALIDAR ID
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("ID no válido");
}

$id = intval($_POST['id']);

/* =========================
   VALIDAR CSRF
========================= */
if (
    !isset($_POST['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token']
    )
){
    die("Solicitud no válida (CSRF detectado)");
}
/* =========================
   CONSULTAR ESTADO ACTUAL
========================= */
$stmt = $conn->prepare("SELECT estado_pago FROM inscritos WHERE id = ?");
if (!$stmt) {
    die("Error en consulta");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$fila = $result->fetch_assoc();

if (!$fila) {
    die("Inscrito no encontrado");
}

/* =========================
   VALIDAR SI YA ESTÁ PAGADO
========================= */
if (strtoupper($fila['estado_pago']) === 'PAGADO') {
    header("Location: detalle_inscrito?id=$id&msg=ya_confirmado");
    exit;
}

/* =========================
   ACTUALIZAR PAGO
========================= */
$update = $conn->prepare("UPDATE inscritos SET estado_pago = 'PAGADO' WHERE id = ?");
if (!$update) {
    die("Error en actualización");
}

$update->bind_param("i", $id);

if ($update->execute()) {
    header("Location: detalle_inscrito?id=$id&msg=ok");
    exit;
} else {
    header("Location: detalle_inscrito?id=$id&msg=error");
    exit;
}