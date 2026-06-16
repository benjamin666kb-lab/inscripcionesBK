<?php

include("../db.php");
require_once("../config_culqi.php");

if(!isset($_GET['id'])){
    header("Location: index");
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
$estadosBloqueados = ['PAGADO','LIBRE'];

if(in_array($inscrito['estado_pago'], $estadosBloqueados)){

    die("
    <div style='
        font-family:Arial;
        max-width:600px;
        margin:50px auto;
        padding:20px;
        text-align:center;
        border:1px solid #ddd;
        border-radius:15px;
        background:#f8f9fa;
    '>

        <h2 style='color:green'>
        ✅ Esta inscripción ya fue completada
        </h2>

        <p>
        El ticket <b>{$inscrito['codigo']}</b> ya no requiere pago.
        </p>

        <a href='ticket?codigo={$inscrito['codigo']}'>
            Ver Ticket
        </a>

    </div>
    ");
}
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
    #05130b,
    #010801,
    #2e0303
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
    .btn-volver{
    position:fixed;
    top:20px;
    left:20px;
    background:rgba(255,255,255,0.15);
    color:white;
    padding:8px 14px;
    border-radius:30px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    backdrop-filter: blur(10px);
    border:1px solid rgba(255,255,255,0.2);
    transition:.3s;
    z-index:999;
    }

    .btn-volver:hover{
    transform:translateY(-2px);
    background:rgba(255,255,255,0.25);
    color:white;
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
const publicKey = "<?php echo CULQI_PUBLIC_KEY; ?>";

const settings = {
    title: "Shrek Run",
    currency: "PEN",
    amount: <?php echo intval($inscrito['monto'] * 100); ?>
};

const client = {
    email: "<?php echo $inscrito['correo']; ?>"
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


const Culqi = new CulqiCheckout(publicKey, config);

const handleCulqiAction = () => {

    if (Culqi.token) {

        const token = Culqi.token.id;

        const form = document.createElement("form");

        form.method = "POST";
        form.action = "procesar_pago";

        const inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "id";
        inputId.value = "<?php echo $inscrito['id']; ?>";

        const inputToken = document.createElement("input");
        inputToken.type = "hidden";
        inputToken.name = "token";
        inputToken.value = token;

        form.appendChild(inputId);
        form.appendChild(inputToken);

        document.body.appendChild(form);

        form.submit();

    } else {

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
<a href="index" class="btn-volver">
← Inicio
</a>
</html>