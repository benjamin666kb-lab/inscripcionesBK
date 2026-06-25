<?php

include("../db.php");
require_once("../config_culqi.php");

if(!isset($_GET['id'])){
    header("Location: index");
    exit;
}

$id = intval($_GET['id']);

$sql = "
SELECT i.*, e.nombre AS nombre_evento
FROM inscritos i
LEFT JOIN eventos e ON e.id = i.evento_id
WHERE i.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows == 0){
    die("Inscripción no encontrada.");
}

$inscrito = $resultado->fetch_assoc();
$estadosBloqueados = ['PAGADO','LIBRE','YAPE_PENDIENTE'];
$montoBase = (float)$inscrito['monto'];
$montoCulqi = round($montoBase * 1.10, 2);
$comisionCulqi = round($montoCulqi - $montoBase, 2);

if($inscrito['estado_pago'] === 'YAPE_PENDIENTE'){
    header("Location: pago_yape_pendiente?codigo=" . urlencode($inscrito['codigo']));
    exit;
}

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
    .payment-options{
    display:grid;
    gap:14px;
    margin-top:22px;
    }
    .payment-option{
    border:2px solid #e4e7ec;
    border-radius:18px;
    padding:16px;
    cursor:pointer;
    transition:.2s;
    background:#fff;
    }
    .payment-option.active{
    border-color:#00c853;
    box-shadow:0 10px 25px rgba(0,200,83,.14);
    }
    .payment-option input{
    margin-right:8px;
    }
    .payment-title{
    font-weight:800;
    color:#1f2933;
    }
    .payment-desc{
    margin:6px 0 0;
    font-size:13px;
    color:#64748b;
    line-height:1.45;
    }
    .yape-box{
    display:none;
    margin-top:16px;
    padding:16px;
    border-radius:18px;
    background:#f7fbff;
    border:1px solid #dbeafe;
    }
    .yape-box.active{
    display:block;
    }
    .input-operacion{
    width:100%;
    border:1px solid #d7dde8;
    border-radius:12px;
    padding:12px 14px;
    font-size:16px;
    font-weight:600;
    outline:none;
    }
    .input-operacion:focus{
    border-color:#00c853;
    box-shadow:0 0 0 4px rgba(0,200,83,.12);
    }
    .amount-detail{
    font-size:13px;
    color:#5b6675;
    margin-top:8px;
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
    .yape-ayuda{
    display:flex;
    gap:20px;
    margin-top:20px;
    flex-wrap:wrap;
    }

    .ayuda-card{
    flex:1;
    min-width:280px;
    background:#f8f9fa;
    border:1px solid #e5e7eb;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 2px 8px rgba(0,0,0,.05);
    }

    .ayuda-img{
    width:100%;
    height:220px;
    object-fit:contain;
    background:#fff;
    padding:10px;
    }

    .ayuda-texto{
    padding:15px;
    text-align:center;
    font-size:14px;
    line-height:1.5;
    }

    .codigo-destacado{
    display:block;
    margin-top:8px;
    font-size:18px;
    font-weight:700;
    color:#6f42c1;
    }

    @media(max-width:768px){

    .yape-ayuda{
        flex-direction:column;
    }

    .ayuda-img{
        height:180px;
    }

    }
    .culqi-bloqueado{
    opacity: 0.5;
    cursor: not-allowed;
    }
    .copiar-btn{
    border: none;
    background: #6f42c1;
    color: #fff;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    transition: .3s;
    cursor: pointer;
}

.copiar-btn:hover{
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(111,66,193,.25);
}

.copiar-btn:active{
    transform: scale(.95);
}
</style>

</head>

<body>

<div class="card-checkout">

    <<div class="header">

    <h1>
        <?php echo htmlspecialchars($inscrito['nombre_evento']); ?>
    </h1>

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

            <p>Monto base de inscripcion</p>

            <h2>
                S/ <?php echo number_format($inscrito['monto'],2); ?>
            </h2>

        </div>

        <div class="payment-options">

            <label class="payment-option culqi-bloqueado" data-payment-option="culqi">    
                <input type="radio" name="metodo_pago" value="culqi">
                <span class="payment-title">Culqi - tarjeta de débito o crédito</span>
                <p class="payment-desc">                
                Validacion en menos de 1H.
                </p>
                <div class="amount-detail">
                    Total con comision: S/ <?php echo number_format($montoCulqi, 2); ?>
                </div>
                 <small style="display:block;margin-top:8px;color:#d97706;font-weight:600;">
                    🚧 Estamos trabajando para habilitar este método de pago
                </small>

</label>

            <label class="payment-option" data-payment-option="yape">
                <input type="radio" name="metodo_pago" value="yape">
                <span class="payment-title">Yape </span>
                <p class="payment-desc">
                     Validación en 6 h.
                </p>
                <div class="amount-detail">
                    Total a yapear: S/ <?php echo number_format($montoBase, 2); ?>
                </div>
                    <div class="yape-ayuda">
    
    <div class="ayuda-card">
        <img src="uploads/codigo unico yape.jpeg"
             alt="Código único Yape"
             class="ayuda-img">

        <div class="ayuda-texto">
            <strong>Código Único</strong><br>
            En el concepto o mensaje del pago escribe:

            <div class="d-flex align-items-center gap-2 mt-2">
                <span
                id="codigoInscripcion"
                class="codigo-destacado">
                <?php echo htmlspecialchars($inscrito['codigo']); ?>
                </span>

                <button
                type="button"
                class="copiar-btn"
                onclick="copiarTexto('codigoInscripcion', this)">
                📋 Copiar
                </button>
            </div>
        </div>

    </div>

    <div class="ayuda-card">
        <img src="uploads/numero de operacion yape.jpeg"
             alt="Número de operación Yape"
             class="ayuda-img">

        <div class="ayuda-texto">
            <strong>Número de Operación</strong><br>
            Luego copia el número de operación que aparece en tu comprobante Yape.
        </div>
    </div>

</div>
            </label>

        </div>

        <form method="POST" action="procesar_yape" id="formYape" class="yape-box">
            <input type="hidden" name="id" value="<?php echo (int)$inscrito['id']; ?>">

            <label for="numeroOperacion" class="mb-2">
                Numero de operacion Yape
            </label>

            <input
                type="text"
                id="numeroOperacion"
                name="numero_operacion_yape"
                class="input-operacion"
                maxlength="10"
                placeholder="Ingresa el número de operación"
                autocomplete="off"
                required>

            

            <p class="payment-desc">
                En el mensaje o descripcion del pago escribe este código:
                <div class="d-inline-flex align-items-center gap-2">

                <strong id="codigoPago">
                    <?php echo htmlspecialchars($inscrito['codigo']); ?>
                </strong>

                <button
                    type="button"
                    class="copiar-btn"
                    onclick="copiarTexto('codigoPago', this)">
                    📋
                </button>

                </div>
                Despues de enviar el número, tu pago quedará pendiente para validación.
            </p>
        </form>

        <button
        class="btn-pagar"
        id="btnPagar">

        💳 PAGAR AHORA

        </button>

    </div>

</div>
<script>
            document.getElementById('numeroOperacion').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            });
            </script>
