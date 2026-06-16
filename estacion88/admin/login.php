<?php
session_start();

include("../../db.php");
include("rate_limit.php");

// 🔒 Máximo 5 intentos fallidos en 5 minutos
verificarRateLimit($conn, "login.php", 5, 300);

// 🔐 Evitar acceso si ya está logueado
if(isset($_SESSION['id_admin'])){
    header("Location: dashboard");
    exit;
}

// 🔐 CSRF TOKEN
if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // 🔐 Validar CSRF
    if(
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ){
        die("Solicitud inválida.");
    }

    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    $sql = "
    SELECT *
    FROM staff_eventos
    WHERE usuario = ?
    AND estado = 'ACTIVO'
    LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if($resultado->num_rows === 1){

        $admin = $resultado->fetch_assoc();

        if(password_verify($password, $admin['password'])){

            // 🔒 Regenerar sesión
            session_regenerate_id(true);

            $_SESSION['id_admin'] = $admin['id'];
            $_SESSION['nombre'] = $admin['nombre'];
            $_SESSION['correo'] = $admin['correo'];
            $_SESSION['rol'] = $admin['rol'];

            // 🧹 Limpiar intentos fallidos de esta IP
            $ip = $_SERVER['REMOTE_ADDR'];

            $stmtDelete = $conn->prepare("
                DELETE FROM rate_limit
                WHERE ip = ?
                AND accion = 'login'
            ");

            $stmtDelete->bind_param("s", $ip);
            $stmtDelete->execute();

            header("Location: dashboard");
            exit;

        } else {

            // ❌ Registrar intento fallido
            registrarIntento($conn, "login.php");

            $error = "Usuario o contraseña incorrectos.";
        }

    } else {

        // ❌ Registrar intento fallido
        registrarIntento($conn, "login.php");

        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login Administrador</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>
*{
    font-family:'Poppins',sans-serif;
}

body{
    background:
    radial-gradient(circle at center,
    #6b0000 0%,
    #350000 35%,
    #120000 65%,
    #050505 100%);

    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

/* =========================
   CARD LOGIN
========================= */
.login-card{

    background:
    linear-gradient(
    180deg,
    rgba(10,10,10,.96),
    rgba(0,0,0,.96)
    );

    width:100%;
    max-width:430px;

    padding:35px;

    border-radius:28px;

    border:2px solid rgba(255,40,40,.8);

    box-shadow:
    0 0 15px rgba(255,0,0,.45),
    0 0 60px rgba(255,0,0,.15),
    0 20px 50px rgba(0,0,0,.7);

    backdrop-filter:blur(10px);

    position:relative;
}

/* brillo superior */
.login-card::before{
    content:"";
    position:absolute;
    top:-2px;
    left:25px;
    right:25px;
    height:6px;

    background:#ff2b2b;

    border-radius:30px;

    box-shadow:
    0 0 15px #ff0000,
    0 0 35px #ff0000;
}

/* =========================
   LOGO
========================= */
.logo{
    font-size:58px;
    text-align:center;
    margin-bottom:8px;
}

/* =========================
   TITULO
========================= */
.titulo{
    text-align:center;
    font-weight:900;
    margin-bottom:5px;
    color:#fff;
    letter-spacing:1px;
    text-transform:uppercase;
}

.titulo span{
    color:#ff2020;

    text-shadow:
        0 0 8px rgba(255,0,0,.7),
        0 0 15px rgba(255,0,0,.5);
}

/* =========================
   SUBTITULO
========================= */
.subtitulo{
    text-align:center;
    color:#d8d8d8;
    font-size:14px;
    margin-bottom:30px;
    font-weight:500;
    text-transform:uppercase;
    letter-spacing:.5px;

    display:flex;
    align-items:center;
    justify-content:center;
    gap:15px;
}

.subtitulo::before,
.subtitulo::after{
    content:"";
    width:55px;
    height:2px;

    background:linear-gradient(
        90deg,
        transparent,
        #ff2020
    );

    box-shadow:
        0 0 8px rgba(255,0,0,.7);
}

.subtitulo::after{
    background:linear-gradient(
        90deg,
        #ff2020,
        transparent
    );
}


/* =========================
   LABELS
========================= */
label{
    color:#fff;
    font-size:15px;
    font-weight:800;
    text-transform:uppercase;
    margin-bottom:8px;
}

/* =========================
   INPUTS
========================= */
.form-control{

    background:#070707 !important;

    color:#fff !important;

    border:2px solid rgba(255,30,30,.85);

    border-radius:16px;

    padding:14px 16px;

    margin-top:6px;
    margin-bottom:15px;

    box-shadow:none;

    transition:.25s;
}

.form-control::placeholder{
    color:#888;
}

.form-control:focus{

    background:#070707;

    color:white;

    border-color:#ff3d3d;

    box-shadow:
    0 0 10px rgba(255,0,0,.25);

    outline:none;
}

/* =========================
   BOTON INGRESAR
========================= */
.btn-login{

    width:100%;
    border:none;

    padding:16px;

    border-radius:18px;

    font-weight:900;
    font-size:20px;

    color:#fff;

    background:
    linear-gradient(
    180deg,
    #ff3030 0%,
    #ff0000 100%
    );

    box-shadow:
    0 0 15px rgba(255,0,0,.5),
    0 10px 25px rgba(255,0,0,.25);

    transition:.25s;
}

.btn-login:hover{

    transform:translateY(-2px);

    box-shadow:
    0 0 20px rgba(255,0,0,.8),
    0 15px 35px rgba(255,0,0,.35);
}

/* =========================
   BOTÓN VOLVER
========================= */
.back-index{

    position:fixed;
    top:20px;
    left:20px;

    text-decoration:none;

    color:#fff;

    padding:10px 16px;

    border-radius:12px;

    background:rgba(0,0,0,.45);

    border:1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(10px);

    transition:.25s;

    z-index:9999;
}

.back-index:hover{
    background:rgba(255,0,0,.18);
    color:white;
}

/* =========================
   BOTON WEB
========================= */
.btn-publico{

    position:fixed;
    top:20px;
    right:20px;

    z-index:9999;

    display:inline-flex;
    align-items:center;
    gap:8px;

    padding:10px 16px;

    color:#fff;
    text-decoration:none;

    border-radius:12px;

    background:rgba(0,0,0,.45);

    border:1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(10px);

    transition:.25s;
}

.btn-publico:hover{
    background:rgba(255,0,0,.18);
    color:#fff;
}

/* =========================
   ALERTAS
========================= */
.alert{
    border:none;
    border-radius:14px;
}

/* =========================
   RESPONSIVE
========================= */
@media(max-width:576px){

    .login-card{
        max-width:95%;
        padding:25px;
    }

    .btn-publico{
        top:65px;
        right:10px;
        font-size:12px;
    }

    .back-index{
        left:10px;
        top:10px;
        font-size:12px;
    }
}
</style>

</head>

<body>

<div class="login-card">

<div class="logo">🏃</div>

<h2 class="titulo">
    ESTACIÓN <span>88</span>
</h2>
<p class="subtitulo">Panel de Administración</p>

<?php if($error){ ?>
<div class="alert alert-danger text-center">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php } ?>
<?php if(isset($_GET['expirado'])){ ?>
<div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
    ⏱ Sesión expirada por inactividad. Por seguridad, vuelve a iniciar sesión.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php } ?>

<?php if(isset($_GET['mensaje'])){ ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_GET['mensaje']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php } ?>
<form method="POST">

<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

<div class="mb-3">
<label>Usuario</label>
<input type="text" name="usuario" class="form-control" required>
</div>

<div class="mb-3">
<label>Contraseña</label>
<input type="password" name="password" class="form-control" required>
</div>

<button type="submit" class="btn-login">
🔐 INGRESAR
</button>

</form>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<a href="../index" class="back-index">
← Volver al inicio
</a>
<a href="https://inscripcionesbk.free.nf/estacion88/index"
   class="btn-publico"
   target="_blank">
    🌐 Ver Estación88 -> Eventos
</a>
</html>