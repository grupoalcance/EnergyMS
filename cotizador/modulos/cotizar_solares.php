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

// Obtener recibos CFE recientes para autocompletado opcional
$recibos_lista = [];
$res_rec_opt = @$conn->query("SELECT id, folio, cliente, servicio_cfe, agente_nombre FROM recibos_cfe ORDER BY fecha DESC LIMIT 30");
if ($res_rec_opt && $res_rec_opt->num_rows > 0) {
    while ($ro = $res_rec_opt->fetch_assoc()) {
        $recibos_lista[] = $ro;
    }
}
?>

<button class="print-btn-float" onclick="guardarSolarEImprimir()">🖨️ Imprimir / Guardar PDF</button>

<div class="page-container">
    <?php if (count($recibos_lista) > 0): ?>
        <div class="quick-fill-bar">
            <label>📄 Cargar datos desde Recibo:</label>
            <select onchange="cargarDesdeReciboSolar(this)">
                <option value="">-- Seleccionar recibo guardado (opcional) --</option>
                <?php foreach ($recibos_lista as $rec): ?>
                    <option value="<?php echo htmlspecialchars(json_encode([
                        'cliente' => $rec['cliente'],
                        'servicio' => $rec['servicio_cfe'],
                        'agente' => $rec['agente_nombre']
                    ]), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($rec['folio'] . ' - ' . $rec['cliente'] . ($rec['servicio_cfe'] ? ' (' . $rec['servicio_cfe'] . ')' : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="page">
        <header>
            <div class="folio-container" style="margin-left: auto;">
                <div class="folio-title">Cotización Paneles Solares</div>
                <div class="folio-number" id="sol-folio-number"><?php echo $folio_solar_inicial; ?></div>
            </div>
        </header>

        <div class="content">
            <div class="customer-info">
                <label>NOMBRE:</label>
                <textarea id="sol-cliente" placeholder="Ingresa el nombre del cliente o empresa" rows="1"
                    oninput="autoResizeEl(this); actualizarBannerSolar();"></textarea>
                <label>NO. DE SERVICIO:</label>
                <textarea id="sol-servicio" placeholder="Ingresa el número de servicio CFE (12 dígitos)" rows="1"
                    oninput="autoResizeEl(this)"></textarea>
            </div>

            <!-- TABLA SISTEMA SOLAR -->
            <div class="solar-box-wrapper">
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
                        <td class="solar-label bold-row">INVERSIÓN TOTAL ESTIMADA (Rango):</td>
                        <td class="solar-val" style="background-color: #f0fdf4;">
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
    function autoResizeEl(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    function actualizarBannerSolar() {
        const bannerEl = document.getElementById('sol-banner-cliente');
        if (!bannerEl) return;
        const val = document.getElementById('sol-cliente').value.trim();
        bannerEl.innerText = val !== '' ? val.toUpperCase() : 'CLIENTE';
    }

    function cargarDesdeReciboSolar(selectEl) {
        if (!selectEl.value) return;
        const data = JSON.parse(selectEl.value);
        const cEl = document.getElementById('sol-cliente');
        const sEl = document.getElementById('sol-servicio');
        const aEl = document.getElementById('sol-agente-nombre');

        cEl.value = data.cliente || '';
        sEl.value = data.servicio || '';
        if (aEl && data.agente) aEl.value = data.agente;

        autoResizeEl(cEl);
        autoResizeEl(sEl);
        actualizarBannerSolar();
        document.getElementById('sol-paneles').focus();
    }

    const intFmt = new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 });

    function recalcularSolar() {
        const paneles = parseFloat(document.getElementById('sol-paneles').value) || 0;
        const watts = parseFloat(document.getElementById('sol-watts').value) || 600;

        if (paneles > 0) {
            const kw = (paneles * watts) / 1000;
            document.getElementById('sol-kw').value = parseFloat(kw.toFixed(2));

            const minInv = Math.round(kw * 17550);
            const maxInv = Math.round(kw * 20475);
            document.getElementById('sol-rango').value = '$' + intFmt.format(minInv) + ' - $' + intFmt.format(maxInv);
        } else {
            document.getElementById('sol-kw').value = '';
            document.getElementById('sol-rango').value = '';
        }
    }

    async function guardarSolarEImprimir() {
        const cliente = document.getElementById('sol-cliente').value.trim();
        if (!cliente) {
            alert("Por favor ingresa el Nombre del cliente antes de guardar.");
            document.getElementById('sol-cliente').focus();
            return;
        }

        const agenteEl = document.getElementById('sol-agente-nombre');
        const telEl = document.getElementById('sol-agente-telefono');

        const payload = {
            cliente: cliente,
            servicio_cfe: document.getElementById('sol-servicio').value.trim(),
            paneles: parseInt(document.getElementById('sol-paneles').value) || 0,
            watts_panel: parseInt(document.getElementById('sol-watts').value) || 600,
            potencia_kw: document.getElementById('sol-kw').value.trim(),
            inversion_rango: document.getElementById('sol-rango').value.trim(),
            agente_nombre: agenteEl ? agenteEl.value.trim() : '',
            agente_telefono: telEl ? telEl.value.trim() : ''
        };

        try {
            const res = await fetch("guardar_solar.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success' && data.folio) {
                document.getElementById('sol-folio-number').innerText = data.folio;
            }
        } catch (e) {
            console.error("Error al guardar cotización solar:", e);
        }

        window.print();
    }
</script>