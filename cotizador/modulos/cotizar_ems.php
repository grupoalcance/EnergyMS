<?php
if (!isset($conn)) {
    header("Location: ../dashboard.php?seccion=cotizar_ems");
    exit;
}
?>

<button class="print-btn-float" onclick="guardarEImprimir()">🖨️ Imprimir / Guardar PDF</button>

<div class="page-container">
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
            folio: document.getElementById("folio-number").innerText,
            cliente: document.getElementById("input-cliente").value,
            direccion: document.getElementById("input-direccion").value,
            servicio: document.getElementById("input-servicio").value,
            energia: cv(inEnergia.value),
            cargo: cv(inCargo.value),
            total_cfe: cv(inTotalCfe.value),
            subtotal: cv(inSubtotal.value),
            iva: cv(dIva.value),
            total: cv(dTotal.value),
            anticipo: cv(dAnticipo.innerText),
            restante: cv(dRestante.innerText),
            agente_nombre: document.getElementById("input-agente-nombre").value,
            agente_telefono: document.getElementById("input-agente-telefono").value
        };
    }

    async function guardarEImprimir() {
        try {
            await fetch("guardar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(getDatos())
            });
        } catch (e) {
            console.error("Error al guardar cotización EMS:", e);
        }
        window.print();
    }
</script>