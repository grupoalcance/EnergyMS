<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';
include 'includes/flujo_helper.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $id                = isset($data['id']) ? intval($data['id']) : null;
    $folio             = $conn->real_escape_string($data['folio'] ?? '');
    $cliente_raw       = trim($data['cliente'] ?? '');
    $servicio_raw      = trim($data['servicio'] ?? '');
    $cliente           = $conn->real_escape_string($cliente_raw);
    $direccion         = $conn->real_escape_string($data['direccion'] ?? '');
    $servicio          = $conn->real_escape_string($servicio_raw);
    $tipo_demanda      = $conn->real_escape_string($data['tipo_demanda'] ?? '');
    $energia           = $conn->real_escape_string($data['energia'] ?? '');
    $cargo             = $conn->real_escape_string($data['cargo'] ?? '');
    $total_cfe         = $conn->real_escape_string($data['total_cfe'] ?? '');
    $subtotal          = $conn->real_escape_string($data['subtotal'] ?? '');
    $iva               = $conn->real_escape_string($data['iva'] ?? '');
    $total             = $conn->real_escape_string($data['total'] ?? '');
    $anticipo          = $conn->real_escape_string($data['anticipo'] ?? '');
    $restante          = $conn->real_escape_string($data['restante'] ?? '');
    $dato_fabricacion  = $conn->real_escape_string($data['dato_fabricacion'] ?? '');
    $costo_fabricacion = $conn->real_escape_string($data['costo_fabricacion'] ?? '');
    $agente_nombre     = $conn->real_escape_string($data['agente_nombre'] ?? '');
    $agente_telefono   = $conn->real_escape_string($data['agente_telefono'] ?? '');

    if ($id) {
        $sql = "UPDATE cotizaciones SET 
                cliente = '$cliente', 
                direccion = '$direccion', 
                servicio_cfe = '$servicio', 
                tipo_demanda = '$tipo_demanda',
                energia_cfe = '$energia', 
                cargo_cfe = '$cargo', 
                total_cfe = '$total_cfe', 
                subtotal = '$subtotal', 
                iva = '$iva', 
                total = '$total', 
                anticipo = '$anticipo', 
                restante = '$restante', 
                dato_fabricacion = '$dato_fabricacion',
                costo_fabricacion = '$costo_fabricacion',
                agente_nombre = '$agente_nombre', 
                agente_telefono = '$agente_telefono' 
                WHERE id = $id";
    } else {
        $sql = "INSERT INTO cotizaciones 
                (folio, cliente, direccion, servicio_cfe, tipo_demanda, energia_cfe, cargo_cfe, total_cfe, subtotal, iva, total, anticipo, restante, dato_fabricacion, costo_fabricacion, agente_nombre, agente_telefono)
                VALUES 
                ('$folio', '$cliente', '$direccion', '$servicio', '$tipo_demanda', '$energia', '$cargo', '$total_cfe', '$subtotal', '$iva', '$total', '$anticipo', '$restante', '$dato_fabricacion', '$costo_fabricacion', '$agente_nombre', '$agente_telefono')";
    }

    if ($conn->query($sql) === TRUE) {
        $saved_id = $id ? $id : $conn->insert_id;

        // Pasar expediente automáticamente a Cotización Completa
        avanzarEstatusExpediente($conn, $cliente_raw, $servicio_raw, 'Cotización Completa');

        echo json_encode(["status" => "success", "id" => $saved_id, "message" => "Guardado correctamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No llegaron datos"]);
}

$conn->close();
