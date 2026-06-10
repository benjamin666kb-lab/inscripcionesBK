<?php

include("../db.php");

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM inscritos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows == 0){
    die("Inscripción no encontrada.");
}

$inscrito = $resultado->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Checkout - Shrek Run</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>

    *{
    font-family:'Poppins',sans-serif;
    }

    body{

    background:
    linear-gradient(
    135deg,
    #00c853,
    #43a047,
    #1b5e20
    );

    min-height:100vh;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:20px;
    }

    .card-checkout{

    background:white;

    border-radius:30px;

    overflow:hidden;

    box-shadow:
    0 20px 60px rgba(0,0,0,.25);

    width:100%;
    max-width:700px;
    }

    .header{

    background:
    linear-gradient(
    135deg,
    #ff9800,
    #ff5722
    );

    color:white;

    text-align:center;

    padding:35px;
    }

    .header h1{

    font-weight:900;
    margin:0;
    }

    .body-card{

    padding:35px;
    }

    .info{

    background:#f8f9fa;

    border-radius:15px;

    padding:15px;

    margin-bottom:15px;
    }

    label{

    font-size:14px;
    color:#777;
    }

    .valor{

    font-size:18px;
    font-weight:700;
    }

    .total{

    background:#e8f5e9;

    border:2px solid #00c853;

    border-radius:20px;

    text-align:center;

    padding:25px;

    margin-top:25px;
    }

    .total h2{

    color:#00c853;
    font-weight:900;
    margin:0;
    }

    .btn-pagar{

    width:100%;

    border:none;

    padding:18px;

    border-radius:50px;

    font-size:20px;

    font-weight:700;

    color:white;

    margin-top:25px;

    background:
    linear-gradient(
    135deg,
    #00c853,
    #43a047
    );

    transition:.3s;
    }

    .btn-pagar:hover{

    transform:translateY(-3px);

    }

</style>

</head>

<body>

<div class="card-checkout">

    <div class="header">

        <h1>🏃 SHREK RUN</h1>

        <p class="mb-0">
        Completa tu inscripción
        </p>

    </div>

    <div class="body-card">

        <div class="info">

            <div class="label">Código de inscripción</div>

            <div class="valor">
            <?php echo htmlspecialchars($inscrito['codigo']); ?>
            </div>

        </div>

        <div class="info">

            <div class="label">Participante</div>

            <div class="valor">
            <?php echo htmlspecialchars($inscrito['nombre']); ?>
            </div>

        </div>

        <div class="info">

            <div class="label">Kit seleccionado</div>

            <div class="valor">
            <?php echo htmlspecialchars($inscrito['kit']); ?>
            </div>

        </div>

        <div class="info">

            <div class="label">Distancia</div>

            <div class="valor">
            <?php echo htmlspecialchars($inscrito['distancia']); ?>
            </div>

        </div>

        <div class="total">

            <p>Monto a pagar</p>

            <h2>
                S/ <?php echo number_format($inscrito['monto'],2); ?>
            </h2>

        </div>

        <button
        class="btn-pagar"
        id="btnPagar">

        💳 PAGAR AHORA

        </button>

    </div>

</div>
<script src="https://js.culqi.com/checkout-js"></script>
<script>

const settings = {
    title: "Shrek Run",
    currency: "PEN",
    amount: <?php echo intval($inscrito['monto'] * 100); ?>
};

const client = {
    email: "prueba@correo.com"
};

const options = {
    lang: "es",
    paymentMethods: {
        tarjeta: true,
        yape: true,
        billetera: true,
        bancaMovil: true,
        agente: false,
        cuotealo: false
    }
};

const config = {
    settings,
    client,
    options
};

const publicKey = "pk_test_8j86Lmv2KMRWu9xP";

const Culqi = new CulqiCheckout(publicKey, config);

const handleCulqiAction = () => {

   if (Culqi.token) {

    const token = Culqi.token.id;

    window.location.href =
    "procesar_pago.php?id=<?php echo $inscrito['id']; ?>&token=" + token;

}else {

        console.log(Culqi.error);

    }

};

Culqi.culqi = handleCulqiAction;

document
.getElementById("btnPagar")
.addEventListener("click", function(e){

    Culqi.open();

    e.preventDefault();

});

</script>

</body>
</html>