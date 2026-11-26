<?php
if (!isset($conn)) {
    header("Location: ../dashboard.php?seccion=historial");
    exit;
}

$f_n = function ($v) {
    $n = floatval(preg_replace('/[^0-9.-]/', '', (string)$v));
    return number_format($n, 2, '.', ',');
};

$expedientes = [];

$obtenerClave = function ($cliente, $servicio) {
    $serv_limpio = preg_replace('/[^0-9]/', '', (string)$servicio);
    if (strlen($serv_limpio) >= 6) {
        return 'srv_' . $serv_limpio;
    }
    $nom_limpio = strtolower(trim(preg_replace('/\s+/', ' ', (string)$cliente)));
    return 'cli_' . md5($nom_limpio ?: 'sin_nombre');
};

$asegurarExpediente = function (&$expedientes, $clave, $cliente, $servicio, $demanda, $fecha) {
    if (!isset($expedientes[$clave])) {
        $expedientes[$clave] = [
            'clave'        => $clave,
            'cliente'      => $cliente ?: 'Sin nombre',
            'servicio'     => $servicio ?: '',
            'tipo_demanda' => $demanda ?: '',
            'ultima_fecha' => $fecha,
            'recibo_id'    => 0,
            'solar_id'     => 0,
            'ems_id'       => 0,
            'recibo_doc'   => null,
            'solar_doc'    => null,
            'ems_doc'      => null,
            'docs'         => []
        ];
    } else {
        if (empty($expedientes[$clave]['servicio']) && !empty($servicio)) {
            $expedientes[$clave]['servicio'] = $servicio;
        }
        if (empty($expedientes[$clave]['tipo_demanda']) && !empty($demanda)) {
            $expedientes[$clave]['tipo_demanda'] = $demanda;
        }
        if (strtotime($fecha) > strtotime($expedientes[$clave]['ultima_fecha'])) {
            $expedientes[$clave]['ultima_fecha'] = $fecha;
        }
    }
};

// 1. Recibos CFE
$rr = @$conn->query("SELECT * FROM recibos_cfe ORDER BY fecha DESC");
if ($rr && $rr->num_rows > 0) {
    while ($r = $rr->fetch_assoc()) {
        $demanda = $r['tipo_demanda'] ?? '';
        $clave = $obtenerClave($r['cliente'], $r['servicio_cfe']);
        $asegurarExpediente($expedientes, $clave, $r['cliente'], $r['servicio_cfe'], $demanda, $r['fecha']);

        $payload = [
            'tipo'         => 'recibo',
            'id'           => $r['id'],
            'folio'        => $r['folio'],
            'cliente'      => $r['cliente'],
            'direccion'    => $r['direccion'],
            'servicio'     => $r['servicio_cfe'],
            'tipo_demanda' => $demanda,
            'monto'        => '—',
            'agente'       => $r['agente_nombre'],
            'archivo_ruta' => $r['archivo_ruta'],
            'archivo_tipo' => $r['archivo_tipo']
        ];

        if ($expedientes[$clave]['recibo_id'] === 0) {
            $expedientes[$clave]['recibo_id'] = intval($r['id']);
            $expedientes[$clave]['recibo_doc'] = $payload;
        }

        $expedientes[$clave]['docs'][] = [
            'tipo'         => 'recibo',
            'tipo_demanda' => $demanda,
            'id'           => $r['id'],
            'f'            => $r['folio'],
            'fecha'        => $r['fecha'],
            'c'            => $r['cliente'],
            'a'            => $r['agente_nombre'],
            'm'            => '—',
            'p'            => $payload
        ];
    }
}

