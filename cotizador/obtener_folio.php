<?php
include 'conexion.php';

// Buscar el folio más alto registrado en la base de datos
$sql = "SELECT folio FROM cotizaciones ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

$siguiente_folio = 1; // Si la base está vacía, empezamos en 1

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ultimo_folio = intval($row['folio']);
    $siguiente_folio = $ultimo_folio + 1;
}

// Devolver el número al cotizador
echo json_encode(["siguiente_folio" => $siguiente_folio]);
$conn->close();
