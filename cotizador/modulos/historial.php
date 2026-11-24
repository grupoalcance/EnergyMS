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

// Ordenar expedientes por fecha más reciente
uasort($expedientes, function ($a, $b) {
    return strtotime($b['ultima_fecha']) - strtotime($a['ultima_fecha']);
});
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

    .search-exp {
        padding: 9px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13.5px;
        font-family: inherit;
        min-width: 240px;
        outline: none;
    }

    .search-exp:focus {
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
        gap: 4px;
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
    }

    .exp-right {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
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

    @media (max-width: 820px) {
        .search-exp {
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
        <input type="text" id="buscador-exp" class="search-exp" placeholder="🔍 Buscar cliente o No. de Servicio..." oninput="filtrarExpedientes()">

        <div class="filter-pills" id="historial-filters">
            <button type="button" class="filter-pill active" onclick="setFiltroExp('todos', this)">Todos</button>
            <button type="button" class="filter-pill" onclick="setFiltroExp('gran_demanda', this)">🏭 Gran Demanda</button>
            <button type="button" class="filter-pill" onclick="setFiltroExp('baja_demanda', this)">🏠 Baja Demanda</button>
            <button type="button" class="filter-pill" onclick="setFiltroExp('completos', this)">✅ Completos (3/3)</button>
        </div>
    </div>

    <div class="exp-list" id="lista-expedientes">
        <?php if (count($expedientes) > 0): ?>
            <?php foreach ($expedientes as $exp): ?>
                <?php
                $dem = $exp['tipo_demanda'];
                $cat_dem = ($dem === 'Gran Demanda') ? 'gran_demanda' : (($dem === 'Baja Demanda') ? 'baja_demanda' : 'otro');
                $es_completo = ($exp['recibo_id'] > 0 && $exp['solar_id'] > 0 && $exp['ems_id'] > 0) ? '1' : '0';
                $url_pdf = "expediente_pdf.php?r={$exp['recibo_id']}&s={$exp['solar_id']}&e={$exp['ems_id']}";
                $texto_busqueda = strtolower($exp['cliente'] . ' ' . $exp['servicio']);
                ?>
                <div class="exp-card"
                    data-demanda="<?php echo $cat_dem; ?>"
                    data-completo="<?php echo $es_completo; ?>"
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
                            </div>
                            <div class="exp-meta">
                                <span><strong>Servicio CFE:</strong> <?php echo $exp['servicio'] ? htmlspecialchars($exp['servicio']) : 'Sin registrar'; ?></span>
                                <span><strong>Última actividad:</strong> <?php echo date("d/m/Y h:i A", strtotime($exp['ultima_fecha'])); ?></span>
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
        document.querySelectorAll('#lista-expedientes .exp-card').forEach(card => {
            const matchTexto = !q || card.dataset.search.includes(q);
            let matchFiltro = true;
            if (filtroActualExp === 'gran_demanda' || filtroActualExp === 'baja_demanda') {
                matchFiltro = (card.dataset.demanda === filtroActualExp);
            } else if (filtroActualExp === 'completos') {
                matchFiltro = (card.dataset.completo === '1');
            }
            card.style.display = (matchTexto && matchFiltro) ? '' : 'none';
        });
    }
</script>