// 2. Cotizaciones Paneles Solares
$rs = @$conn->query("SELECT * FROM cotizaciones_solares ORDER BY fecha DESC");
if ($rs && $rs->num_rows > 0) {
    while ($r = $rs->fetch_assoc()) {
        $clave = $obtenerClave($r['cliente'], $r['servicio_cfe']);
        $asegurarExpediente($expedientes, $clave, $r['cliente'], $r['servicio_cfe'], '', $r['fecha']);

        $payload = [
            'tipo'             => 'solar',
            'id'               => $r['id'],
            'folio'            => $r['folio'],
            'cliente'          => $r['cliente'],
            'servicio'         => $r['servicio_cfe'],
            'paneles'          => $r['paneles'],
            'watts_panel'      => $r['watts_panel'],
            'potencia_kw'      => $r['potencia_kw'],
            'metros_cuadrados' => $r['metros_cuadrados'] ?? '',
            'inversion_rango'  => $r['inversion_rango'],
            'agente'           => $r['agente_nombre'],
            'telefono'         => $r['agente_telefono']
        ];

        if ($expedientes[$clave]['solar_id'] === 0) {
            $expedientes[$clave]['solar_id'] = intval($r['id']);
            $expedientes[$clave]['solar_doc'] = $payload;
        }

        $expedientes[$clave]['docs'][] = [
            'tipo'  => 'solar',
            'id'    => $r['id'],
            'f'     => $r['folio'],
            'fecha' => $r['fecha'],
            'c'     => $r['cliente'],
            'a'     => $r['agente_nombre'],
            'm'     => $r['inversion_rango'],
            'p'     => $payload
        ];
    }
}

// 3. Cotizaciones EMS
$re = @$conn->query("SELECT * FROM cotizaciones ORDER BY fecha DESC");
if ($re && $re->num_rows > 0) {
    while ($r = $re->fetch_assoc()) {
        $demanda = $r['tipo_demanda'] ?? '';
        $clave = $obtenerClave($r['cliente'], $r['servicio_cfe']);
        $asegurarExpediente($expedientes, $clave, $r['cliente'], $r['servicio_cfe'], $demanda, $r['fecha']);

        $e = floatval(preg_replace('/[^0-9.-]/', '', $r["energia_cfe"]));
        $c = floatval(preg_replace('/[^0-9.-]/', '', $r["cargo_cfe"]));
        $ic = ($e + $c) * 0.16;

        $payload = [
            'tipo'              => 'ems',
            'id'                => $r['id'],
            'folio'             => $r['folio'],
            'cliente'           => $r['cliente'],
            'direccion'         => $r['direccion'],
            'servicio'          => $r['servicio_cfe'],
            'tipo_demanda'      => $demanda,
            'energia'           => $f_n($r['energia_cfe']),
            'cargo'             => $f_n($r['cargo_cfe']),
            'iva_cfe'           => number_format($ic, 2, '.', ','),
            'total_cfe'         => $f_n($r['total_cfe']),
            'subtotal'          => $f_n($r['subtotal']),
            'iva'               => $f_n($r['iva']),
            'total'             => $f_n($r['total']),
            'anticipo'          => $f_n($r['anticipo']),
            'restante'          => $f_n($r['restante']),
            'dato_fabricacion'  => $r['dato_fabricacion'] ?? '',
            'costo_fabricacion' => $r['costo_fabricacion'] ?? '',
            'agente'            => $r['agente_nombre'],
            'telefono'          => $r['agente_telefono']
        ];

        if ($expedientes[$clave]['ems_id'] === 0) {
            $expedientes[$clave]['ems_id'] = intval($r['id']);
            $expedientes[$clave]['ems_doc'] = $payload;
        }

        $expedientes[$clave]['docs'][] = [
            'tipo'  => 'ems',
            'id'    => $r['id'],
            'f'     => $r['folio'],
            'fecha' => $r['fecha'],
            'c'     => $r['cliente'],
            'a'     => $r['agente_nombre'],
            'm'     => $r['total'],
            'fab'   => $r['costo_fabricacion'] ?? '',
            'p'     => $payload
        ];
    }
}

// 4. Cargar Seguimiento y Cobranza por expediente
$seguimientos = [];
$rseg = @$conn->query("SELECT * FROM expedientes_seguimiento");
if ($rseg && $rseg->num_rows > 0) {
    while ($s = $rseg->fetch_assoc()) {
        $seguimientos[$s['clave_exp']] = $s;
    }
}

// Ordenar expedientes por fecha más reciente
uasort($expedientes, function ($a, $b) {
    return strtotime($b['ultima_fecha']) - strtotime($a['ultima_fecha']);
});

$lista_estatus = [
    'Prospecto',
    'Cotización Enviada',
    'Anticipo 60% Pagado',
    'En Fabricación',
    'Instalado / Liquidado',
    'Descartado'
];

