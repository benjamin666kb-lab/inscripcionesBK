<?php

session_start();
include("../../db.php");

// 🔐 Evitar acceso si ya está logueado
if(isset($_SESSION['id_admin'])){
    header("Location: dashboard.php");
    exit;
}

// 🔐 CSRF TOKEN
if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// 🔐 control de intentos
if(!isset($_SESSION['intentos'])){
    $_SESSION['intentos'] = 0;
}

if($_SESSION['intentos'] >= 5){
    die("Demasiados intentos. Intenta más tarde.");
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // CSRF VALIDATION
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
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

        // VERIFICACIÓN SEGURA
        if(password_verify($password, $admin['password'])){

            session_regenerate_id(true);

            $_SESSION['id_admin'] = $admin['id'];
            $_SESSION['nombre'] = $admin['nombre'];
            $_SESSION['correo'] = $admin['correo'];
            $_SESSION['rol'] = $admin['rol'];

            $_SESSION['intentos'] = 0;

            header("Location: dashboard.php");
            exit;

        } else {
            $_SESSION['intentos']++;
            $error = "Usuario o contraseña incorrectos.";
        }

    } else {
        $_SESSION['intentos']++;
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
background: linear-gradient(135deg,#00c853,#43a047,#1b5e20);
height:100vh;
display:flex;
align-items:center;
justify-content:center;
}

.login-card{
background:white;
width:100%;
max-width:420px;
padding:40px;
border-radius:25px;
box-shadow:0 15px 50px rgba(0,0,0,.25);
}

.logo{
font-size:55px;
text-align:center;
margin-bottom:10px;
}

.titulo{
text-align:center;
font-weight:900;
margin-bottom:5px;
}

.subtitulo{
text-align:center;
color:#777;
margin-bottom:25px;
}

.btn-login{
width:100%;
border:none;
padding:14px;
font-weight:700;
border-radius:50px;
background:linear-gradient(135deg,#00c853,#43a047);
color:white;
}

.btn-login:hover{
opacity:.9;
}
.back-index{
    position:absolute;
    top:20px;
    left:20px;
    text-decoration:none;
    font-size:13px;
    padding:8px 12px;
    border-radius:30px;
    background:rgba(255,255,255,0.15);
    color:white;
    border:1px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
    transition:.3s;
    font-weight:600;
}

.back-index:hover{
    transform:translateY(-2px);
    background:rgba(255,255,255,0.25);
    color:white;
}
.btn-publico{
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;

    display:inline-flex;
    align-items:center;
    gap:8px;

    padding:10px 18px;

    background:#fff;
    color:#555;
    text-decoration:none;
    font-weight:600;

    border:1px solid #dcdfe4;
    border-radius:12px;

    transition:.25s;
}

.btn-publico:hover{
    background:#f8f9fa;
    color:#198754;
    border-color:#198754;
    transform:translateY(-2px);
    text-decoration:none;

    box-shadow:0 6px 15px rgba(25,135,84,.10);
}
</style>

</head>

<body>

<div class="login-card">

<div class="logo">🏃</div>

<h2 class="titulo">ESTACIÓN 88</h2>

<p class="subtitulo">Panel de Administración</p>

<?php if($error){ ?>
<div class="alert alert-danger text-center">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php } ?>
<?php if(isset($_GET['timeout'])){ ?>

<div class="alert alert-warning shadow-sm">
    ⏱ Sesión expirada por inactividad. Por seguridad, vuelve a iniciar sesión.
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

</body>
<a href="../index.php" class="back-index">
← Volver al inicio
</a>
<a href="https://inscripcionesbk.free.nf/estacion88/index.php"
   class="btn-publico"
   target="_blank">
    🌐 Ver Estación88 -> Eventos
</a>
</html>