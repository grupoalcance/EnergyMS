<?php
include 'conexion.php';

// Buscar el folio más alto registrado en la base de datos
$sql = "SELECT folio FROM cotizaciones ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

$siguiente_folio = 100; // Si la base está vacía o el número es menor, empezamos en 100

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ultimo_folio = intval($row['folio']);

    // Si el último folio guardado ya es 100 o mayor, seguimos sumando a partir de ese
    if ($ultimo_folio >= 100) {
        $siguiente_folio = $ultimo_folio + 1;
    }
}

// Devolver el número al cotizador
echo json_encode(["siguiente_folio" => $siguiente_folio]);
$conn->close();
?>