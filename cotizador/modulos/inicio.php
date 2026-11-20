<?php
if (!isset($conn)) {
    header("Location: ../dashboard.php?seccion=inicio");
    exit;
}
?>

<h1 class="section-heading">Dashboard General</h1>
<div class="card">
    <h2 style="margin-top: 0; color: var(--text-main);">Bienvenido al Sistema EMS</h2>
    <p style="color: var(--text-muted); margin-bottom: 24px;">Selecciona una opción del menú lateral o entra
        directamente a un módulo:</p>

    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        <a href="dashboard.php?seccion=recibos" class="btn-primary-ems" style="text-decoration: none;">
            <span>📄</span> Subir Recibo CFE
        </a>
        <a href="dashboard.php?seccion=cotizar_solares" class="btn-primary-ems" style="text-decoration: none;">
            <span>☀️</span> Cotizar Paneles Solares
        </a>
        <a href="dashboard.php?seccion=cotizar_ems" class="btn-primary-ems" style="text-decoration: none;">
            <span>⚡</span> Cotizar Aparato EMS
        </a>
        <a href="dashboard.php?seccion=historial" class="btn-primary-ems"
            style="text-decoration: none; background-color: #374151;">
            <span>🗂️</span> Ver Historial
        </a>
    </div>
</div>