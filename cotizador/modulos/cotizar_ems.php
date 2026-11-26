<?php
if (!isset($conn)) {
    header("Location: ../dashboard.php?seccion=cotizar_ems");
    exit;
}

// Detectar clientes con solar listo y clientes que ya tienen EMS
$con_solar = [];
$rs_s = @$conn->query("SELECT servicio_cfe, cliente FROM cotizaciones_solares");
if ($rs_s && $rs_s->num_rows > 0) {
    while ($r = $rs_s->fetch_assoc()) {
        $srv = preg_replace('/[^0-9]/', '', (string)$r['servicio_cfe']);
        if (strlen($srv) >= 6) $con_solar['srv_' . $srv] = true;
        $cli = strtolower(trim(preg_replace('/\s+/', ' ', (string)$r['cliente'])));
        if ($cli !== '') $con_solar['cli_' . md5($cli)] = true;
    }
}

$con_ems = [];
$re_e = @$conn->query("SELECT servicio_cfe, cliente FROM cotizaciones");
if ($re_e && $re_e->num_rows > 0) {
    while ($r = $re_e->fetch_assoc()) {
        $srv = preg_replace('/[^0-9]/', '', (string)$r['servicio_cfe']);
        if (strlen($srv) >= 6) $con_ems['srv_' . $srv] = true;
        $cli = strtolower(trim(preg_replace('/\s+/', ' ', (string)$r['cliente'])));
        if ($cli !== '') $con_ems['cli_' . md5($cli)] = true;
    }
}

$recibos_listos_ems = [];
$recibos_otros = [];
$res_rec_opt = @$conn->query("SELECT * FROM recibos_cfe ORDER BY fecha DESC LIMIT 40");
if ($res_rec_opt && $res_rec_opt->num_rows > 0) {
    while ($ro = $res_rec_opt->fetch_assoc()) {
        $srv = preg_replace('/[^0-9]/', '', (string)$ro['servicio_cfe']);
        $cli = strtolower(trim(preg_replace('/\s+/', ' ', (string)$ro['cliente'])));
        $k_srv = strlen($srv) >= 6 ? 'srv_' . $srv : '';
        $k_cli = $cli !== '' ? 'cli_' . md5($cli) : '';

        $tiene_solar = ($k_srv && isset($con_solar[$k_srv])) || ($k_cli && isset($con_solar[$k_cli]));
        $tiene_ems   = ($k_srv && isset($con_ems[$k_srv]))   || ($k_cli && isset($con_ems[$k_cli]));

        if ($tiene_solar && !$tiene_ems) {
            $recibos_listos_ems[] = $ro;
        } else {
            $recibos_otros[] = $ro;
        }
    }
}
?>