$metodos_pago = ['', 'Transferencia', 'Depósito', 'Efectivo', 'Cheque', 'Tarjeta'];
?>

<style>
    .historial-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .toolbar-left {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-exp,
    .select-estatus-filter {
        padding: 9px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13.5px;
        font-family: inherit;
        outline: none;
        background: #fff;
    }

    .search-exp {
        min-width: 240px;
    }

    .search-exp:focus,
    .select-estatus-filter:focus {
        border-color: var(--brand-green);
    }

    .filter-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .filter-pill {
        background-color: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        padding: 7px 14px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s;
    }

    .filter-pill.active {
        background-color: #111827;
        color: #ffffff;
        border-color: #111827;
    }

    .exp-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .exp-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background-color: #ffffff;
        transition: box-shadow 0.2s;
    }

    .exp-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .exp-header {
        padding: 15px 20px;
        background-color: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        cursor: pointer;
        border-bottom: 1px solid #e5e7eb;
    }

    .exp-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .exp-client-name {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .exp-meta {
        font-size: 12.5px;
        color: #6b7280;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .exp-right {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Selector de estatus con color dinámico */
    .status-select {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px solid #d1d5db;
        font-family: inherit;
        cursor: pointer;
        outline: none;
    }

    .status-select[data-st="Prospecto"] {
        background: #f3f4f6;
        color: #374151;
        border-color: #d1d5db;
    }

    .status-select[data-st="Cotización Enviada"] {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .status-select[data-st="Anticipo 60% Pagado"] {
        background: #fefce8;
        color: #a16207;
        border-color: #fde047;
    }

    .status-select[data-st="En Fabricación"] {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }

    .status-select[data-st="Instalado / Liquidado"] {
        background: #f0fdf4;
        color: #15803d;
        border-color: #86efac;
    }

    .status-select[data-st="Descartado"] {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .cobranza-mini-tag {
        font-size: 11.5px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
    }

    .cob-pendiente {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .cob-liquidado {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .doc-pill {
        font-size: 12px;
        font-weight: 700;
        padding: 6px 11px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        color: #9ca3af;
        font-family: inherit;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    button.doc-pill {
        cursor: pointer;
    }

    button.doc-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .doc-pill.ok-rec {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .doc-pill.ok-sol {
        background: #fefce8;
        color: #a16207;
        border-color: #fde047;
    }

    .doc-pill.ok-ems {
        background: #f0fdf4;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .btn-exp-print {
        background-color: #111827;
        color: #ffffff;
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }

    .btn-exp-print:hover {
        background-color: #374151;
    }

    .badge.gran-demanda {
        background-color: #fffbeb;
        color: #b45309;
        border: 1px solid #fcd34d;
    }

    .badge.baja-demanda {
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .exp-body {
        display: none;
        padding: 0;
    }

    .exp-card.open .exp-body {
        display: block;
    }

    /* Panel CRM + Cobranza dentro del expediente */
    .crm-panel {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 20px;
        display: grid;
        grid-template-columns: 1.45fr 1fr;
        gap: 18px;
    }

    .crm-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 16px;
    }

    .crm-box-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        color: #111827;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 8px;
    }

    .pay-stage {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #f9fafb;
    }

    .pay-stage.paid {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .pay-stage-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }

    .pay-chk-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-weight: 700;
        font-size: 13.5px;
        color: #111827;
        cursor: pointer;
    }

    .pay-amount-tag {
        font-size: 14px;
        font-weight: 800;
        color: #15803d;
    }

    .pay-controls {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 8px;
        align-items: center;
    }

    .pay-controls input[type="date"],
    .pay-controls select {
        padding: 6px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 12.5px;
        font-family: inherit;
        background: #fff;
    }

    .comp-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-file-mini {
        background: #e2e8f0;
        color: #1e293b;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-file-mini:hover {
        background: #cbd5e1;
    }

    .link-comp {
        font-size: 12px;
        font-weight: 700;
        color: #2563eb;
        text-decoration: none;
        white-space: nowrap;
    }

    .link-comp:hover {
        text-decoration: underline;
    }

    .crm-notes-area {
        width: 100%;
        min-height: 105px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        padding: 9px 11px;
        font-family: inherit;
        font-size: 13px;
        resize: vertical;
        outline: none;
    }

    .crm-notes-area:focus {
        border-color: var(--brand-green);
    }

    .crm-footer-save {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }

    .btn-save-crm {
        background: #111827;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
    }

    .btn-save-crm:hover {
        background: #374151;
    }

    .crm-saved-msg {
        font-size: 12px;
        font-weight: 700;
        color: #15803d;
        display: none;
    }

    @media (max-width: 900px) {
        .crm-panel {
            grid-template-columns: 1fr;
        }

        .pay-controls {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 820px) {

        .search-exp,
        .select-estatus-filter {
            width: 100%;
        }

        .exp-header {
            padding: 13px 14px;
        }

        .btn-exp-print {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<h1 class="section-heading">Expedientes de Clientes</h1>
<div class="card">
    <div class="historial-toolbar">
        <div class="toolbar-left">
            <input type="text" id="buscador-exp" class="search-exp" placeholder="🔍 Buscar cliente o No. de Servicio..." oninput="filtrarExpedientes()">
            <select id="filtro-estatus-exp" class="select-estatus-filter" onchange="filtrarExpedientes()">
                <option value="">📌 Todos los estatus</option>
                <?php foreach ($lista_estatus as $st_opt): ?>
                    <option value="<?php echo htmlspecialchars($st_opt); ?>"><?php echo htmlspecialchars($st_opt); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-pills" id="historial-filters">
            <button type="button" class="filter-pill active" onclick="setFiltroExp('todos', this)">Todos</button>
            <button type="button" class="filter-pill" onclick="setFiltroExp('por_cobrar', this)">💰 Cobranza Pendiente</button>
            <button type="button" class="filter-pill" onclick="setFiltroExp('gran_demanda', this)">🏭 Gran Demanda</button>
            <button type="button" class="filter-pill" onclick="setFiltroExp('baja_demanda', this)">🏠 Baja Demanda</button>
            <button type="button" class="filter-pill" onclick="setFiltroExp('completos', this)">✅ Completos (3/3)</button>
        </div>
    </div>

    <div class="exp-list" id="lista-expedientes">
        <?php if (count($expedientes) > 0): ?>
            <?php foreach ($expedientes as $exp): ?>
                <?php
                $clave = $exp['clave'];
                $seg = $seguimientos[$clave] ?? [
                    'estatus'                 => 'Prospecto',
                    'anticipo_pagado'         => 0,
                    'anticipo_fecha'          => '',
                    'anticipo_metodo'         => '',
                    'anticipo_comprobante'    => '',
                    'liquidacion_pagada'      => 0,
                    'liquidacion_fecha'       => '',
                    'liquidacion_metodo'      => '',
                    'liquidacion_comprobante' => '',
                    'notas'                   => ''
                ];

                $estatus_actual = $seg['estatus'] ?: 'Prospecto';
                $ant_ok = intval($seg['anticipo_pagado']) === 1;
                $liq_ok = intval($seg['liquidacion_pagada']) === 1;

                // Montos de referencia desde la cotización EMS (si existe)
                $monto_total_ems = $exp['ems_doc'] ? '$' . $exp['ems_doc']['total'] : '$0.00';
                $monto_ant_ems   = $exp['ems_doc'] ? '$' . $exp['ems_doc']['anticipo'] : 'Sin cotizar EMS';
                $monto_res_ems   = $exp['ems_doc'] ? '$' . $exp['ems_doc']['restante'] : 'Sin cotizar EMS';

                // Calcular saldo pendiente para etiqueta rápida
                $tiene_saldo_pendiente = '0';
                $etiqueta_cobranza = '';
                if ($exp['ems_doc'] && $estatus_actual !== 'Descartado') {
                    $ant_f = floatval(str_replace(',', '', $exp['ems_doc']['anticipo']));
                    $res_f = floatval(str_replace(',', '', $exp['ems_doc']['restante']));

                    if ($ant_ok && $liq_ok) {
                        $etiqueta_cobranza = "<span class='cobranza-mini-tag cob-liquidado'>✓ 100% Liquidado</span>";
                    } elseif ($ant_ok && !$liq_ok) {
                        $tiene_saldo_pendiente = '1';
                        $etiqueta_cobranza = "<span class='cobranza-mini-tag cob-pendiente'>Resta 40%: $" . number_format($res_f, 2) . "</span>";
                    } elseif (!$ant_ok && $estatus_actual !== 'Prospecto') {
                        $tiene_saldo_pendiente = '1';
                        $etiqueta_cobranza = "<span class='cobranza-mini-tag cob-pendiente'>Pendiente 60%: $" . number_format($ant_f, 2) . "</span>";
                    }
                }

                $dem = $exp['tipo_demanda'];
                $cat_dem = ($dem === 'Gran Demanda') ? 'gran_demanda' : (($dem === 'Baja Demanda') ? 'baja_demanda' : 'otro');
                $es_completo = ($exp['recibo_id'] > 0 && $exp['solar_id'] > 0 && $exp['ems_id'] > 0) ? '1' : '0';
                $url_pdf = "expediente_pdf.php?r={$exp['recibo_id']}&s={$exp['solar_id']}&e={$exp['ems_id']}";
                $texto_busqueda = strtolower($exp['cliente'] . ' ' . $exp['servicio']);
                ?>
                <div class="exp-card"
                    id="card-<?php echo htmlspecialchars($clave); ?>"
                    data-demanda="<?php echo $cat_dem; ?>"
                    data-completo="<?php echo $es_completo; ?>"
                    data-estatus="<?php echo htmlspecialchars($estatus_actual); ?>"
                    data-porcobrar="<?php echo $tiene_saldo_pendiente; ?>"
                    data-search="<?php echo htmlspecialchars($texto_busqueda, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="exp-header" onclick="this.parentElement.classList.toggle('open')">
                        <div class="exp-info">
                            <div class="exp-client-name">
                                📁 <?php echo htmlspecialchars($exp['cliente']); ?>
                                <?php if ($dem === 'Gran Demanda'): ?>
                                    <span class="badge gran-demanda">Gran Demanda</span>
                                <?php elseif ($dem === 'Baja Demanda'): ?>
                                    <span class="badge baja-demanda">Baja Demanda</span>
                                <?php endif; ?>

                                <!-- Selector rápido de Estatus -->
                                <select class="status-select"
                                    id="st-head-<?php echo htmlspecialchars($clave); ?>"
                                    data-st="<?php echo htmlspecialchars($estatus_actual); ?>"
                                    onclick="event.stopPropagation()"
                                    onchange="cambioRapidoEstatus('<?php echo htmlspecialchars($clave); ?>', this)">
                                    <?php foreach ($lista_estatus as $st_item): ?>
                                        <option value="<?php echo htmlspecialchars($st_item); ?>" <?php echo ($estatus_actual === $st_item) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($st_item); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="exp-meta">
                                <span><strong>Servicio CFE:</strong> <?php echo $exp['servicio'] ? htmlspecialchars($exp['servicio']) : 'Sin registrar'; ?></span>
                                <span><strong>Última actividad:</strong> <?php echo date("d/m/Y h:i A", strtotime($exp['ultima_fecha'])); ?></span>
                                <?php echo $etiqueta_cobranza; ?>
                            </div>
                        </div>

                        <div class="exp-right" onclick="event.stopPropagation()">
                            <?php if ($exp['recibo_doc']): ?>
                                <button type="button" class="doc-pill ok-rec" onclick="abrirModal(this)"
                                    data-doc="<?php echo htmlspecialchars(json_encode($exp['recibo_doc']), ENT_QUOTES, 'UTF-8'); ?>"
                                    title="Ver Recibo CFE">📄 Recibo</button>
                            <?php else: ?>
                                <span class="doc-pill" title="Sin recibo registrado">📄 Recibo</span>
                            <?php endif; ?>

                            <?php if ($exp['solar_doc']): ?>
                                <button type="button" id="btn-pill-solar-<?php echo $exp['solar_id']; ?>" class="doc-pill ok-sol" onclick="abrirModal(this)"
                                    data-doc="<?php echo htmlspecialchars(json_encode($exp['solar_doc']), ENT_QUOTES, 'UTF-8'); ?>"
                                    title="Ver Cotización Paneles Solares">☀️ Paneles</button>
                            <?php else: ?>
                                <span class="doc-pill" title="Sin cotización solar">☀️ Paneles</span>
                            <?php endif; ?>

                            <?php if ($exp['ems_doc']): ?>
                                <button type="button" id="btn-pill-ems-<?php echo $exp['ems_id']; ?>" class="doc-pill ok-ems" onclick="abrirModal(this)"
                                    data-doc="<?php echo htmlspecialchars(json_encode($exp['ems_doc']), ENT_QUOTES, 'UTF-8'); ?>"
                                    title="Ver Cotización EMS">⚡ EMS</button>
                            <?php else: ?>
                                <span class="doc-pill" title="Sin cotización EMS">⚡ EMS</span>
                            <?php endif; ?>

                            <a href="<?php echo $url_pdf; ?>" target="_blank" class="btn-exp-print">🖨️ Imprimir Expediente</a>
                        </div>
                    </div>

                    <div class="exp-body">
                        <!-- PANEL DE COBRANZA Y SEGUIMIENTO -->
                        <form class="crm-panel" id="form-seg-<?php echo htmlspecialchars($clave); ?>" onsubmit="guardarSeguimientoExp(event, '<?php echo htmlspecialchars($clave); ?>')">
                            <input type="hidden" name="clave_exp" value="<?php echo htmlspecialchars($clave); ?>">
                            <input type="hidden" name="estatus" id="st-hidden-<?php echo htmlspecialchars($clave); ?>" value="<?php echo htmlspecialchars($estatus_actual); ?>">

                            <!-- Columna 1: Control de Cobranza 60% / 40% -->
                            <div class="crm-box">
                                <div class="crm-box-title">
                                    <span>💰 Control de Cobranza (60% / 40%)</span>
                                    <?php if ($exp['ems_doc']): ?>
                                        <span style="color:#4b5563; font-size:12px;">Total EMS: <?php echo htmlspecialchars($monto_total_ems); ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Anticipo 60% -->
                                <div class="pay-stage <?php echo $ant_ok ? 'paid' : ''; ?>" id="stage-ant-<?php echo htmlspecialchars($clave); ?>">
                                    <div class="pay-stage-top">
                                        <label class="pay-chk-label">
                                            <input type="checkbox" name="anticipo_pagado" value="1" <?php echo $ant_ok ? 'checked' : ''; ?>
                                                onchange="document.getElementById('stage-ant-<?php echo htmlspecialchars($clave); ?>').classList.toggle('paid', this.checked)">
                                            Anticipo 60% Pagado
                                        </label>
                                        <span class="pay-amount-tag"><?php echo htmlspecialchars($monto_ant_ems); ?></span>
                                    </div>
                                    <div class="pay-controls">
                                        <input type="date" name="anticipo_fecha" value="<?php echo htmlspecialchars($seg['anticipo_fecha'] ?? ''); ?>" title="Fecha de pago del anticipo">
                                        <select name="anticipo_metodo">
                                            <?php foreach ($metodos_pago as $mp): ?>
                                                <option value="<?php echo $mp; ?>" <?php echo (($seg['anticipo_metodo'] ?? '') === $mp) ? 'selected' : ''; ?>>
                                                    <?php echo $mp ?: '-- Método de pago --'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="comp-actions">
                                            <label class="btn-file-mini">
                                                📎 Comprobante
                                                <input type="file" name="file_anticipo" accept=".pdf,image/*" style="display:none;"
                                                    onchange="this.parentElement.childNodes[0].nodeValue = '📎 Listo '">
                                            </label>
                                            <a id="link-ant-<?php echo htmlspecialchars($clave); ?>"
                                                href="<?php echo htmlspecialchars($seg['anticipo_comprobante'] ?? '#'); ?>"
                                                target="_blank"
                                                class="link-comp"
                                                style="<?php echo !empty($seg['anticipo_comprobante']) ? '' : 'display:none;'; ?>">Ver</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Liquidación 40% -->
                                <div class="pay-stage <?php echo $liq_ok ? 'paid' : ''; ?>" id="stage-liq-<?php echo htmlspecialchars($clave); ?>" style="margin-bottom:0;">
                                    <div class="pay-stage-top">
                                        <label class="pay-chk-label">
                                            <input type="checkbox" name="liquidacion_pagada" value="1" <?php echo $liq_ok ? 'checked' : ''; ?>
                                                onchange="document.getElementById('stage-liq-<?php echo htmlspecialchars($clave); ?>').classList.toggle('paid', this.checked)">
                                            Restante 40% (Al instalar)
                                        </label>
                                        <span class="pay-amount-tag"><?php echo htmlspecialchars($monto_res_ems); ?></span>
                                    </div>
                                    <div class="pay-controls">
                                        <input type="date" name="liquidacion_fecha" value="<?php echo htmlspecialchars($seg['liquidacion_fecha'] ?? ''); ?>" title="Fecha de liquidación">
                                        <select name="liquidacion_metodo">
                                            <?php foreach ($metodos_pago as $mp): ?>
                                                <option value="<?php echo $mp; ?>" <?php echo (($seg['liquidacion_metodo'] ?? '') === $mp) ? 'selected' : ''; ?>>
                                                    <?php echo $mp ?: '-- Método de pago --'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="comp-actions">
                                            <label class="btn-file-mini">
                                                📎 Comprobante
                                                <input type="file" name="file_liquidacion" accept=".pdf,image/*" style="display:none;"
                                                    onchange="this.parentElement.childNodes[0].nodeValue = '📎 Listo '">
                                            </label>
                                            <a id="link-liq-<?php echo htmlspecialchars($clave); ?>"
                                                href="<?php echo htmlspecialchars($seg['liquidacion_comprobante'] ?? '#'); ?>"
                                                target="_blank"
                                                class="link-comp"
                                                style="<?php echo !empty($seg['liquidacion_comprobante']) ? '' : 'display:none;'; ?>">Ver</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 2: Bitácora / Notas de seguimiento -->
                            <div class="crm-box" style="display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div class="crm-box-title">
                                        <span>📝 Bitácora y Notas de Seguimiento</span>
                                    </div>
                                    <textarea name="notas" class="crm-notes-area" placeholder="Escribe acuerdos, llamadas, fechas tentativa de instalación o comentarios del cliente..."><?php echo htmlspecialchars($seg['notas'] ?? ''); ?></textarea>
                                </div>
                                <div class="crm-footer-save">
                                    <span class="crm-saved-msg" id="msg-seg-<?php echo htmlspecialchars($clave); ?>">✓ Guardado</span>
                                    <button type="submit" class="btn-save-crm">💾 Guardar Cobranza y Notas</button>
                                </div>
                            </div>
                        </form>

                        <!-- TABLA DE ARCHIVOS DEL EXPEDIENTE -->
                        <div class="table-container" style="margin-top:0;">
                            <table class="dash-table">
                                <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Monto / Costo Fab.</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($exp['docs'] as $d): ?>
                                        <?php
                                        $f_fmt = date("d/m/Y h:i A", strtotime($d["fecha"]));
                                        $dp = htmlspecialchars(json_encode($d['p']), ENT_QUOTES, 'UTF-8');
                                        if ($d['tipo'] === 'ems') {
                                            $badge = "<span class='badge ems'>Cotización EMS</span>";
                                            $monto = "<strong class='row-total' style='color: var(--brand-green);'>" . htmlspecialchars($d['m']) . "</strong>";
                                            if (!empty($d['fab']) && $d['fab'] !== '$0.00') {
                                                $monto .= "<br><span style='font-size:11.5px; color:#4b5563;'>Fab: " . htmlspecialchars($d['fab']) . "</span>";
                                            }
                                            $del = "dashboard.php?seccion=historial&eliminar=" . $d["id"];
                                        } elseif ($d['tipo'] === 'solar') {
                                            $badge = "<span class='badge solar'>Cotización Solar</span>";
                                            $monto = "<strong class='row-total'>" . htmlspecialchars($d['m']) . "</strong>";
                                            $del = "dashboard.php?seccion=historial&eliminar_solar=" . $d["id"];
                                        } else {
                                            $badge = "<span class='badge recibo'>Recibo CFE</span>";
                                            $monto = "—";
                                            $del = "dashboard.php?seccion=historial&eliminar_recibo=" . $d["id"];
                                        }
                                        ?>
                                        <tr id="row-<?php echo $d['tipo'] . '-' . $d['id']; ?>">
                                            <td><strong><?php echo htmlspecialchars($d['f']); ?></strong></td>
                                            <td style="color: var(--text-muted); font-size: 13px;"><?php echo $f_fmt; ?></td>
                                            <td><?php echo $badge; ?></td>
                                            <td><?php echo $monto; ?></td>
                                            <td>
                                                <div class="action-btns">
                                                    <button type="button" id="btn-view-<?php echo $d['tipo'] . '-' . $d['id']; ?>" class="btn-action btn-view" onclick="abrirModal(this)" data-doc="<?php echo $dp; ?>">Ver</button>
                                                    <a href="<?php echo $del; ?>" class="btn-action btn-delete" onclick="return confirm('¿Seguro que deseas eliminar <?php echo htmlspecialchars($d['f']); ?>?');">Borrar</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color: var(--text-muted);">No hay expedientes registrados aún.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    let filtroActualExp = 'todos';

    function setFiltroExp(filtro, btnEl) {
        filtroActualExp = filtro;
        document.querySelectorAll('#historial-filters .filter-pill').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');
        filtrarExpedientes();
    }

    function filtrarExpedientes() {
        const q = (document.getElementById('buscador-exp')?.value || '').trim().toLowerCase();
        const stFiltro = document.getElementById('filtro-estatus-exp')?.value || '';

        document.querySelectorAll('#lista-expedientes .exp-card').forEach(card => {
            const matchTexto = !q || card.dataset.search.includes(q);
            const matchEstatus = !stFiltro || card.dataset.estatus === stFiltro;

            let matchFiltro = true;
            if (filtroActualExp === 'gran_demanda' || filtroActualExp === 'baja_demanda') {
                matchFiltro = (card.dataset.demanda === filtroActualExp);
            } else if (filtroActualExp === 'completos') {
                matchFiltro = (card.dataset.completo === '1');
            } else if (filtroActualExp === 'por_cobrar') {
                matchFiltro = (card.dataset.porcobrar === '1');
            }

            card.style.display = (matchTexto && matchEstatus && matchFiltro) ? '' : 'none';
        });
    }

    async function cambioRapidoEstatus(clave, selectEl) {
        selectEl.setAttribute('data-st', selectEl.value);
        const hiddenInput = document.getElementById('st-hidden-' + clave);
        if (hiddenInput) hiddenInput.value = selectEl.value;

        const card = document.getElementById('card-' + clave);
        if (card) card.dataset.estatus = selectEl.value;

        await enviarFormularioSeguimiento(clave);
    }

    async function guardarSeguimientoExp(e, clave) {
        e.preventDefault();
        await enviarFormularioSeguimiento(clave);
    }

    async function enviarFormularioSeguimiento(clave) {
        const form = document.getElementById('form-seg-' + clave);
        if (!form) return;

        const fd = new FormData(form);
        try {
            const res = await fetch('guardar_seguimiento.php', {
                method: 'POST',
                body: fd
            });
            const data = await res.json();

            if (data.status === 'success') {
                const stHead = document.getElementById('st-head-' + clave);
                const stHidden = document.getElementById('st-hidden-' + clave);
                const card = document.getElementById('card-' + clave);
                if (data.estatus && stHead) {
                    stHead.value = data.estatus;
                    stHead.setAttribute('data-st', data.estatus);
                    if (stHidden) stHidden.value = data.estatus;
                    if (card) card.dataset.estatus = data.estatus;
                }

                if (data.anticipo_comprobante) {
                    const lAnt = document.getElementById('link-ant-' + clave);
                    if (lAnt) {
                        lAnt.href = data.anticipo_comprobante;
                        lAnt.style.display = 'inline';
                    }
                }
                if (data.liquidacion_comprobante) {
                    const lLiq = document.getElementById('link-liq-' + clave);
                    if (lLiq) {
                        lLiq.href = data.liquidacion_comprobante;
                        lLiq.style.display = 'inline';
                    }
                }

                const msg = document.getElementById('msg-seg-' + clave);
                if (msg) {
                    msg.style.display = 'inline';
                    setTimeout(() => {
                        msg.style.display = 'none';
                    }, 2500);
                }
            } else {
                alert('Error al guardar: ' + (data.message || 'Desconocido'));
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión al guardar el seguimiento.');
        }
    }
</script>