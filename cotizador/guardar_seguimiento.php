<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    echo json_encode(["status" => "error", "message" => "Sesión no válida"]);
    exit;
}

include 'conexion.php';

$clave_exp = isset($_POST['clave_exp']) ? $conn->real_escape_string(trim($_POST['clave_exp'])) : '';
if ($clave_exp === '') {
    echo json_encode(["status" => "error", "message" => "Clave de expediente vacía"]);
    exit;
}

// Consultar registro previo para conservar rutas de comprobantes si no se sube uno nuevo
$prev = null;
$res_prev = @$conn->query("SELECT * FROM expedientes_seguimiento WHERE clave_exp = '$clave_exp' LIMIT 1");
if ($res_prev && $res_prev->num_rows > 0) {
    $prev = $res_prev->fetch_assoc();
}

$estatus            = $conn->real_escape_string($_POST['estatus'] ?? 'Prospecto');
$anticipo_pagado    = !empty($_POST['anticipo_pagado']) ? 1 : 0;
$anticipo_fecha     = !empty($_POST['anticipo_fecha']) ? "'" . $conn->real_escape_string($_POST['anticipo_fecha']) . "'" : "NULL";
$anticipo_metodo    = $conn->real_escape_string($_POST['anticipo_metodo'] ?? '');
$liquidacion_pagada = !empty($_POST['liquidacion_pagada']) ? 1 : 0;
$liquidacion_fecha  = !empty($_POST['liquidacion_fecha']) ? "'" . $conn->real_escape_string($_POST['liquidacion_fecha']) . "'" : "NULL";
$liquidacion_metodo = $conn->real_escape_string($_POST['liquidacion_metodo'] ?? '');
$notas              = $conn->real_escape_string($_POST['notas'] ?? '');

// Ajustar estatus automáticamente según pagos marcados
if ($liquidacion_pagada && in_array($estatus, ['Prospecto', 'Cotización Enviada', 'Anticipo 60% Pagado', 'En Fabricación'])) {
    $estatus = 'Instalado / Liquidado';
} elseif ($anticipo_pagado && in_array($estatus, ['Prospecto', 'Cotización Enviada'])) {
    $estatus = 'Anticipo 60% Pagado';
}

$dir_comp = __DIR__ . '/comprobantes/';
if (!is_dir($dir_comp)) {
    @mkdir($dir_comp, 0775, true);
}

$subirComprobante = function ($campo_file, $prefijo, $ruta_anterior) use ($dir_comp, $conn) {
    if (isset($_FILES[$campo_file]) && $_FILES[$campo_file]['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES[$campo_file]['name'], PATHINFO_EXTENSION));
        $permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $permitidos)) {
            $nombre = $prefijo . '_' . date('Ymd_His') . '_' . substr(uniqid(), -6) . '.' . $ext;
            if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], $dir_comp . $nombre)) {
                return $conn->real_escape_string('comprobantes/' . $nombre);
            }
        }
    }
    return $conn->real_escape_string($ruta_anterior ?? '');
};

$ant_comp = $subirComprobante('file_anticipo', 'ant', $prev['anticipo_comprobante'] ?? '');
$liq_comp = $subirComprobante('file_liquidacion', 'liq', $prev['liquidacion_comprobante'] ?? '');

$sql = "INSERT INTO expedientes_seguimiento 
        (clave_exp, estatus, anticipo_pagado, anticipo_fecha, anticipo_metodo, anticipo_comprobante, liquidacion_pagada, liquidacion_fecha, liquidacion_metodo, liquidacion_comprobante, notas)
        VALUES 
        ('$clave_exp', '$estatus', $anticipo_pagado, $anticipo_fecha, '$anticipo_metodo', '$ant_comp', $liquidacion_pagada, $liquidacion_fecha, '$liquidacion_metodo', '$liq_comp', '$notas')
        ON DUPLICATE KEY UPDATE
        estatus = '$estatus',
        anticipo_pagado = $anticipo_pagado,
        anticipo_fecha = $anticipo_fecha,
        anticipo_metodo = '$anticipo_metodo',
        anticipo_comprobante = '$ant_comp',
        liquidacion_pagada = $liquidacion_pagada,
        liquidacion_fecha = $liquidacion_fecha,
        liquidacion_metodo = '$liquidacion_metodo',
        liquidacion_comprobante = '$liq_comp',
        notas = '$notas'";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "success",
        "estatus" => $estatus,
        "anticipo_comprobante" => $ant_comp,
        "liquidacion_comprobante" => $liq_comp
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Error BD: " . $conn->error]);
}

$conn->close();
