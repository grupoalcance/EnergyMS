<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

include 'conexion.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

$id               = isset($input['id']) ? intval($input['id']) : 0;
$cliente          = trim($input['cliente'] ?? '');
$servicio_cfe     = trim($input['servicio_cfe'] ?? '');
$paneles          = intval($input['paneles'] ?? 0);
$watts_panel      = intval($input['watts_panel'] ?? 600);
$potencia_kw      = trim($input['potencia_kw'] ?? '');
$metros_cuadrados = trim($input['metros_cuadrados'] ?? '');
$inversion_rango  = trim($input['inversion_rango'] ?? '');
$agente_nombre    = trim($input['agente_nombre'] ?? '');
$agente_telefono  = trim($input['agente_telefono'] ?? '');

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE cotizaciones_solares SET cliente=?, servicio_cfe=?, paneles=?, watts_panel=?, potencia_kw=?, metros_cuadrados=?, inversion_rango=?, agente_nombre=?, agente_telefono=? WHERE id=?");
    $stmt->bind_param("ssiisssssi", $cliente, $servicio_cfe, $paneles, $watts_panel, $potencia_kw, $metros_cuadrados, $inversion_rango, $agente_nombre, $agente_telefono, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'id' => $id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

// Nueva cotización solar (folio iniciando en PS-0100)
$stmt = $conn->prepare("INSERT INTO cotizaciones_solares (folio, cliente, servicio_cfe, paneles, watts_panel, potencia_kw, metros_cuadrados, inversion_rango, agente_nombre, agente_telefono) VALUES ('TEMP', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiisssss", $cliente, $servicio_cfe, $paneles, $watts_panel, $potencia_kw, $metros_cuadrados, $inversion_rango, $agente_nombre, $agente_telefono);

if ($stmt->execute()) {
    $nuevo_id = $stmt->insert_id;
    $numero_folio = 99 + $nuevo_id;
    $folio_final = 'PS-' . str_pad($numero_folio, 4, '0', STR_PAD_LEFT);
    $conn->query("UPDATE cotizaciones_solares SET folio = '$folio_final' WHERE id = $nuevo_id");

    echo json_encode(['status' => 'success', 'id' => $nuevo_id, 'folio' => $folio_final]);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
