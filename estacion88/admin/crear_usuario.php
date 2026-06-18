<?php
 
include("sesion_check.php");
include("csrf.php");
include("../../db.php");

// 🔐 SOLO ADMIN PUEDE ENTRAR
if(!isset($_SESSION['id_admin']) || strtoupper($_SESSION['rol']) !== 'ADMIN'){
    die("Acceso denegado");
}

$error = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(
    !isset($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
){
    die("Solicitud inválida");
}

    $usuario = trim($_POST['usuario']);
    $passwordPlano = $_POST['password'];

    if(strlen($passwordPlano) < 8){
    $error = "La contraseña debe tener al menos 8 caracteres";
    }

    $password = password_hash(
    $passwordPlano,
    PASSWORD_BCRYPT
    );
    
    $nombre = trim($_POST['nombre']);

    $correo = filter_var(
    trim($_POST['correo']),
    FILTER_VALIDATE_EMAIL
                        );

    if(!$correo){
    $error = "Correo inválido";
    }
    // Rol
    $rol = $_POST['rol'];
    $rolesPermitidos = [
    'ADMIN',
    'OPERADOR',
    'LECTOR'
    ];

    if(!in_array($rol, $rolesPermitidos)){
    die("Rol inválido");
    }

    // validar usuario
    $check = $conn->prepare("SELECT id FROM staff_eventos WHERE usuario = ?");
    $check->bind_param("s", $usuario);
    $check->execute();
    $res = $check->get_result();

    if($res->num_rows > 0){
        $error = "El usuario ya existe";
    } else {

        $sql = "INSERT INTO staff_eventos (usuario, password, nombre, correo, rol, estado)
                VALUES (?, ?, ?, ?, ?, 'ACTIVO')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $usuario, $password, $nombre, $correo, $rol);
        $stmt->execute();

        $success = "Usuario creado correctamente";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Usuario</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>

    body{
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #000000, #1d0101, #050303);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    }

    .card-box{
    background: #fff;
    width: 100%;
    max-width: 500px;
    border-radius: 25px;
    padding: 35px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    }

    .title{
    text-align: center;
    font-weight: 900;
    margin-bottom: 5px;
    }

    .subtitle{
    text-align: center;
    color: #777;
    margin-bottom: 25px;
    }

    .form-control{
    border-radius: 12px;
    padding: 10px;
    }

    .btn-create{
    width: 100%;
    border: none;
    padding: 14px;
    border-radius: 50px;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #00c853, #43a047);
    transition: .3s;
    }

    .btn-create:hover{
    transform: translateY(-2px);
    opacity: .95;
    }

    .role-badge{
    font-size: 12px;
    color: #666;
    }

    .top-actions{
    display:flex;
    justify-content:space-between;
    margin-bottom:20px;
    gap:10px;
    }

    .btn-back, .btn-logout{
    flex:1;
    text-align:center;
    padding:10px;
    border-radius:50px;
    font-weight:700;
    text-decoration:none;
    transition:.3s;
    font-size:14px;
    }

    .btn-back{
    background:#e0f2f1;
    color:#00695c;
    border:1px solid #b2dfdb;
    }

    .btn-back:hover{
    background:#b2dfdb;
    }

    .btn-logout{
    background:#ffebee;
    color:#c62828;
    border:1px solid #ffcdd2;
    }

    .btn-logout:hover{
    background:#ffcdd2;
    }
</style>

</head>

<body>

<div class="card-box">
<div class="top-actions">

    <!-- 🔙 VOLVER -->
    <a href="dashboard" class="btn-back">
        🔙 Volver
    </a>

    <!-- 🚪 SALIR -->
    <a href="logout" class="btn-logout">
        🚪 Salir
    </a>

</div>
    <h2 class="title">👤 Crear Usuario</h2>
    <p class="subtitle">Panel de administración del sistema</p>

    <?php if($error){ ?>
        <div class="alert alert-danger text-center">
            <?= $error ?>
        </div>
    <?php } ?>

    <?php if($success){ ?>
        <div class="alert alert-success text-center">
            <?= $success ?>
        </div>
    <?php } ?>

    <form method="POST">
        <input type="hidden"
       name="csrf_token"
       value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label>Usuario</label>
            <input type="text" name="usuario" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nombre completo</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Correo</label>
            <input type="email" name="correo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Rol</label>
            <select name="rol" class="form-control" required>
                <option value="ADMIN">ADMIN - Control total</option>
                <option value="OPERADOR">OPERADOR - Gestión de pagos y kits</option>
                <option value="LECTOR">LECTOR - Solo lectura</option>
            </select>
            <small class="role-badge">Define permisos del usuario en el sistema</small>
        </div>

        <button class="btn-create">
            ➕ Crear Usuario
        </button>

    </form>

</div>

</body>
</html>