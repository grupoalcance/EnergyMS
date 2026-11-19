<?php
include 'conexion.php';

// Recibir los datos enviados desde el HTML en formato JSON
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    // Limpiar los datos para mayor seguridad
    $id = isset($data['id']) ? intval($data['id']) : null;
    $folio = $conn->real_escape_string($data['folio']);
    $cliente = $conn->real_escape_string($data['cliente']);
    $direccion = $conn->real_escape_string($data['direccion']);
    $servicio = $conn->real_escape_string($data['servicio']);
    $energia = $conn->real_escape_string($data['energia']);
    $cargo = $conn->real_escape_string($data['cargo']);
    $total_cfe = $conn->real_escape_string($data['total_cfe']);
    $subtotal = $conn->real_escape_string($data['subtotal']);
    $iva = $conn->real_escape_string($data['iva']);
    $total = $conn->real_escape_string($data['total']);
    $anticipo = $conn->real_escape_string($data['anticipo']);
    $restante = $conn->real_escape_string($data['restante']);
    $agente_nombre = $conn->real_escape_string($data['agente_nombre']);
    $agente_telefono = $conn->real_escape_string($data['agente_telefono']);

    // Si viene con un ID, significa que estamos editando desde el historial (ver_pdf.php)
    if ($id) {
        $sql = "UPDATE cotizaciones SET 
                cliente = '$cliente', 
                direccion = '$direccion', 
                servicio_cfe = '$servicio', 
                energia_cfe = '$energia', 
                cargo_cfe = '$cargo', 
                total_cfe = '$total_cfe', 
                subtotal = '$subtotal', 
                iva = '$iva', 
                total = '$total', 
                anticipo = '$anticipo', 
                restante = '$restante', 
                agente_nombre = '$agente_nombre', 
                agente_telefono = '$agente_telefono' 
                WHERE id = $id";
    }
    // Si no trae ID, significa que es una cotización nueva desde el cotizador (index.html)
    else {
        $sql = "INSERT INTO cotizaciones 
                (folio, cliente, direccion, servicio_cfe, energia_cfe, cargo_cfe, total_cfe, subtotal, iva, total, anticipo, restante, agente_nombre, agente_telefono)
                VALUES 
                ('$folio', '$cliente', '$direccion', '$servicio', '$energia', '$cargo', '$total_cfe', '$subtotal', '$iva', '$total', '$anticipo', '$restante', '$agente_nombre', '$agente_telefono')";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Guardado correctamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No llegaron datos"]);
}

$conn->close();
?>