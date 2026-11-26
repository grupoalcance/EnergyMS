<?php
if (!isset($conn)) {
    header("Location: ../dashboard.php?seccion=cotizar_solares");
    exit;
}

// Calcular siguiente folio solar iniciando en PS-0100
$next_sol_num = 100;
$res_sol_folio = @$conn->query("SELECT MAX(id) AS max_id FROM cotizaciones_solares");
if ($res_sol_folio && $row_sf = $res_sol_folio->fetch_assoc()) {
    $max_id = intval($row_sf['max_id']);
    if ($max_id > 0) {
        $next_sol_num = 100 + $max_id;
    }
}
$folio_solar_inicial = 'PS-' . str_pad($next_sol_num, 4, '0', STR_PAD_LEFT);

// Obtener servicios que ya tienen cotización solar
$servicios_con_solar = [];
$rs_exist = @$conn->query("SELECT servicio_cfe, cliente FROM cotizaciones_solares");
if ($rs_exist && $rs_exist->num_rows > 0) {
    while ($se = $rs_exist->fetch_assoc()) {
        $srv = preg_replace('/[^0-9]/', '', (string)$se['servicio_cfe']);
        if (strlen($srv) >= 6) $servicios_con_solar['srv_' . $srv] = true;
        $cli = strtolower(trim(preg_replace('/\s+/', ' ', (string)$se['cliente'])));
        if ($cli !== '') $servicios_con_solar['cli_' . md5($cli)] = true;
    }
}

// Obtener recibos CFE recientes y priorizar los pendientes de cotización solar
$recibos_pendientes = [];
$recibos_otros = [];
$res_rec_opt = @$conn->query("SELECT * FROM recibos_cfe ORDER BY fecha DESC LIMIT 40");
if ($res_rec_opt && $res_rec_opt->num_rows > 0) {
    while ($ro = $res_rec_opt->fetch_assoc()) {
        $srv = preg_replace('/[^0-9]/', '', (string)$ro['servicio_cfe']);
        $cli = strtolower(trim(preg_replace('/\s+/', ' ', (string)$ro['cliente'])));
        $ya_tiene = (strlen($srv) >= 6 && isset($servicios_con_solar['srv_' . $srv]))
            || ($cli !== '' && isset($servicios_con_solar['cli_' . md5($cli)]));

        if ($ya_tiene) {
            $recibos_otros[] = $ro;
        } else {
            $recibos_pendientes[] = $ro;
        }
    }
}
?>

<style>
    .solar-box-wrapper {
        margin: 16px 0 24px 0;
        border: 1.5px solid #111827;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    .solar-banner-header {
        background-color: #111827;
        color: #ffffff;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        border-bottom: 3px solid var(--brand-green);
    }

    table.solar-spec-table {
        width: 100%;
        border-collapse: collapse;
    }

    table.solar-spec-table td {
        border-top: 1px solid #d1d5db;
        padding: 15px 20px;
        font-size: 15px;
    }

    table.solar-spec-table tr:first-child td {
        border-top: none;
    }

    table.solar-spec-table td.solar-label {
        width: 58%;
        font-weight: 600;
        color: #1f2937;
        border-right: 1px solid #d1d5db;
        background-color: #f9fafb;
    }

    table.solar-spec-table td.solar-label.bold-row {
        font-weight: 800;
        color: #111827;
        background-color: #f0fdf4;
    }

    table.solar-spec-table td.solar-val {
        width: 42%;
        text-align: center;
        padding: 10px 14px;
        background-color: #ffffff;
    }

    table.solar-spec-table td.solar-val.highlight-row {
        background-color: #f0fdf4;
    }

    .solar-m2-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        width: 100%;
    }

    .solar-input-m2 {
        width: 72px !important;
        font-size: 18px;
        font-weight: 800;
        color: #111827 !important;
        text-align: right !important;
    }

    .solar-m2-unit {
        font-size: 15px;
        font-weight: 700;
        color: #4b5563;
        flex-shrink: 0;
    }

    .solar-watts-inline {
        width: 52px;
        border: none;
        border-bottom: 1.5px dashed #6b7280;
        text-align: center;
        font-weight: 800;
        font-size: 15px;
        background: transparent;
        outline: none;
        color: #111827;
    }

    .btn-ver-recibo-inline {
        background-color: #1d4ed8;
        color: #ffffff;
        border: none;
        padding: 8px 14px;
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        white-space: nowrap;
        display: none;
    }

    .btn-ver-recibo-inline:hover {
        background-color: #1e40af;
    }

    .badge-pendiente-count {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fcd34d;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 800;
    }

    @media (max-width: 820px) {
        table.solar-spec-table td {
            padding: 12px 12px;
            font-size: 14px;
        }

        .solar-input-m2 {
            width: 58px !important;
            font-size: 15px;
        }

        .solar-watts-inline {
            width: 44px;
            font-size: 14px;
        }
    }
