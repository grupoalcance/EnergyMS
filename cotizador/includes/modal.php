<!-- === VENTANA EMERGENTE UNIVERSAL (COTIZACIONES EMS, PANELES SOLARES Y RECIBOS CFE) === -->
<style>
    /* Estilos Solar en Modal */
    #modal-solar-page .solar-box-wrapper {
        margin: 16px 0 24px 0;
        border: 1.5px solid #111827;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    #modal-solar-page .solar-banner-header {
        background-color: #111827;
        color: #ffffff;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        border-bottom: 3px solid var(--brand-green);
    }

    #modal-solar-page .solar-banner-left {
        font-size: 14px;
        font-weight: 800;
        color: var(--brand-green);
        letter-spacing: 0.6px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #modal-solar-page .solar-banner-right {
        font-size: 14px;
        font-weight: 700;
        color: #f3f4f6;
        text-transform: uppercase;
        text-align: right;
        word-break: break-word;
    }

    #modal-solar-page table.solar-spec-table {
        width: 100%;
        border-collapse: collapse;
    }

    #modal-solar-page table.solar-spec-table td {
        border-top: 1px solid #d1d5db;
        padding: 15px 20px;
        font-size: 15px;
    }

    #modal-solar-page table.solar-spec-table tr:first-child td {
        border-top: none;
    }

    #modal-solar-page table.solar-spec-table td.solar-label {
        width: 58%;
        font-weight: 600;
        color: #1f2937;
        border-right: 1px solid #d1d5db;
        background-color: #f9fafb;
    }

    #modal-solar-page table.solar-spec-table td.solar-label.bold-row {
        font-weight: 800;
        color: #111827;
        background-color: #f0fdf4;
    }

    #modal-solar-page table.solar-spec-table td.solar-val {
        width: 42%;
        text-align: center;
        padding: 10px 14px;
        background-color: #ffffff;
    }

    #modal-solar-page table.solar-spec-table td.solar-val.highlight-row {
        background-color: #f0fdf4;
    }

    #modal-solar-page .solar-m2-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        width: 100%;
    }

    #modal-solar-page .solar-input-m2 {
        width: 72px !important;
        font-size: 18px;
        font-weight: 800;
        color: #111827 !important;
        text-align: right !important;
    }

    #modal-solar-page .solar-m2-unit {
        font-size: 15px;
        font-weight: 700;
        color: #4b5563;
        flex-shrink: 0;
    }

    /* Casilla incluir costo de fabricación en modal */
    .modal-chk-fab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #ffffff;
        cursor: pointer;
        user-select: none;
    }

    .modal-chk-fab input {
        width: 15px;
        height: 15px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    /* Estilos Costo de Fabricación en Modal EMS */
    #modal-page .fab-box {
        background-color: #f8fafc;
        border: 1.5px solid #111827;
        border-radius: 8px;
        width: 82%;
        margin-bottom: 24px;
        overflow: hidden;
    }

    #modal-page .fab-header {
        background-color: #111827;
        color: #ffffff;
        padding: 8px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid var(--brand-green);
    }

    #modal-page .fab-title {
        font-size: 12.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--brand-green);
    }

    #modal-page .fab-demanda-select {
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

    #modal-page .fab-demanda-select:disabled {
        opacity: 0.9;
        cursor: default;
        border-color: transparent;
        background-color: transparent;
    }

    #modal-page .fab-body {
        padding: 12px 18px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        align-items: center;
    }

    #modal-page .fab-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    #modal-page .fab-field label {
        font-size: 11.5px;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
    }

    #modal-page .fab-input-wrap {
        display: flex;
        align-items: center;
        border-bottom: 1.5px solid #cbd5e1;
        padding-bottom: 3px;
        gap: 6px;
    }

    #modal-page .fab-input-wrap input {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        font-size: 15px;
        font-weight: 800;
        color: #111827;
    }

    #modal-page .fab-result-box {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        padding: 8px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #modal-page .fab-result-label {
        font-size: 12px;
        font-weight: 800;
        color: #166534;
        text-transform: uppercase;
    }

    #modal-page .fab-result-val {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
    }

    /* Ocultar Costo de Fabricación en pantalla y al imprimir */
    body.modal-ocultar-fab #modal-page .fab-box {
        display: none !important;
    }

    @media (max-width: 820px) {
        #modal-solar-page .solar-banner-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            padding: 12px 14px;
        }

        #modal-solar-page .solar-banner-right {
            text-align: left;
            font-size: 13px;
        }

        #modal-solar-page table.solar-spec-table td {
            padding: 12px 12px;
            font-size: 14px;
        }

        #modal-solar-page .solar-input-m2 {
            width: 56px !important;
            font-size: 15px;
        }

        #modal-page .fab-box {
            width: 100%;
        }

        #modal-page .fab-body {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }

    @media (max-width: 480px) {
        #modal-solar-page table.solar-spec-table td.solar-label {
            width: 55%;
            font-size: 13px;
            padding: 10px 8px;
        }

        #modal-solar-page table.solar-spec-table td.solar-val {
            width: 45%;
            padding: 8px 6px;
        }

        #modal-solar-page .solar-input-m2 {
            width: 48px !important;
            font-size: 14px;
        }
    }

    @media print {
        body.modal-ocultar-fab #modal-page .fab-box {
            display: none !important;
        }

        #modal-solar-page .solar-box-wrapper {
            margin: 14px 0 20px 0 !important;
            border-radius: 6px !important;
        }

        #modal-solar-page .solar-banner-header {
            flex-direction: row !important;
            padding: 12px 18px !important;
        }

        #modal-solar-page .solar-banner-right {
            text-align: right !important;
        }

        #modal-solar-page table.solar-spec-table td {
            padding: 14px 18px !important;
            font-size: 15px !important;
        }

        #modal-page .fab-box {
            width: 82% !important;
            margin-bottom: 14px !important;
        }

        #modal-page .fab-demanda-select {
            border: none !important;
            background: transparent !important;
            appearance: none;
            -webkit-appearance: none;
            padding: 0 !important;
        }
    }
