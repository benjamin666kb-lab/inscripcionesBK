<?php

session_start();
include("sesion_check.php");
if(!isset($_SESSION['id_admin'])){
    header("Location: login.php");
    exit;
}

include("../../db.php");

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=inscritos.csv");

$output = fopen("php://output", "w");

fputcsv($output, [
    'ID','Codigo','Nombre','DNI','Telefono','Correo','Edad',
    'Distancia','Talla','Categoria','Kit','Monto','Estado','Fecha'
]);

$sql = "SELECT * FROM inscritos ORDER BY id DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){

    fputcsv($output, [
        $row['id'],
        $row['codigo'],
        $row['nombre'],
        $row['dni'],
        $row['telefono'],
        $row['correo'],
        $row['edad'],
        $row['distancia'],
        $row['talla'],
        $row['categoria'],
        $row['kit'],
        $row['monto'],
        $row['estado_pago'],
        $row['fecha_registro']
    ]);
}

fclose($output);
exit;