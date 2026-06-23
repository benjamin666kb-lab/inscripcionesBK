<?php

include("sesion_check.php");

if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}

include("csrf.php");
include("../../db.php");
include("reglas_conciliacion_yape.php");

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: dashboard");
    exit;
}

validar_csrf($_POST['csrf_token'] ?? '');

if(!isset($_FILES['archivo_yape']) || $_FILES['archivo_yape']['error'] !== UPLOAD_ERR_OK){
    die("No se pudo subir el archivo Yape.");
}

$nombreArchivo = $_FILES['archivo_yape']['name'];
$extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
$permitidas = ['xlsx', 'csv'];

if(!in_array($extension, $permitidas, true)){
    die("Formato no permitido. Sube un archivo XLSX o CSV.");
}

/* =========================
   NORMALIZACION
========================= */
function normalizar_yape($texto){
    $texto = strtoupper(trim((string)$texto));
    $texto = strtr($texto, [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N',
        'á'=>'A','é'=>'E','í'=>'I','ó'=>'O','ú'=>'U','ü'=>'U','ñ'=>'N'
    ]);
    $texto = preg_replace('/[^A-Z0-9. ]+/', ' ', $texto);
    return preg_replace('/\s+/', ' ', trim($texto));
}

function monto_yape($valor){
    $valor = trim((string)$valor);
    $valor = str_replace(['S/', 's/', ' ', ','], ['', '', '', '.'], $valor);
    return round((float)$valor, 2);
}
/* =========================
   XLSX READER CORREGIDO
========================= */
function celda_xlsx_texto($cell, $sharedStrings){

    $tipo = (string)$cell['t'];

    if($tipo === 's'){

        $v = $cell->xpath('main:v');

        $idx = isset($v[0]) ? intval((string)$v[0]) : -1;

        return $sharedStrings[$idx] ?? '';
    }

    $texto = '';

    foreach($cell->xpath('.//main:t') as $t){
        $texto .= (string)$t;
    }

    if($texto !== ''){
        return $texto;
    }

    $v = $cell->xpath('main:v');

    return isset($v[0]) ? (string)$v[0] : '';
}

function col_index_xlsx($ref){
    preg_match('/[A-Z]+/', (string)$ref, $match);
    $letters = $match[0] ?? '';
    $index = 0;
    for($i = 0; $i < strlen($letters); $i++){
        $index = ($index * 26) + (ord($letters[$i]) - 64);
    }
    return max(0, $index - 1);
}
function cargar_shared_strings($zip){

    $strings = [];

    $xml = $zip->getFromName('xl/sharedStrings.xml');

    if(!$xml){
        return $strings;
    }

    $shared = simplexml_load_string($xml);

    $shared->registerXPathNamespace(
        'main',
        'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
    );

    foreach($shared->xpath('//main:si') as $si){

        $texto = '';

        foreach($si->xpath('.//main:t') as $t){
            $texto .= (string)$t;
        }

        $strings[] = $texto;
    }

    return $strings;
}
function leer_xlsx_yape($path){
    if(!class_exists('ZipArchive')){
        die("El servidor no tiene habilitada la extension ZipArchive.");
    }

    $zip = new ZipArchive();
    if($zip->open($path) !== true){
        die("No se pudo abrir el XLSX de Yape.");
    }
        $sharedStrings = cargar_shared_strings($zip);

    $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if(!$xml){
        die("El XLSX no contiene una hoja valida.");
    }

    $sheet = simplexml_load_string($xml);
    $sheet->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

    $rows = [];
    foreach($sheet->xpath('//main:sheetData/main:row') as $row){
        $row->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $values = [];
        foreach($row->xpath('main:c') as $cell){
            $index = col_index_xlsx((string)$cell['r']);
            while(count($values) < $index){
                $values[] = '';
            }
            $values[] = celda_xlsx_texto($cell,$sharedStrings);
        }
        $rows[] = $values;
    }

    return $rows;
}

function leer_csv_yape($path){
    $rows = [];
    $handle = fopen($path, 'r');
    if(!$handle){
        die("No se pudo abrir el CSV de Yape.");
    }

    while(($row = fgetcsv($handle, 0, ',')) !== false){
        $rows[] = $row;
    }

    fclose($handle);
    return $rows;
}

