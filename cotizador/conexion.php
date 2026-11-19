<?php
// Credenciales exactas de tu Hostinger
$host = "195.35.61.75";
$user = "u492244081_ventas";
$pass = "En3rgyMS!";
$db = "u492244081_energy_ms";

// Crear la conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar si hay errores
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar para que acepte acentos y caracteres latinos
$conn->set_charset("utf8");
