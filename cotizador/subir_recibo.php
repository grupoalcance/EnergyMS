<?php
session_start();
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: panel.php");
    exit;
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente      = trim($_POST['cliente'] ?? '');
    $direccion    = trim($_POST['direccion'] ?? '');
    $servicio     = trim($_POST['servicio_cfe'] ?? '');
    $tipo_demanda = trim($_POST['tipo_demanda'] ?? '');
    $agente       = trim($_POST['agente_nombre'] ?? '');
    $monto        = '';

    $demandas_validas = ['Gran Demanda', 'Baja Demanda'];

    if (
        $cliente === '' ||
        !in_array($tipo_demanda, $demandas_validas, true) ||
        !isset($_FILES['archivo_recibo']) ||
        $_FILES['archivo_recibo']['error'] !== UPLOAD_ERR_OK
    ) {
        header("Location: dashboard.php?seccion=recibos&error=datos");
        exit;
    }

    $archivo = $_FILES['archivo_recibo'];
    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];

    if (!in_array($ext, $permitidos)) {
        header("Location: dashboard.php?seccion=recibos&error=formato");
        exit;
    }

    $carpeta_destino = __DIR__ . '/recibos/';
    if (!is_dir($carpeta_destino)) {
        mkdir($carpeta_destino, 0755, true);
    }

    $nombre_archivo = 'recibo_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
    $ruta_completa  = $carpeta_destino . $nombre_archivo;
    $ruta_relativa  = 'recibos/' . $nombre_archivo;
    $tipo_archivo   = ($ext === 'pdf') ? 'pdf' : 'imagen';

    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        $stmt = $conn->prepare("INSERT INTO recibos_cfe (folio, cliente, direccion, servicio_cfe, tipo_demanda, monto, agente_nombre, archivo_ruta, archivo_tipo) VALUES ('TEMP', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $cliente, $direccion, $servicio, $tipo_demanda, $monto, $agente, $ruta_relativa, $tipo_archivo);

        if ($stmt->execute()) {
            $nuevo_id = $stmt->insert_id;
            $folio_final = 'REC-' . str_pad($nuevo_id, 4, '0', STR_PAD_LEFT);
            $conn->query("UPDATE recibos_cfe SET folio = '$folio_final' WHERE id = $nuevo_id");

            header("Location: dashboard.php?seccion=recibos&ok=1&folio=" . urlencode($folio_final));
            exit;
        }
    }

    header("Location: dashboard.php?seccion=recibos&error=subida");
    exit;
}
