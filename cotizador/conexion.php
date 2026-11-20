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

// Sincronizar zona horaria de Torreón, Coahuila (UTC-6)
date_default_timezone_set('America/Monterrey');
$conn->query("SET time_zone = '-06:00'");

// Configurar para que acepte acentos y caracteres latinos
$conn->set_charset("utf8mb4");