function transacciones_yape($rows){
    $headerIndex = -1;
    $headers = [];

    foreach($rows as $i => $row){
        $normalized = array_map('normalizar_yape', $row);
        
        $hasMonto = false;
$hasMensaje = false;

foreach($normalized as $cell){

    $cell = normalizar_yape($cell);

    if(
        strpos($cell, 'MONTO') !== false ||
        strpos($cell, 'IMPORTE') !== false
    ){
        $hasMonto = true;
    }

    if(
        strpos($cell, 'MENSAJE') !== false ||
        strpos($cell, 'GLOSA') !== false ||
        strpos($cell, 'DESCRIPCION') !== false ||
        strpos($cell, 'REFERENCIA') !== false
    ){
        $hasMensaje = true;
    }
}

if($hasMonto && $hasMensaje){

    $headerIndex = $i;
    $headers = $normalized;
    break;
}
    }

    if($headerIndex < 0){
        return [];
    }

    $buscarHeader = function($aliases) use ($headers){
        foreach($aliases as $alias){
            $pos = array_search(normalizar_yape($alias), $headers, true);
            if($pos !== false){
                return $pos;
            }
        }

        foreach($headers as $i => $header){
            foreach($aliases as $alias){
                if($header !== '' && strpos($header, normalizar_yape($alias)) !== false){
                    return $i;
                }
            }
        }

        return null;
    };

    $index = [
        'tipo' => $buscarHeader(['TIPO DE TRANSACCION', 'TIPO']),
        'origen' => $buscarHeader(['ORIGEN', 'REMITENTE', 'CLIENTE', 'PAGADOR']),
        'destino' => $buscarHeader(['DESTINO', 'BENEFICIARIO', 'CUENTA DESTINO']),
        'monto' => $buscarHeader(['MONTO', 'IMPORTE']),
        'mensaje' => $buscarHeader(['MENSAJE', 'GLOSA', 'DESCRIPCION', 'REFERENCIA']),
        'fecha' => $buscarHeader(['FECHA DE OPERACION', 'FECHA', 'FECHA OPERACION']),
        'operacion' => $buscarHeader([
            'NUMERO DE OPERACION',
            'NRO OPERACION',
            'NRO DE OPERACION',
            'OPERACION',
            'CODIGO OPERACION',
            'CODIGO DE OPERACION',
            'NUM OPERACION',
            'N OPERACION',
        ]),
    ];

    $valor = function($row, $key) use ($index){
        $pos = $index[$key] ?? null;
        return $pos === null ? '' : ($row[$pos] ?? '');
    };

    $items = [];
    for($i = $headerIndex + 1; $i < count($rows); $i++){
        $row = $rows[$i];
        $tipo = $valor($row, 'tipo');
        $origen = $valor($row, 'origen');
        $destino = $valor($row, 'destino');
        $monto = $valor($row, 'monto');
        $mensaje = $valor($row, 'mensaje');
        $fecha = $valor($row, 'fecha');
        $operacion = $valor($row, 'operacion');

        if(trim($tipo.$origen.$destino.$monto.$mensaje.$fecha.$operacion) === ''){
            continue;
        }

        $items[] = [
            'tipo' => trim($tipo),
            'origen' => trim($origen),
            'destino' => trim($destino),
            'monto' => monto_yape($monto),
            'mensaje' => trim($mensaje),
            'fecha' => trim($fecha),
            'operacion' => trim($operacion),
        ];
    }

    return $items;
}

function nombre_coincide($nombreInscrito, $origenYape){
    $nombre = normalizar_yape($nombreInscrito);
    $origen = normalizar_yape($origenYape);
    if($nombre === '' || $origen === ''){
        return false;
    }

    $tokensNombre = array_filter(explode(' ', $nombre), function($token){
        return strlen($token) >= 3;
    });
    $tokensOrigen = array_filter(explode(' ', $origen), function($token){
        return strlen($token) >= 3;
    });

    $coincidencias = 0;
    foreach($tokensNombre as $tokenNombre){
        foreach($tokensOrigen as $tokenOrigen){
            if($tokenNombre === $tokenOrigen || strpos($tokenNombre, $tokenOrigen) === 0 || strpos($tokenOrigen, $tokenNombre) === 0){
                $coincidencias++;
                break;
            }
        }
    }

    return $coincidencias > 0;
}

function evaluar_match_yape($inscrito, $tx){

    $score = 0;
    $razones = [];

    $codigo = normalizar_yape($inscrito['codigo'] ?? '');
    $mensaje = normalizar_yape($tx['mensaje'] ?? '');

    $operacionBanco = normalizar_yape($tx['operacion'] ?? '');
    $numeroOperacion = normalizar_yape($inscrito['numero_operacion_yape'] ?? '');

    $codigoCoincide = false;
    $montoCoincide = false;

    if(
        $codigo !== '' &&
        (
            strpos($mensaje, $codigo) !== false ||
            strpos($operacionBanco, $codigo) !== false
        )
    ){

        $codigoCoincide = true;

        $score += 100;

        $razones[] = 'Codigo encontrado';
    }

    if(
        abs(
            (float)$inscrito['monto']
            -
            (float)$tx['monto']
        ) < 0.01
    ){

        $montoCoincide = true;

        $score += 25;

        $razones[] = 'Monto exacto';
    }

    if(
        $numeroOperacion !== '' &&
        (
            $operacionBanco === $numeroOperacion ||
            strpos($mensaje, $numeroOperacion) !== false
        )
    ){

        $score += 20;

        $razones[] = 'Operacion coincide';
    }

    if(
        nombre_coincide(
            $inscrito['nombre'] ?? '',
            $tx['origen'] ?? ''
        )
    ){

        $score += 10;

        $razones[] = 'Nombre similar';
    }

    $fuerte = (
        $codigoCoincide &&
        $montoCoincide
    );

    return [
        'score' => $score,
        'razones' => $razones,
        'fuerte' => $fuerte,
        'posible' => ($score >= 25)
    ];
}