<script src="https://js.culqi.com/checkout-js"></script>
<script>
const publicKey = <?= json_encode(CULQI_PUBLIC_KEY); ?>;

const settings = {
    title: <?= json_encode("Shrek Run"); ?>,
    currency: <?= json_encode("PEN"); ?>,
    amount: <?= json_encode((int)round($montoCulqi * 100)); ?>
};

const client = {
    email: <?= json_encode($inscrito['correo']); ?>
};

const options = {
    lang: "es",
    paymentMethods: {
        tarjeta: true,
        yape: false,
        billetera: false,
        bancaMovil: false,
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
        inputId.value = <?= json_encode((int)$inscrito['id']); ?>;

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

    const metodo = document.querySelector("input[name='metodo_pago']:checked").value;

    if (metodo === "yape") {
        const operacion = document.getElementById("numeroOperacion");
        if (!operacion.value.trim()) {
            operacion.focus();
            return;
        }

        document.getElementById("formYape").submit();
        return;
    }

    Culqi.open();

    e.preventDefault();

});

document.querySelectorAll("input[name='metodo_pago']").forEach(function(input){
    input.addEventListener("change", function(){
        document.querySelectorAll(".payment-option").forEach(function(option){
            option.classList.toggle(
                "active",
                option.dataset.paymentOption === input.value
            );
        });

        document
            .getElementById("formYape")
            .classList
            .toggle("active", input.value === "yape");

        document.getElementById("btnPagar").textContent =
            input.value === "yape" ? "REGISTRAR OPERACION YAPE" : "PAGAR AHORA";
    });
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const culqi = document.querySelector('[data-payment-option="culqi"]');

    culqi.addEventListener("click", function(e){
        e.preventDefault();
        e.stopPropagation();
    });

});
</script>

<script>
function copiarTexto(id, btn){

    const texto =
        document.getElementById(id).innerText;

    navigator.clipboard.writeText(texto)
        .then(() => {

            const original = btn.innerHTML;

            btn.innerHTML = "✅";

            setTimeout(() => {
                btn.innerHTML = original;
            }, 2000);

        })
        .catch(err => {
            console.log(err);
            alert("No se pudo copiar.");
        });
}
</script>
</body>
<a href="index" class="btn-volver">
← Inicio
</a>
</html>