</style>

<button class="print-btn-float" onclick="guardarSolarEImprimir()">🖨️ Imprimir / Guardar PDF</button>

<div class="page-container">
    <?php if (count($recibos_pendientes) > 0 || count($recibos_otros) > 0): ?>
        <div class="quick-fill-bar">
            <label>
                📄 Cargar Recibo CFE:
                <?php if (count($recibos_pendientes) > 0): ?>
                    <span class="badge-pendiente-count">⏳ <?php echo count($recibos_pendientes); ?> pendiente(s)</span>
                <?php endif; ?>
            </label>
            <select onchange="cargarDesdeReciboSolar(this)">
                <option value="">-- Seleccionar recibo guardado --</option>
                <?php if (count($recibos_pendientes) > 0): ?>
                    <optgroup label="⏳ PENDIENTES DE COTIZAR PANELES">
                        <?php foreach ($recibos_pendientes as $rec): ?>
                            <option value="<?php echo htmlspecialchars(json_encode([
                                                'tipo'         => 'recibo',
                                                'id'           => $rec['id'],
                                                'folio'        => $rec['folio'],
                                                'cliente'      => $rec['cliente'],
                                                'servicio'     => $rec['servicio_cfe'],
                                                'archivo_ruta' => $rec['archivo_ruta'],
                                                'archivo_tipo' => $rec['archivo_tipo']
                                            ]), ENT_QUOTES, 'UTF-8'); ?>">
                                ⏳ <?php echo htmlspecialchars($rec['folio'] . ' - ' . $rec['cliente'] . ($rec['servicio_cfe'] ? ' (' . $rec['servicio_cfe'] . ')' : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <?php if (count($recibos_otros) > 0): ?>
                    <optgroup label="✓ YA COTIZADOS ANTERIORMENTE">
                        <?php foreach ($recibos_otros as $rec): ?>
                            <option value="<?php echo htmlspecialchars(json_encode([
                                                'tipo'         => 'recibo',
                                                'id'           => $rec['id'],
                                                'folio'        => $rec['folio'],
                                                'cliente'      => $rec['cliente'],
                                                'servicio'     => $rec['servicio_cfe'],
                                                'archivo_ruta' => $rec['archivo_ruta'],
                                                'archivo_tipo' => $rec['archivo_tipo']
                                            ]), ENT_QUOTES, 'UTF-8'); ?>">
                                ✓ <?php echo htmlspecialchars($rec['folio'] . ' - ' . $rec['cliente']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            </select>
            <button type="button" id="btn-ver-recibo-solar" class="btn-ver-recibo-inline" onclick="abrirModal(this)">
                📄 Ver Recibo CFE
            </button>
        </div>
    <?php endif; ?>

    <div class="page">
        <header>
            <div class="folio-container" style="text-align: left;">
                <div class="folio-title">Cotización Paneles Solares</div>
                <div class="folio-number" id="sol-folio-number"><?php echo $folio_solar_inicial; ?></div>
            </div>
        </header>

        <div class="content">
            <div class="customer-info">
                <label>NOMBRE:</label>
                <textarea id="sol-cliente" placeholder="Ingresa el nombre del cliente o empresa" rows="1"
                    oninput="autoResizeEl(this);"></textarea>
                <label>NO. DE SERVICIO:</label>
                <textarea id="sol-servicio" placeholder="Ingresa el número de servicio CFE (12 dígitos)" rows="1"
                    oninput="autoResizeEl(this)"></textarea>
            </div>

            <!-- TABLA SISTEMA SOLAR -->
            <div class="solar-box-wrapper">
                <div class="solar-banner-header"></div>
                <table class="solar-spec-table">
                    <tr>
                        <td class="solar-label">
                            Paneles de <input type="number" id="sol-watts" class="solar-watts-inline" value="600"
                                min="100" max="1000" oninput="recalcularSolar()">W Necesarios:
                        </td>
                        <td class="solar-val">
                            <input type="number" id="sol-paneles" class="solar-input-paneles" placeholder="0" min="1"
                                oninput="recalcularSolar()" />
                        </td>
                    </tr>
                    <tr>
                        <td class="solar-label">Potencia Instalada Real (kW):</td>
                        <td class="solar-val">
                            <input type="text" id="sol-kw" class="solar-input-kw" placeholder="0.0" />
                        </td>
                    </tr>
                    <tr>
                        <td class="solar-label">Superficie Requerida (M²):</td>
                        <td class="solar-val">
                            <div class="solar-m2-wrap">
                                <input type="number" id="sol-m2" class="solar-input-m2" placeholder="0" min="1" step="any" />
                                <span class="solar-m2-unit">m²</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="solar-label bold-row">INVERSIÓN TOTAL ESTIMADA (Rango):</td>
                        <td class="solar-val highlight-row">
                            <input type="text" id="sol-rango" class="solar-input-rango" placeholder="$0 - $0" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <footer>
            <div class="footer-left">
                <p>BLVD. INDEPENDENCIA ESQ. CALZ. COLÓN</p>
                <p>TORREÓN, COAHUILA</p>
            </div>
            <div class="footer-right">
                <p><a href="https://energymultipliersystem.com/" target="_blank">energymultipliersystem.com</a></p>
            </div>
        </footer>
    </div>
</div>

<script>
    let solarGuardadaId = null;

    function autoResizeEl(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    function cargarDesdeReciboSolar(selectEl) {
        const btnRec = document.getElementById('btn-ver-recibo-solar');
        if (!selectEl.value) {
            if (btnRec) btnRec.style.display = 'none';
            return;
        }
        const data = JSON.parse(selectEl.value);
        const cEl = document.getElementById('sol-cliente');
        const sEl = document.getElementById('sol-servicio');

        cEl.value = data.cliente || '';
        sEl.value = data.servicio || '';

        autoResizeEl(cEl);
        autoResizeEl(sEl);

        if (btnRec && data.archivo_ruta) {
            btnRec.setAttribute('data-doc', selectEl.value);
            btnRec.style.display = 'inline-block';
        }

        document.getElementById('sol-paneles').focus();
    }

    const intFmt = new Intl.NumberFormat("en-US", {
        maximumFractionDigits: 0
    });

    function recalcularSolar() {
        const paneles = parseFloat(document.getElementById('sol-paneles').value) || 0;
        const watts = parseFloat(document.getElementById('sol-watts').value) || 600;

        if (paneles > 0) {
            const kw = (paneles * watts) / 1000;
            document.getElementById('sol-kw').value = parseFloat(kw.toFixed(2));
            document.getElementById('sol-m2').value = Math.ceil(paneles * 2.8);

            const minInv = Math.round(kw * 17550);
            const maxInv = Math.round(kw * 20475);
            document.getElementById('sol-rango').value = '$' + intFmt.format(minInv) + ' - $' + intFmt.format(maxInv);
        } else {
            document.getElementById('sol-kw').value = '';
            document.getElementById('sol-m2').value = '';
            document.getElementById('sol-rango').value = '';
        }
    }

    async function guardarSolarEImprimir() {
        const cliente = document.getElementById('sol-cliente').value.replace(/\s+/g, ' ').trim();
        if (!cliente) {
            alert("Por favor ingresa el Nombre del cliente antes de guardar.");
            document.getElementById('sol-cliente').focus();
            return;
        }

        const payload = {
            ...(solarGuardadaId ? {
                id: solarGuardadaId
            } : {}),
            cliente: cliente,
            servicio_cfe: document.getElementById('sol-servicio').value.trim(),
            paneles: parseInt(document.getElementById('sol-paneles').value) || 0,
            watts_panel: parseInt(document.getElementById('sol-watts').value) || 600,
            potencia_kw: document.getElementById('sol-kw').value.trim(),
            metros_cuadrados: document.getElementById('sol-m2').value.trim(),
            inversion_rango: document.getElementById('sol-rango').value.trim(),
            agente_nombre: '',
            agente_telefono: ''
        };

        try {
            const res = await fetch("guardar_solar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                if (data.folio) document.getElementById('sol-folio-number').innerText = data.folio;
                if (data.id) solarGuardadaId = data.id;
            }
        } catch (e) {
            console.error("Error al guardar cotización solar:", e);
        }

        const tituloOriginal = document.title;
        const folioTxt = document.getElementById('sol-folio-number').innerText.trim();
        document.title = `Cotizacion ${folioTxt} ${cliente}`.trim();

        window.print();

        setTimeout(() => {
            document.title = tituloOriginal;
        }, 500);
    }
</script>