<?php
session_start();
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: panel.php");
    exit;
}

include 'conexion.php';

// Eliminar Cotización EMS
if (isset($_GET['eliminar'])) {
    $id_a_eliminar = intval($_GET['eliminar']);
    $conn->query("DELETE FROM cotizaciones WHERE id = $id_a_eliminar");
    header("Location: dashboard.php?seccion=historial");
    exit;
}

// Eliminar Cotización Solar
if (isset($_GET['eliminar_solar'])) {
    $id_solar = intval($_GET['eliminar_solar']);
    $conn->query("DELETE FROM cotizaciones_solares WHERE id = $id_solar");
    header("Location: dashboard.php?seccion=historial");
    exit;
}

// Eliminar Recibo CFE (registro y archivo físico)
if (isset($_GET['eliminar_recibo'])) {
    $id_recibo = intval($_GET['eliminar_recibo']);
    $res_file = $conn->query("SELECT archivo_ruta FROM recibos_cfe WHERE id = $id_recibo");
    if ($res_file && $res_file->num_rows > 0) {
        $fila = $res_file->fetch_assoc();
        $ruta_fisica = __DIR__ . '/' . $fila['archivo_ruta'];
        if (file_exists($ruta_fisica)) {
            unlink($ruta_fisica);
        }
    }
    $conn->query("DELETE FROM recibos_cfe WHERE id = $id_recibo");
    header("Location: dashboard.php?seccion=historial");
    exit;
}

// Lista blanca de secciones permitidas
$secciones_permitidas = ['inicio', 'recibos', 'cotizar_solares', 'cotizar_ems', 'historial'];
$seccion = (isset($_GET['seccion']) && in_array($_GET['seccion'], $secciones_permitidas))
    ? $_GET['seccion']
    : 'inicio';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | EMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    </script>
    <link rel="stylesheet" href="assets/estilos.css">
</head>

<body>

    <!-- FONDO OSCURO PARA MENÚ EN MÓVIL -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="cerrarMenuMovil()"></div>

    <!-- MENÚ LATERAL -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../img/energyblack.jpeg" alt="EMS Logo">
            <button type="button" class="sidebar-close-btn" onclick="cerrarMenuMovil()" aria-label="Cerrar menú">&times;</button>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php?seccion=inicio"
                    class="nav-link <?php echo $seccion == 'inicio' ? 'active' : ''; ?>">📊 Inicio</a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?seccion=recibos"
                    class="nav-link <?php echo $seccion == 'recibos' ? 'active' : ''; ?>">📄 Subir Recibos</a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?seccion=cotizar_solares"
                    class="nav-link <?php echo $seccion == 'cotizar_solares' ? 'active' : ''; ?>">☀️ Cotizar Paneles</a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?seccion=cotizar_ems"
                    class="nav-link <?php echo $seccion == 'cotizar_ems' ? 'active' : ''; ?>">⚡ Cotizar EMS</a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?seccion=historial"
                    class="nav-link <?php echo $seccion == 'historial' ? 'active' : ''; ?>">🗂️ Historial y Archivos</a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-avatar">A</div>
            <div class="user-details">
                <span class="user-greeting">Hola, Admin</span>
                <a href="panel.php?logout=true" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <!-- BARRA SUPERIOR SOLO VISIBLE EN CELULAR / TABLET -->
        <header class="mobile-header">
            <button type="button" class="mobile-menu-btn" onclick="abrirMenuMovil()" aria-label="Abrir menú">☰</button>
            <img src="../img/energyblack.jpeg" alt="EMS Logo" class="mobile-header-logo">
            <div class="mobile-header-spacer"></div>
        </header>

        <div class="content-area">
            <?php include "modulos/{$seccion}.php"; ?>
        </div>
    </main>

    <!-- VENTANA EMERGENTE Y SCRIPTS -->
    <?php include 'includes/modal.php'; ?>
    <script src="assets/modal.js"></script>
    <script>
        function abrirMenuMovil() {
            document.getElementById('sidebar')?.classList.add('open');
            document.getElementById('sidebarBackdrop')?.classList.add('open');
        }

        function cerrarMenuMovil() {
            document.getElementById('sidebar')?.classList.remove('open');
            document.getElementById('sidebarBackdrop')?.classList.remove('open');
        }
    </script>
</body>

</html>