<!-- === VENTANA EMERGENTE UNIVERSAL (COTIZACIONES EMS, PANELES SOLARES Y RECIBOS CFE) === -->
<div id="docModal" class="modal-overlay" onclick="if(event.target === this) cerrarModal();">
    <div class="modal-box">
        <div class="modal-topbar">
            <div class="modal-title">
                <span id="modal-mode-label">Vista Previa</span>
                <span class="folio-tag" id="modal-folio-label">0000</span>
                <span id="modal-save-feedback" class="save-feedback">✓ Guardado</span>
            </div>
            <div class="modal-actions">
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
                    <div class="folio-container" style="margin-left: auto;">
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
                                <td class="solar-label bold-row">INVERSIÓN TOTAL ESTIMADA (Rango):</td>
                                <td class="solar-val" style="background-color: #f0fdf4;">
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