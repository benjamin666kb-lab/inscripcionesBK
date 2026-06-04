<?php

include("../db.php");

if(!isset($_GET['id'])){
    die("Inscripción no encontrada.");
}

$id = intval($_GET['id']);

$sql = "
UPDATE inscritos
SET estado_pago='PAGADO'
WHERE id=?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);

if($stmt->execute()){

    $sql2 = "
    SELECT codigo
    FROM inscritos
    WHERE id=?
    ";

    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i",$id);
    $stmt2->execute();

    $resultado = $stmt2->get_result();
    $inscrito = $resultado->fetch_assoc();

    header(
    "Location: confirmacion.php?codigo=".$inscrito['codigo']
    );

    exit;

}else{

    echo "Error al actualizar pago.";

}
?>