$rows = $extension === 'csv'
    ? leer_csv_yape($_FILES['archivo_yape']['tmp_name'])
    : leer_xlsx_yape($_FILES['archivo_yape']['tmp_name']);

$transacciones = transacciones_yape($rows);
$reglas = reglas_conciliacion_yape();

$pendientes = [];
$result = $conn->query("
    SELECT id, codigo, nombre, dni, telefono, monto, numero_operacion_yape, fecha_yape, fecha_registro
    FROM inscritos
    WHERE estado_pago='YAPE_PENDIENTE'
    ORDER BY fecha_yape ASC, id ASC
");

while($row = $result->fetch_assoc()){
    $pendientes[] = $row;
}

$matches = [];
$sinCoincidencia = [];
$usados = [];

foreach($pendientes as $inscrito){
    $mejor = null;
    foreach($transacciones as $txIndex => $tx){
        if(isset($usados[$txIndex])){
            continue;
        }
        $evaluacion = evaluar_match_yape($inscrito, $tx);
        if(!$evaluacion['posible']){
            continue;
        }
        if($mejor === null || $evaluacion['score'] > $mejor['score']){
            $mejor = [
                'tx_index' => $txIndex,
                'inscrito' => $inscrito,
                'tx' => $tx,
                'score' => $evaluacion['score'],
                'razones' => $evaluacion['razones'],
                'fuerte' => $evaluacion['fuerte'],
            ];
        }
    }

    if($mejor){
        $matches[] = $mejor;
        if($mejor['fuerte']){
            $usados[$mejor['tx_index']] = true;
        }
    }else{
        $sinCoincidencia[] = $inscrito;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Conciliacion Yape</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9;}
.badge-score{font-size:12px;}
.table td,.table th{vertical-align:middle;font-size:14px;}
</style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Conciliacion Yape</h1>
            <div class="text-muted">
                Archivo: <strong><?= htmlspecialchars($nombreArchivo, ENT_QUOTES, 'UTF-8'); ?></strong>.
                Transacciones leidas: <?= count($transacciones); ?>.
                Pendientes Yape: <?= count($pendientes); ?>.
            </div>
        </div>
        <a href="dashboard" class="btn btn-secondary">Volver</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 mb-3">Reglas aplicadas</h2>
            <div class="row">
                <?php foreach($reglas as $regla){ ?>
                <div class="col-md-4 mb-2">
                    <div class="border rounded p-2 bg-light">
                        <strong><?= (int)$regla['prioridad']; ?>. <?= htmlspecialchars($regla['campo'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <div class="small text-muted"><?= htmlspecialchars($regla['descripcion'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <form method="POST" action="aplicar_conciliacion_yape">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Coincidencias encontradas</h2>

                <?php if(empty($matches)){ ?>
                    <div class="alert alert-warning">No se encontraron coincidencias con las reglas actuales.</div>
                <?php }else{ ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Confirmar</th>
                                <th>Inscrito</th>
                                <th>Codigo</th>
                                <th>Monto esperado</th>
                                <th>Origen Yape</th>
                                <th>Monto Yape</th>
                                <th>Mensaje</th>
                                <th>Operacion</th>
                                <th>Fecha Yape</th>
                                <th>Validacion</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($matches as $match){ ?>
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="confirmar[]"
                                        value="<?= (int)$match['inscrito']['id']; ?>"
                                        <?= $match['fuerte'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <?= htmlspecialchars($match['inscrito']['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    <div class="small text-muted">
                                        DNI <?= htmlspecialchars($match['inscrito']['dni'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($match['inscrito']['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>S/ <?= number_format((float)$match['inscrito']['monto'], 2); ?></td>
                                <td><?= htmlspecialchars($match['tx']['origen'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>S/ <?= number_format((float)$match['tx']['monto'], 2); ?></td>
                                <td><?= htmlspecialchars($match['tx']['mensaje'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($match['tx']['operacion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($match['tx']['fecha'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge badge-score <?= $match['fuerte'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                        <?= $match['fuerte'] ? 'Fuerte' : 'Revisar'; ?> / <?= (int)$match['score']; ?>
                                    </span>
                                    <div class="small text-muted">
                                        <?= htmlspecialchars(implode(', ', $match['razones']), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-success" onclick="return confirm('Confirmar pagos seleccionados?');">
                    Confirmar pagos seleccionados
                </button>
                <?php } ?>
            </div>
        </div>
    </form>

    <?php if(!empty($sinCoincidencia)){ ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3">Yape pendientes sin coincidencia</h2>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Monto</th>
                            <th>Operacion ingresada</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($sinCoincidencia as $item){ ?>
                        <tr>
                            <td><?= htmlspecialchars($item['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item['dni'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>S/ <?= number_format((float)$item['monto'], 2); ?></td>
                            <td><?= htmlspecialchars($item['numero_operacion_yape'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
</body>
</html>
