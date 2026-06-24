<?php

include("sesion_check.php"); // 🔥 PRIMERO control de tiempo
if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}
include("csrf.php");
include("../../db.php");

// 🔥 Estadísticas
$total = $conn->query("
SELECT COUNT(*) total FROM inscritos
")->fetch_assoc();

$pagados = $conn->query("
SELECT COUNT(*) total FROM inscritos WHERE estado_pago='PAGADO'
")->fetch_assoc();

$pendientes = $conn->query("
SELECT COUNT(*) total FROM inscritos WHERE estado_pago='PENDIENTE'
")->fetch_assoc();

$yapePendientes = $conn->query("
SELECT COUNT(*) total FROM inscritos WHERE estado_pago='YAPE_PENDIENTE'
")->fetch_assoc();

$libres = $conn->query("
SELECT COUNT(*) total 
FROM inscritos 
WHERE UPPER(TRIM(estado_pago)) = 'LIBRE'
   OR monto <= 0
")->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>

*{
font-family:'Poppins',sans-serif;
}

body{
background:#f4f6f9;
}

.topbar{
    background:
    linear-gradient(
        90deg,
        #000000 0%,        
        #7c0307 50%,        
        #c90f0f 100%
    );

    color:white;

    padding:22px 35px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    border-bottom:3px solid rgba(255,255,255,.1);

    box-shadow:
        0 5px 20px rgba(0,0,0,.35);
}
.topbar h1,
.topbar h2,
.topbar h3{
    margin:0;
    font-size:2rem;
    font-weight:900;
    letter-spacing:1px;
    color:#fff;
}
.topbar span{
    color:#ff2020;

    text-shadow:
        0 0 8px rgba(255,0,0,.7),
        0 0 15px rgba(255,0,0,.5);
}
.card-box{
border:none;
border-radius:20px;
box-shadow:0 10px 30px rgba(0,0,0,.08);
transition:.3s;
}
.card-box:hover{
    transform: translateY(-5px);
}
.numero{
font-size:2.5rem;
font-weight:900;
}

.menu-acciones a{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 22px;
    margin-right: 10px;
    margin-bottom: 12px;
    border-radius: 14px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 6px 15px rgba(0,0,0,.08);
    transition: all .25s ease;
}
.menu-acciones a:hover{
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(0,0,0,.15);
}

.menu-acciones a:active{
    transform: scale(.98);
}
.btn-admin{
    display: inline-flex;
    align-items: center;
    gap: 10px;

    padding: 14px 28px;
    border-radius: 16px;

    background: linear-gradient(135deg,#000000,#e31b23);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    letter-spacing: .3px;

    border: none;

    box-shadow:
        0 10px 25px rgba(123,47,247,.25);

    transition: all .3s ease;
}

.btn-admin:hover{
    color:#fff;
    text-decoration:none;
    transform: translateY(-5px) scale(1.03);

    box-shadow:
        0 18px 35px rgba(123,47,247,.35);
}

.btn-admin:active{
    transform: scale(.98);
}
.btn-publico{
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
.upload-yape{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
    margin-bottom:22px;
}
.upload-yape h5{
    margin:0 0 8px;
    font-weight:800;
}
.upload-yape p{
    margin:0 0 14px;
    color:#64748b;
    font-size:14px;
}
</style>

</head>

<body>

<div class="topbar">

<div>
🏃 Estación <span>88</span> Panel
</div>

<div>
<?php echo $_SESSION['nombre']; ?> |
<?php echo $_SESSION['rol']; ?> |

<a href="logout" class="btn btn-light btn-sm">
Salir
</a>

</div>

</div>

<div class="container mt-4">

<h2 class="mb-4">Panel Principal</h2>

<div class="row">

<!-- INSCRITOS -->
<div class="col-md-2 mb-3">
<div class="card card-box">
<div class="card-body text-center">
<div class="numero">
<?php echo $total['total']; ?>
</div>
<div>👥 Inscritos</div>
</div>
</div>
</div>

<!-- PAGADOS -->
<div class="col-md-2 mb-3">
<div class="card card-box">
<div class="card-body text-center">
<div class="numero text-success">
<?php echo $pagados['total']; ?>
</div>
<div>💰 Pagados</div>
</div>
</div>
</div>

<!-- PENDIENTES -->
<div class="col-md-2 mb-3">
<div class="card card-box">
<div class="card-body text-center">
<div class="numero text-warning">
<?php echo $pendientes['total']; ?>
</div>
<div>⏳ Pendientes</div>
</div>
</div>
</div>

<!-- YAPE PENDIENTES -->
<div class="col-md-2 mb-3">
<div class="card card-box">
<div class="card-body text-center">
<div class="numero text-info">
<?php echo $yapePendientes['total']; ?>
</div>
<div>Yape por validar</div>
</div>
</div>
</div>

<!-- LIBRES -->
<div class="col-md-2 mb-3">
<div class="card card-box">
<div class="card-body text-center">
<div class="numero text-primary">
<?php echo $libres['total']; ?>
</div>
<div>🆓 Libres</div>
</div>
</div>
</div>

</div>

<hr>
<?php if(in_array($_SESSION['rol'], ['ADMIN','OPERADOR'])){ ?>

<div class="upload-yape">
    <h5>Conciliacion Yape</h5>
    <p>
        Sube el Excel exportado desde Yape para contrastarlo con las operaciones registradas.
    </p>

    <form method="POST"
          action="conciliacion_yape"
          enctype="multipart/form-data"
          class="row g-2 align-items-center">

        <input type="hidden"
               name="csrf_token"
               value="<?= $_SESSION['csrf_token']; ?>">

        <div class="col-md-8">
            <input type="file"
                   name="archivo_yape"
                   class="form-control"
                   accept=".xlsx,.csv"
                   required>
        </div>

        <div class="col-md-4">
            <button type="submit"
                    class="btn btn-primary w-100">
                Subir Excel Yape
            </button>
        </div>

    </form>
</div>

<?php } ?>

<!-- BOTONES DE ACCIÓN -->
<div class="menu-acciones">

<a href="inscritos.php" class="btn btn-success">
👥 Ver Inscritos
</a>

<a href="exportar_excel" class="btn btn-primary">
📊 Exportar Excel
</a>

<?php if($_SESSION['rol'] == 'ADMIN'){ ?>

<hr>

<h5 class="mb-3 text-secondary">
🔐 Herramientas de Administración
</h5>

<div class="d-flex gap-3 flex-wrap">

    <a href="crear_evento" class="btn-admin">
        🎟 Crear Evento
    </a>

    <a href="eventos_lista" class="btn-admin">
        🛠 Editar Eventos
    </a>

    <a href="crear_usuario" class="btn-admin">
        👤 Crear Usuario
    </a>

</div>

<?php } ?>
<a href="https://inscripcionesbk.free.nf/estacion88/index"
   class="btn-publico"
   target="_blank">
    🌐 Ver Estación88 -> Eventos
</a>
</div>

</div>

</body>
</html>