</style>

<div id="docModal" class="modal-overlay" onclick="if(event.target === this) cerrarModal();">
    <div class="modal-box">
        <div class="modal-topbar">
            <div class="modal-title">
                <span id="modal-mode-label">Vista Previa</span>
                <span class="folio-tag" id="modal-folio-label">0000</span>
                <span id="modal-save-feedback" class="save-feedback">✓ Guardado</span>
            </div>
            <div class="modal-actions">
                <label id="wrap-modal-chk-fab" class="modal-chk-fab" style="display:none;">
                    <input type="checkbox" id="chk-modal-incluir-fab" checked
                        onchange="document.body.classList.toggle('modal-ocultar-fab', !this.checked)">
                    Incluir Costo de Fabricación
                </label>
                <button type="button" id="btn-modal-edit" class="btn-modal-edit" onclick="toggleEdicionModal()">✏️
                    Editar</button>
                <a id="btn-modal-download" href="#" download class="btn-modal-edit" style="display:none;">Descargar</a>
                <button type="button" class="btn-modal-print" onclick="imprimirDesdeModal()">🖨️ Imprimir / PDF</button>
                <button type="button" class="btn-modal-close" onclick="cerrarModal()" title="Cerrar">&times;</button>
            </div>
        </div>

        <div id="modal-body" class="modal-body">
            <!-- 1. CONTENEDOR COTIZACIÓN EMS -->
            <div id="modal-page" class="page">
                <header>
                    <div class="logo-container"><img src="../img/energy.png" alt="EMS" class="logo-ems-doc" /></div>
                    <div class="folio-container">
                        <div class="folio-title">Cotización</div>
                        <div class="folio-number" id="m-folio">0000</div>
                    </div>
                </header>

                <div class="content">
                    <div class="customer-info">
                        <label>NOMBRE:</label>
                        <textarea id="m-cliente" rows="1" readonly oninput="autoResizeEl(this)"></textarea>
                        <label>DIRECCIÓN:</label>
                        <textarea id="m-direccion" rows="1" readonly
                            oninput="autoResizeEl(this); syncModalDireccion();"></textarea>
                        <label>NO. DE SERVICIO:</label>
                        <textarea id="m-servicio" rows="1" readonly oninput="autoResizeEl(this)"></textarea>
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
                                <td><textarea rows="1" readonly>1</textarea></td>
                                <td><textarea rows="1" readonly>Aparato EMS</textarea></td>
                                <td><textarea rows="1" readonly>10 años</textarea></td>
                                <td><textarea id="m-tabla-direccion" rows="1" readonly
                                        style="text-transform: uppercase;" oninput="autoResizeEl(this)"></textarea></td>
                                <td><textarea rows="1" readonly>10 a 15 años</textarea></td>
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
                                                id="m-energia" readonly /></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Cargo fijo:</td>
                                    <td class="value">
                                        <div class="money-cell"><span class="currency">$</span><input type="text"
                                                id="m-cargo" readonly /></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">IVA:</td>
                                    <td class="value">
                                        <div class="money-cell"><span class="currency">$</span><input type="text"
                                                id="m-iva-cfe" readonly /></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Total a pagar:</td>
                                    <td class="value">
                                        <div class="money-cell"><span class="currency">$</span><input type="text"
                                                id="m-total-cfe" readonly /></div>
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
                                                type="text" id="m-subtotal" readonly /></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">IVA:</td>
                                    <td class="value">
                                        <div class="money-cell finance-color"><span class="currency">$</span><input
                                                type="text" id="m-iva" readonly /></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Total:</td>
                                    <td class="value">
                                        <div class="money-cell finance-color"><span class="currency">$</span><input
                                                type="text" id="m-total" readonly /></div>
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
                                <span class="payment-amount"><span>$</span><strong id="m-anticipo">0.00</strong></span>
                            </div>
                            <div class="payment-row">
                                <span>Una vez colocado, el restante 40%:</span>
                                <span class="payment-amount"><span>$</span><strong id="m-restante">0.00</strong></span>
                            </div>
                        </div>

                        <!-- COSTO DE FABRICACIÓN EN MODAL -->
                        <div class="fab-box">
                            <div class="fab-header">
                                <span class="fab-title">🛠️ Costo de Fabricación</span>
                                <select id="m-tipo-demanda" class="fab-demanda-select" disabled onchange="actualizarModalEtiquetaFab()">
                                    <option value="Gran Demanda">🏭 Gran Demanda</option>
                                    <option value="Baja Demanda">🏠 Baja Demanda</option>
                                </select>
                            </div>
                            <div class="fab-body">
                                <div class="fab-field">
                                    <label id="m-label-dato-fab">Demanda contratada (kW):</label>
                                    <div class="fab-input-wrap">
                                        <input type="number" id="m-dato-fab" placeholder="0" step="any" min="0" readonly
                                            oninput="calcularModalCostoFab()" />
                                    </div>
                                </div>
                                <div class="fab-result-box">
                                    <span class="fab-result-label">Costo Total:</span>
                                    <span class="fab-result-val" id="m-costo-fab">$0.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="agent-bottom-info">
                            <div class="agent-field">
                                <input type="text" id="m-agente" readonly />
                                <label>AGENTE DE VENTAS</label>
                            </div>
                            <div class="agent-field">
                                <input type="text" id="m-telefono" readonly />
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
                        <p><a href="https://energymultipliersystem.com/" target="_blank">energymultipliersystem.com</a>
                        </p>
                    </div>
                </footer>
            </div>

            <!-- 2. CONTENEDOR COTIZACIÓN PANELES SOLARES -->
            <div id="modal-solar-page" class="page" style="display:none;">
                <header>
                    <div class="folio-container" style="text-align: left;">
                        <div class="folio-title">Cotización Paneles Solares</div>
                        <div class="folio-number" id="ms-folio">PS-0100</div>
                    </div>
                </header>

                <div class="content">
                    <div class="customer-info">
                        <label>NOMBRE:</label>
                        <textarea id="ms-cliente" rows="1" readonly
                            oninput="autoResizeEl(this); syncModalSolarBanner();"></textarea>
                        <label>NO. DE SERVICIO:</label>
                        <textarea id="ms-servicio" rows="1" readonly oninput="autoResizeEl(this)"></textarea>
                    </div>

                    <div class="solar-box-wrapper">
                        <div class="solar-banner-header">

                        </div>
                        <table class="solar-spec-table">
                            <tr>
                                <td class="solar-label">
                                    Paneles de <input type="number" id="ms-watts" class="solar-watts-inline" value="600"
                                        readonly oninput="recalcularModalSolar()">W Necesarios:
                                </td>
                                <td class="solar-val">
                                    <input type="number" id="ms-paneles" class="solar-input-paneles" readonly
                                        oninput="recalcularModalSolar()" />
                                </td>
                            </tr>
                            <tr>
                                <td class="solar-label">Potencia Instalada Real (kW):</td>
                                <td class="solar-val">
                                    <input type="text" id="ms-kw" class="solar-input-kw" readonly />
                                </td>
                            </tr>
                            <tr>
                                <td class="solar-label">Superficie Requerida (M²):</td>
                                <td class="solar-val">
                                    <div class="solar-m2-wrap">
                                        <input type="number" id="ms-m2" class="solar-input-m2" min="1" step="any" readonly />
                                        <span class="solar-m2-unit">m²</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="solar-label bold-row">INVERSIÓN TOTAL ESTIMADA (Rango):</td>
                                <td class="solar-val highlight-row">
                                    <input type="text" id="ms-rango" class="solar-input-rango" readonly />
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
                        <p><a href="https://energymultipliersystem.com/" target="_blank">energymultipliersystem.com</a>
                        </p>
                    </div>
                </footer>
            </div>

            <!-- 3. CONTENEDOR RECIBOS CFE -->
            <div id="modal-recibo-view" class="recibo-viewer-wrap" style="display:none;">
                <div class="recibo-meta-bar">
                    <div><strong>Cliente:</strong> <span id="r-cliente"></span></div>
                    <div><strong>No. Servicio:</strong> <span id="r-servicio"></span></div>
                </div>
                <iframe id="r-iframe" class="recibo-iframe" style="display:none;"></iframe>
                <img id="r-img" class="recibo-img" style="display:none;" alt="Recibo CFE">
            </div>
        </div>
    </div>
</div>