<style>
    .fab-box {
        background-color: #f8fafc;
        border: 1.5px solid #111827;
        border-radius: 8px;
        width: 82%;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .fab-header {
        background-color: #111827;
        color: #ffffff;
        padding: 8px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid var(--brand-green);
    }

    .fab-title {
        font-size: 12.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--brand-green);
    }

    .fab-demanda-select {
        background-color: #1f2937;
        color: #ffffff;
        border: 1px solid #374151;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 700;
        font-family: inherit;
        outline: none;
        cursor: pointer;
    }

    .fab-body {
        padding: 12px 18px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        align-items: center;
    }

    .fab-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .fab-field label {
        font-size: 11.5px;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
    }

    .fab-input-wrap {
        display: flex;
        align-items: center;
        border-bottom: 1.5px solid #cbd5e1;
        padding-bottom: 3px;
        gap: 6px;
    }

    .fab-input-wrap input {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        font-size: 15px;
        font-weight: 800;
        color: #111827;
    }

    .fab-result-box {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        padding: 8px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .fab-result-label {
        font-size: 12px;
        font-weight: 800;
        color: #166534;
        text-transform: uppercase;
    }

    .fab-result-val {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
    }

    .print-actions-float {
        position: fixed;
        bottom: 26px;
        right: 26px;
        display: flex;
        gap: 10px;
        z-index: 100;
    }

    .print-actions-float button {
        border: none;
        padding: 13px 20px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.25);
        transition: transform 0.2s;
        font-family: inherit;
    }

    .print-actions-float button:hover {
        transform: scale(1.03);
    }

    .btn-print-cliente {
        background-color: #1a202c;
        color: #ffffff;
    }

    .btn-print-interno {
        background-color: var(--brand-green);
        color: #000000;
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

    .badge-listo-ems {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 800;
    }

    @media (max-width: 820px) {
        .fab-box {
            width: 100%;
        }

        .fab-body {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .print-actions-float {
            bottom: 12px;
            left: 16px;
            right: 16px;
            flex-direction: column;
            gap: 8px;
        }

        .print-actions-float button {
            width: 100%;
            border-radius: 10px;
            padding: 12px 16px;
        }
    }

    @media print {
        .print-actions-float {
            display: none !important;
        }

        body.print-sin-fab .fab-box {
            display: none !important;
        }

        .fab-box {
            width: 82% !important;
            margin-bottom: 14px !important;
        }

        .fab-demanda-select {
            border: none !important;
            background: transparent !important;
            appearance: none;
            -webkit-appearance: none;
            padding: 0 !important;
        }

        .payment-conditions {
            margin-bottom: 14px !important;
        }

        .agent-bottom-info {
            margin-top: 14px !important;
        }
    }
</style>

<div class="print-actions-float">
    <button type="button" class="btn-print-cliente" onclick="guardarEImprimir(false)">🖨️ Imprimir (Cliente)</button>
    <button type="button" class="btn-print-interno" onclick="guardarEImprimir(true)">🛠️ Imprimir (Con Costo Fab.)</button>
</div>

<div class="page-container">
    <?php if (count($recibos_listos_ems) > 0 || count($recibos_otros) > 0): ?>
        <div class="quick-fill-bar">
            <label>
                📄 Cargar cliente desde Recibo:
                <?php if (count($recibos_listos_ems) > 0): ?>
                    <span class="badge-listo-ems">⚡ <?php echo count($recibos_listos_ems); ?> listo(s) tras Solar</span>
                <?php endif; ?>
            </label>
            <select onchange="cargarDesdeReciboEMS(this)">
                <option value="">-- Seleccionar recibo guardado --</option>
                <?php if (count($recibos_listos_ems) > 0): ?>
                    <optgroup label="⚡ LISTOS PARA COTIZAR EMS (Solar ya listo)">
                        <?php foreach ($recibos_listos_ems as $rec): ?>
                            <option value="<?php echo htmlspecialchars(json_encode([
                                                'tipo'         => 'recibo',
                                                'id'           => $rec['id'],
                                                'folio'        => $rec['folio'],
                                                'cliente'      => $rec['cliente'],
                                                'direccion'    => $rec['direccion'],
                                                'servicio'     => $rec['servicio_cfe'],
                                                'tipo_demanda' => $rec['tipo_demanda'],
                                                'agente'       => $rec['agente_nombre'],
                                                'archivo_ruta' => $rec['archivo_ruta'],
                                                'archivo_tipo' => $rec['archivo_tipo']
                                            ]), ENT_QUOTES, 'UTF-8'); ?>">
                                ⚡ <?php echo htmlspecialchars($rec['folio'] . ' - ' . $rec['cliente'] . ($rec['tipo_demanda'] ? ' [' . $rec['tipo_demanda'] . ']' : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <?php if (count($recibos_otros) > 0): ?>
                    <optgroup label="OTROS RECIBOS GUARDADOS">
                        <?php foreach ($recibos_otros as $rec): ?>
                            <option value="<?php echo htmlspecialchars(json_encode([
                                                'tipo'         => 'recibo',
                                                'id'           => $rec['id'],
                                                'folio'        => $rec['folio'],
                                                'cliente'      => $rec['cliente'],
                                                'direccion'    => $rec['direccion'],
                                                'servicio'     => $rec['servicio_cfe'],
                                                'tipo_demanda' => $rec['tipo_demanda'],
                                                'agente'       => $rec['agente_nombre'],
                                                'archivo_ruta' => $rec['archivo_ruta'],
                                                'archivo_tipo' => $rec['archivo_tipo']
                                            ]), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($rec['folio'] . ' - ' . $rec['cliente'] . ($rec['tipo_demanda'] ? ' [' . $rec['tipo_demanda'] . ']' : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            </select>
            <button type="button" id="btn-ver-recibo-ems" class="btn-ver-recibo-inline" onclick="abrirModal(this)">
                📄 Ver Recibo CFE
            </button>
        </div>
    <?php endif; ?>

    <div class="page">
        <header>
            <div class="logo-container"><img src="../img/energy.png" alt="EMS" class="logo-ems-doc" /></div>
            <div class="folio-container">
                <div class="folio-title">Cotización</div>
                <div class="folio-number" id="folio-number">Cargando...</div>
            </div>
        </header>

        <div class="content">
            <div class="customer-info">
                <label>NOMBRE:</label>
                <textarea id="input-cliente" placeholder="Ingresa el nombre del cliente" rows="1"
                    oninput="autoResizeEl(this)"></textarea>
                <label>DIRECCIÓN:</label>
                <textarea id="input-direccion" placeholder="Ingresa la dirección" rows="1"
                    oninput="autoResizeEl(this)"></textarea>
                <label>NO. DE SERVICIO:</label>
                <textarea id="input-servicio" placeholder="Ingresa el número de servicio CFE" rows="1"
                    oninput="autoResizeEl(this)"></textarea>
            </div>

            <table class="products">
                <thead>
                    <tr>
                        <th style="width: 8%">cant.</th>
                        <th style="width: 25%">Descripción</th>
                        <th style="width: 20%">Garantía de fábrica</th>
                        <th style="width: 25%">Para colocarlo en</th>
                        <th style="width: 22%">Duración</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><textarea rows="1">1</textarea></td>
                        <td><textarea rows="1">Aparato EMS</textarea></td>
                        <td><textarea rows="1">10 años</textarea></td>
                        <td><textarea id="input-tabla-direccion" placeholder="La dirección se copiará aquí..." rows="1"
                                oninput="autoResizeEl(this)"></textarea></td>
                        <td><textarea rows="1">10 a 15 años</textarea></td>
                    </tr>
                </tbody>
            </table>

            <div class="split-section">
                <div class="left-box">
                    <div class="red-title">Ahorro Estimado</div>
                    <div class="cfe-title">Una vez colocado se espera un pago a CFE de</div>
                    <table class="table-mini">
                        <tr>
                            <td class="label">Energía:</td>
                            <td class="value">
                                <div class="money-cell"><span class="currency">$</span><input type="text"
                                        id="input-energia" placeholder="0.00"
                                        title="Ingresa el monto de energía del recibo; se calculará automáticamente el 10%" /></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Cargo fijo:</td>
                            <td class="value">
                                <div class="money-cell"><span class="currency">$</span><input type="text"
                                        id="input-cargo" placeholder="0.00" /></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">IVA:</td>
                            <td class="value">
                                <div class="money-cell"><span class="currency">$</span><input type="text"
                                        id="iva-cfe-display" placeholder="0.00" readonly /></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Total a pagar:</td>
                            <td class="value">
                                <div class="money-cell"><span class="currency">$</span><input type="text"
                                        id="input-total-cfe" placeholder="0.00" readonly /></div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="right-box">
                    <div class="green-title">Precio</div>
                    <table class="table-mini" style="margin-top: 19px;">
                        <tr>
                            <td class="label">Subtotal:</td>
                            <td class="value">
                                <div class="money-cell finance-color"><span class="currency">$</span><input
                                        type="text" id="input-subtotal" placeholder="0.00" /></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">IVA:</td>
                            <td class="value">
                                <div class="money-cell finance-color"><span class="currency">$</span><input
                                        type="text" id="iva-display" placeholder="0.00" readonly /></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Total:</td>
                            <td class="value">
                                <div class="money-cell finance-color"><span class="currency">$</span><input
                                        type="text" id="total-display" placeholder="0.00" readonly /></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="bottom-section">
                <div class="payment-conditions">
                    <div class="payment-title">Condiciones de Pago</div>
                    <div class="payment-row">
                        <span>Anticipo 60%:</span>
                        <span class="payment-amount"><span>$</span><strong id="anticipo-display">0.00</strong></span>
                    </div>
                    <div class="payment-row">
                        <span>Una vez colocado, el restante 40%:</span>
                        <span class="payment-amount"><span>$</span><strong id="restante-display">0.00</strong></span>
                    </div>
                </div>

                <!-- COSTO DE FABRICACIÓN -->
                <div class="fab-box">
                    <div class="fab-header">
                        <span class="fab-title">🛠️ Costo de Fabricación</span>
                        <select id="input-tipo-demanda" class="fab-demanda-select" onchange="actualizarEtiquetaFabricacion()">
                            <option value="Gran Demanda">🏭 Gran Demanda</option>
                            <option value="Baja Demanda">🏠 Baja Demanda</option>
                        </select>
                    </div>
                    <div class="fab-body">
                        <div class="fab-field">
                            <label id="label-dato-fab">Demanda contratada (kW):</label>
                            <div class="fab-input-wrap">
                                <input type="number" id="input-dato-fab" placeholder="0" step="any" min="0"
                                    oninput="calcularCostoFabricacion()" />
                            </div>
                        </div>
                        <div class="fab-result-box">
                            <span class="fab-result-label">Costo Total:</span>
                            <span class="fab-result-val" id="costo-fab-display">$0.00</span>
                        </div>
                    </div>
                </div>

                <div class="agent-bottom-info">
                    <div class="agent-field">
                        <input type="text" id="input-agente-nombre" placeholder="Nombre completo" />
                        <label>AGENTE DE VENTAS</label>
                    </div>
                    <div class="agent-field">
                        <input type="text" id="input-agente-telefono" placeholder="Teléfono" />
                        <label>TELÉFONO</label>
                    </div>
                </div>
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
    let cotizacionGuardadaId = null;

    function autoResizeEl(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    async function cargarFolio() {
        try {
            const res = await fetch("obtener_folio.php");
            const data = await res.json();
            document.getElementById("folio-number").innerText = "EMS-" + String(data.siguiente_folio).padStart(4, "0");
        } catch (e) {
            document.getElementById("folio-number").innerText = "EMS-0100";
        }
    }
    cargarFolio();

    const inEnergia = document.getElementById("input-energia"),
        inCargo = document.getElementById("input-cargo"),
        inTotalCfe = document.getElementById("input-total-cfe"),
        inSubtotal = document.getElementById("input-subtotal"),
        dIva = document.getElementById("iva-display"),
        dTotal = document.getElementById("total-display"),
        dAnticipo = document.getElementById("anticipo-display"),
        dRestante = document.getElementById("restante-display");

    document.getElementById("input-direccion").addEventListener("input", function(e) {
        const td = document.getElementById("input-tabla-direccion");
        td.value = e.target.value;
        autoResizeEl(td);
    });

    const numFmt = new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function cleanFmt(e) {
        e.target.value = e.target.value.replace(/[^0-9.-]+/g, "");
    }

    function appFmt(e) {
        let v = parseFloat(e.target.value.replace(/[^0-9.-]+/g, ""));
        e.target.value = !isNaN(v) ? numFmt.format(v) : "";
    }

    function cargarDesdeReciboEMS(selectEl) {
        const btnRec = document.getElementById("btn-ver-recibo-ems");
        if (!selectEl.value) {
            if (btnRec) btnRec.style.display = "none";
            return;
        }
        const data = JSON.parse(selectEl.value);
        const cEl = document.getElementById("input-cliente");
        const dEl = document.getElementById("input-direccion");
        const tdEl = document.getElementById("input-tabla-direccion");
        const sEl = document.getElementById("input-servicio");
        const aEl = document.getElementById("input-agente-nombre");
        const demEl = document.getElementById("input-tipo-demanda");

        cEl.value = data.cliente || "";
        dEl.value = data.direccion || "";
        tdEl.value = data.direccion || "";
        sEl.value = data.servicio || "";
        if (aEl && data.agente) aEl.value = data.agente;

        if (demEl && (data.tipo_demanda === "Gran Demanda" || data.tipo_demanda === "Baja Demanda")) {
            demEl.value = data.tipo_demanda;
            actualizarEtiquetaFabricacion();
        }

        if (btnRec && data.archivo_ruta) {
            btnRec.setAttribute("data-doc", selectEl.value);
            btnRec.style.display = "inline-block";
        }

        [cEl, dEl, tdEl, sEl].forEach(autoResizeEl);
    }

    function actualizarEtiquetaFabricacion() {
        const tipo = document.getElementById("input-tipo-demanda").value;
        const lbl = document.getElementById("label-dato-fab");
        lbl.innerText = (tipo === "Baja Demanda") ? "Lectura total:" : "Demanda contratada (kW):";
        calcularCostoFabricacion();
    }

    function calcularCostoFabricacion() {
        const tipo = document.getElementById("input-tipo-demanda").value;
        const val = parseFloat(document.getElementById("input-dato-fab").value) || 0;
        let costo = 0;

        if (val > 0) {
            if (tipo === "Baja Demanda") {
                costo = (val / 60) * 1.5 * 1000;
            } else {
                costo = val * 1.5 * 1000;
            }
        }
        document.getElementById("costo-fab-display").innerText = "$" + numFmt.format(costo);
    }

    // Energía: convierte automáticamente al 10% al salir de la casilla
    inEnergia.addEventListener("focus", cleanFmt);
    inEnergia.addEventListener("blur", function(e) {
        let v = parseFloat(e.target.value.replace(/[^0-9.-]+/g, ""));
        if (!isNaN(v)) {
            if (inEnergia.dataset.pendiente10 === "1") {
                v = v * 0.10;
                inEnergia.dataset.pendiente10 = "0";
            }
            e.target.value = numFmt.format(v);
        } else {
            e.target.value = "";
            inEnergia.dataset.pendiente10 = "0";
        }
        calCfe();
    });

    [inCargo, inSubtotal].forEach(i => {
        i.addEventListener("focus", cleanFmt);
        i.addEventListener("blur", appFmt);
    });

    function calCfe() {
        let rawE = parseFloat(inEnergia.value.replace(/[^0-9.-]+/g, "")) || 0;
        let e = (inEnergia.dataset.pendiente10 === "1") ? (rawE * 0.10) : rawE;
        let c = parseFloat(inCargo.value.replace(/[^0-9.-]+/g, "")) || 0;

        if (e > 0 || c > 0) {
            let s = e + c,
                i = s * 0.16;
            document.getElementById("iva-cfe-display").value = numFmt.format(i);
            inTotalCfe.value = numFmt.format(s + i);
        } else {
            document.getElementById("iva-cfe-display").value = "";
            inTotalCfe.value = "";
        }
    }

    inEnergia.addEventListener("input", function() {
        inEnergia.dataset.pendiente10 = "1";
        calCfe();
    });
    inCargo.addEventListener("input", calCfe);

    inSubtotal.addEventListener("input", function(e) {
        let s = parseFloat(e.target.value.replace(/[^0-9.-]+/g, ""));
        if (isNaN(s)) s = 0;
        let i = s * 0.16,
            t = s + i;
        dIva.value = numFmt.format(i);
        dTotal.value = numFmt.format(t);
        dAnticipo.innerText = numFmt.format(t * 0.6);
        dRestante.innerText = numFmt.format(t * 0.4);
    });

    function getDatos() {
        if (inEnergia.dataset.pendiente10 === "1") {
            let v = parseFloat(inEnergia.value.replace(/[^0-9.-]+/g, ""));
            inEnergia.value = !isNaN(v) ? numFmt.format(v * 0.10) : "";
            inEnergia.dataset.pendiente10 = "0";
            calCfe();
        }

        const cv = v => v.trim() !== "" ? "$" + v.trim() : "";
        return {
            id: cotizacionGuardadaId,
            folio: document.getElementById("folio-number").innerText,
            cliente: document.getElementById("input-cliente").value,
            direccion: document.getElementById("input-direccion").value,
            servicio: document.getElementById("input-servicio").value,
            tipo_demanda: document.getElementById("input-tipo-demanda").value,
            energia: cv(inEnergia.value),
            cargo: cv(inCargo.value),
            total_cfe: cv(inTotalCfe.value),
            subtotal: cv(inSubtotal.value),
            iva: cv(dIva.value),
            total: cv(dTotal.value),
            anticipo: cv(dAnticipo.innerText),
            restante: cv(dRestante.innerText),
            dato_fabricacion: document.getElementById("input-dato-fab").value.trim(),
            costo_fabricacion: document.getElementById("costo-fab-display").innerText,
            agente_nombre: document.getElementById("input-agente-nombre").value,
            agente_telefono: document.getElementById("input-agente-telefono").value
        };
    }

    async function guardarEImprimir(incluirFabricacion) {
        const cliente = document.getElementById("input-cliente").value.replace(/\s+/g, " ").trim();
        if (!cliente) {
            alert("Por favor ingresa el Nombre del cliente antes de guardar.");
            document.getElementById("input-cliente").focus();
            return;
        }

        try {
            const res = await fetch("guardar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(getDatos())
            });
            const data = await res.json();
            if (data.status === "success" && data.id) {
                cotizacionGuardadaId = data.id;
            }
        } catch (e) {
            console.error("Error al guardar cotización EMS:", e);
        }

        if (incluirFabricacion) {
            document.body.classList.remove("print-sin-fab");
        } else {
            document.body.classList.add("print-sin-fab");
        }

        const tituloOriginal = document.title;
        const folioTxt = document.getElementById("folio-number").innerText.trim();
        document.title = `Cotizacion ${folioTxt} ${cliente}`.trim();

        window.print();

        setTimeout(() => {
            document.title = tituloOriginal;
            document.body.classList.remove("print-sin-fab");
        }, 500);
    }
</script>