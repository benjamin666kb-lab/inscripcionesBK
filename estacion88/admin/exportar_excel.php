<?php

include("sesion_check.php");

if(!isset($_SESSION['id_admin'])){
    header("Location: login");
    exit;
}

include("../../db.php");

if(!class_exists('ZipArchive')){
    http_response_code(500);
    exit("El servidor no tiene habilitada la extension ZipArchive, necesaria para generar XLSX.");
}

function xlsx_escape($value){
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function xlsx_col($index){
    $letters = '';
    while($index > 0){
        $index--;
        $letters = chr(65 + ($index % 26)) . $letters;
        $index = intdiv($index, 26);
    }
    return $letters;
}

$headers = [
    'ID','Codigo','Nombre','DNI','Telefono','Correo','Edad',
    'Distancia','Talla','Categoria','Kit','Monto','Estado','Fecha'
];

$rows = [];
$sql = "SELECT * FROM inscritos ORDER BY id DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    $rows[] = [
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
        number_format((float)$row['monto'], 2, '.', ''),
        $row['estado_pago'],
        $row['fecha_registro']
    ];
}

$sheetRows = '';
$allRows = array_merge([$headers], $rows);

foreach($allRows as $rowIndex => $rowData){
    $excelRow = $rowIndex + 1;
    $sheetRows .= '<row r="' . $excelRow . '">';

    foreach($rowData as $colIndex => $value){
        $cell = xlsx_col($colIndex + 1) . $excelRow;
        $sheetRows .= '<c r="' . $cell . '" t="inlineStr"><is><t>' . xlsx_escape($value) . '</t></is></c>';
    }

    $sheetRows .= '</row>';
}

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
           xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheetViews>
        <sheetView workbookViewId="0">
            <pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>
        </sheetView>
    </sheetViews>
    <cols>
        <col min="1" max="1" width="8" customWidth="1"/>
        <col min="2" max="2" width="18" customWidth="1"/>
        <col min="3" max="6" width="24" customWidth="1"/>
        <col min="7" max="14" width="16" customWidth="1"/>
    </cols>
    <sheetData>' . $sheetRows . '</sheetData>
</worksheet>';

$workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Inscritos" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';

$contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>';

$relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1"
                  Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
                  Target="xl/workbook.xml"/>
</Relationships>';

$workbookRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1"
                  Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
                  Target="worksheets/sheet1.xml"/>
</Relationships>';

$tmpFile = tempnam(sys_get_temp_dir(), 'inscritos_');
$zip = new ZipArchive();

if($zip->open($tmpFile, ZipArchive::OVERWRITE) !== true){
    http_response_code(500);
    exit("No se pudo crear el archivo XLSX.");
}

$zip->addFromString('[Content_Types].xml', $contentTypesXml);
$zip->addFromString('_rels/.rels', $relsXml);
$zip->addFromString('xl/workbook.xml', $workbookXml);
$zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
$zip->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="inscritos.xlsx"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

readfile($tmpFile);
unlink($tmpFile);
exit;
