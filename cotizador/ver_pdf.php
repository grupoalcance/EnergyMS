<?php
session_start();
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    die("Acceso denegado.");
}
include 'conexion.php';
if (!isset($_GET['id'])) {
    die("ID no especificado.");
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM cotizaciones WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("Cotización no encontrada.");
}
$cotizacion = $result->fetch_assoc();
$conn->close();

function cleanMoney($val)
{
    $num = floatval(preg_replace('/[^0-9.-]/', '', $val));
    return $num > 0 ? number_format($num, 2, '.', ',') : "";
}

$energia_val = floatval(preg_replace('/[^0-9.-]/', '', $cotizacion['energia_cfe']));
$cargo_val = floatval(preg_replace('/[^0-9.-]/', '', $cotizacion['cargo_cfe']));
$iva_cfe_val = ($energia_val + $cargo_val) * 0.16;
$iva_cfe_str = $iva_cfe_val > 0 ? number_format($iva_cfe_val, 2, '.', ',') : "";
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cotización <?php echo htmlspecialchars($cotizacion['folio']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --green-brand: #9bb556;
            --yellow-line: #fbbd30;
        }

        body {
            font-family: "Arial", sans-serif;
            background-color: #e5e7eb;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .page {
            background-color: white;
            width: 100%;
            max-width: 800px;
            min-height: 1050px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        * {
            box-sizing: border-box;
        }

        input,
        textarea {
            font-family: inherit;
            font-size: inherit;
            color: inherit;
        }

        /* === REGLAS DE IMPRESIÓN === */
        @media print {
            @page {
                margin: 0;
            }

            body {
                background-color: white;
                padding: 0;
                margin: 0;
            }

            .page {
                box-shadow: none;
                max-width: 100%;
                min-height: auto;
                page-break-after: avoid;
                page-break-inside: avoid;
            }

            .no-print {
                display: none !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            ::-webkit-input-placeholder {
                color: transparent !important;
            }

            ::-moz-placeholder {
                color: transparent !important;
            }

            :-ms-input-placeholder {
                color: transparent !important;
            }

            ::placeholder {
                color: transparent !important;
            }

            .agent-field input {
                border-bottom: 1px solid #000 !important;
                color: #000 !important;
            }

            header {
                padding: 20px 40px !important;
            }

            .content {
                padding: 20px 40px !important;
            }

            table.products {
                margin-bottom: 20px !important;
            }

            .split-section {
                margin-bottom: 20px !important;
            }

            .payment-conditions {
                margin-bottom: 30px !important;
                padding: 10px 30px !important;
            }

            .agent-bottom-info {
                margin-top: 30px !important;
                margin-bottom: 10px !important;
            }

            footer {
                padding: 15px 40px !important;
            }
        }

        header {
            background-color: #000;
            color: #fff;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
        }

        .logo-ems {
            max-width: 220px;
            height: auto;
        }

        .folio-container {
            text-align: right;
        }

        .folio-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .folio-number {
            font-size: 20px;
            font-weight: 800;
        }

        .content {
            padding: 40px;
            flex-grow: 1;
        }

        .customer-info {
            display: grid;
            grid-template-columns: 140px 1fr;
            row-gap: 15px;
            margin-bottom: 30px;
            align-items: end;
        }

        .customer-info label {
            font-weight: 800;
            font-size: 15px;
        }

        .customer-info textarea {
            width: 100%;
            border: none;
            border-bottom: 2px solid var(--yellow-line);
            padding: 5px 0;
            outline: none;
            text-transform: uppercase;
            resize: none;
            overflow: hidden;
        }

        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        table.products th {
            background-color: var(--green-brand);
            color: #000;
            font-weight: 700;
            padding: 10px;
            font-size: 13px;
            border: 1px solid #ccc;
        }

        table.products td {
            border: 1px solid #ccc;
            padding: 0;
            height: 70px;
            vertical-align: middle;
        }

        table.products td textarea {
            width: 100%;
            height: 100%;
            border: none;
            text-align: center;
            outline: none;
            padding: 10px;
            font-weight: 600;
            resize: none;
            background-color: transparent;
            overflow: hidden;
        }

        /* === SECCIÓN DIVIDIDA === */
        .split-section {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 30px;
        }

        .left-box,
        .right-box {
            width: 45%;
        }

        .red-title {
            color: #e53e3e;
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 8px;
            text-align: center;
            letter-spacing: 1px;
        }

        .green-title {
            color: var(--green-brand);
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 8px;
            text-align: center;
            letter-spacing: 1px;
        }

        .cfe-title {
            font-weight: 800;
            font-size: 12px;
            margin-bottom: 5px;
            text-align: center;
        }

        .table-mini {
            width: 100%;
            border-collapse: collapse;
        }

        .table-mini td {
            border: 1px solid #000;
            height: 35px;
        }

        .table-mini td.label {
            background-color: var(--green-brand);
            font-weight: 700;
            font-size: 13px;
            padding-left: 15px;
            width: 40%;
        }

        /* === ALINEACIÓN PERFECTA === */
        .table-mini td.value {
            width: 60%;
            padding: 0;
        }

        .money-cell {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
            height: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        .money-cell .currency {
            font-weight: 700;
            color: #7b291d;
        }

        .money-cell input {
            flex-grow: 1;
            text-align: right;
            width: 100%;
            border: none;
            font-weight: 700;
            color: #7b291d;
            outline: none;
            background-color: transparent;
            padding: 0;
            margin: 0;
            font-family: inherit;
            font-size: 14px;
        }

        .finance-color .currency,
        .finance-color input {
            color: #000 !important;
        }

        /* === CONDICIONES DE PAGO === */
        .bottom-section {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .payment-conditions {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px 30px;
            border-radius: 8px;
            width: 80%;
            margin-bottom: 40px;
            text-align: left;
        }

        .payment-title {
            font-weight: 800;
            font-size: 13px;
            color: #1a202c;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            text-align: center;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 8px;
            color: #4a5568;
            border-bottom: 1px dashed #cbd5e0;
            padding-bottom: 5px;
        }

        .payment-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .payment-amount {
            display: flex;
            justify-content: space-between;
            width: 120px;
            font-weight: 800;
            color: #1a202c;
        }

        /* === VENDEDOR === */
        .agent-bottom-info {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 40px;
            margin-bottom: 20px;
            width: 100%;
        }

        .agent-field {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            width: 280px;
        }

        .agent-field input {
            border: none;
            border-bottom: 1px solid #ccc;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            color: #1a202c;
            padding: 4px 0 2px 0;
            text-transform: uppercase;
            background-color: transparent;
            width: 100%;
            text-align: center;
        }

        .agent-field label {
            font-weight: 700;
            font-size: 11px;
            color: #718096;
            letter-spacing: 0.5px;
        }

        footer {
            background-color: #000;
            color: #fff;
            padding: 20px 40px;
            font-size: 11px;
            font-weight: 600;
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
        }

        .footer-right a {
            color: var(--green-brand);
            text-decoration: none;
        }

        footer p {
            margin: 3px 0;
        }

        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #1a202c;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s;
            z-index: 100;
        }

        .print-btn:hover {
            transform: scale(1.05);
        }

        .panel-btn {
            position: fixed;
            top: 30px;
            left: 30px;
            background-color: white;
            color: #1a202c;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            border-color: #cbd5e0;
        }
    </style>
</head>

<body>
    <button class="no-print print-btn" onclick="guardarEImprimir()">🖨️ Guardar Cambios y PDF</button>
    <a href="panel.php" class="no-print panel-btn">Volver al Panel</a>

    <div class="page">
        <header>
            <div class="logo-container"><img src="/img/energy.png" alt="EMS" class="logo-ems" /></div>
            <div class="folio-container">
                <div class="folio-title">Cotización</div>
                <div class="folio-number" id="folio-number"><?php echo htmlspecialchars($cotizacion['folio']); ?></div>
            </div>
        </header>

        <div class="content">
            <div class="customer-info">
                <label>NOMBRE:</label>
                <textarea id="input-cliente" rows="1"
                    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';"><?php echo htmlspecialchars($cotizacion['cliente']); ?></textarea>
                <label>DIRECCIÓN:</label>
                <textarea id="input-direccion" rows="1"
                    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';"><?php echo htmlspecialchars($cotizacion['direccion']); ?></textarea>
                <label>NO. DE SERVICIO:</label>
                <textarea id="input-servicio" rows="1"
                    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';"><?php echo htmlspecialchars($cotizacion['servicio_cfe']); ?></textarea>
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
                        <td><textarea rows="1"
                                oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">1</textarea>
                        </td>
                        <td><textarea rows="1"
                                oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">Aparato EMS</textarea>
                        </td>
                        <td><textarea rows="1"
                                oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">10 años</textarea>
                        </td>
                        <td><textarea id="input-tabla-direccion" rows="1"
                                oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';"><?php echo htmlspecialchars($cotizacion['direccion']); ?></textarea>
                        </td>
                        <td><textarea rows="1"
                                oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">10 a 15 años</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="split-section">
                <!-- CFE BOX -->
                <div class="left-box">
                    <div class="red-title">Ahorro Estimado</div>
                    <div class="cfe-title">Una vez colocado se espera un pago a CFE de</div>
                    <table class="table-mini">
                        <tr>
                            <td class="label">Energía:</td>
                            <td class="value">
                                <div class="money-cell">
                                    <span class="currency">$</span>
                                    <input type="text" id="input-energia"
                                        value="<?php echo cleanMoney($cotizacion['energia_cfe']); ?>" />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Cargo fijo:</td>
                            <td class="value">
                                <div class="money-cell">
                                    <span class="currency">$</span>
                                    <input type="text" id="input-cargo"
                                        value="<?php echo cleanMoney($cotizacion['cargo_cfe']); ?>" />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">IVA:</td>
                            <td class="value">
                                <div class="money-cell">
                                    <span class="currency">$</span>
                                    <input type="text" id="iva-cfe-display" value="<?php echo $iva_cfe_str; ?>"
                                        readonly />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Total a pagar:</td>
                            <td class="value">
                                <div class="money-cell">
                                    <span class="currency">$</span>
                                    <input type="text" id="input-total-cfe"
                                        value="<?php echo cleanMoney($cotizacion['total_cfe']); ?>" readonly />
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- FINANCE BOX -->
                <div class="right-box">
                    <div class="green-title">Precio</div>
                    <table class="table-mini" style="margin-top: 19px;">
                        <tr>
                            <td class="label">Subtotal:</td>
                            <td class="value">
                                <div class="money-cell finance-color">
                                    <span class="currency">$</span>
                                    <input type="text" id="input-subtotal"
                                        value="<?php echo cleanMoney($cotizacion['subtotal']); ?>" />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">IVA:</td>
                            <td class="value">
                                <div class="money-cell finance-color">
                                    <span class="currency">$</span>
                                    <input type="text" id="iva-display"
                                        value="<?php echo cleanMoney($cotizacion['iva']); ?>" readonly />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Total:</td>
                            <td class="value">
                                <div class="money-cell finance-color">
                                    <span class="currency">$</span>
                                    <input type="text" id="total-display"
                                        value="<?php echo cleanMoney($cotizacion['total']); ?>" readonly />
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="bottom-section">
                <!-- CONDICIONES DE PAGO JUSTIFICADAS -->
                <div class="payment-conditions">
                    <div class="payment-title">Condiciones de Pago</div>
                    <div class="payment-row">
                        <span>Anticipo 60%:</span>
                        <span class="payment-amount"><span>$</span><strong
                                id="anticipo-display"><?php echo cleanMoney($cotizacion['anticipo']); ?></strong></span>
                    </div>
                    <div class="payment-row">
                        <span>Una vez colocado, el restante 40%:</span>
                        <span class="payment-amount"><span>$</span><strong
                                id="restante-display"><?php echo cleanMoney($cotizacion['restante']); ?></strong></span>
                    </div>
                </div>

                <!-- INFORMACIÓN DEL AGENTE -->
                <div class="agent-bottom-info">
                    <div class="agent-field">
                        <input type="text" id="input-agente-nombre"
                            value="<?php echo htmlspecialchars($cotizacion['agente_nombre']); ?>" />
                        <label>AGENTE DE VENTAS</label>
                    </div>
                    <div class="agent-field">
                        <input type="text" id="input-agente-telefono"
                            value="<?php echo htmlspecialchars($cotizacion['agente_telefono']); ?>" />
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

    <script>
        const inputEnergia = document.getElementById("input-energia"), inputCargo = document.getElementById("input-cargo");
        const inputTotalCfe = document.getElementById("input-total-cfe"), inputSubtotal = document.getElementById("input-subtotal");
        const ivaDisplay = document.getElementById("iva-display"), totalDisplay = document.getElementById("total-display");
        const anticipoDisplay = document.getElementById("anticipo-display"), restanteDisplay = document.getElementById("restante-display");

        // === SINCRONIZAR DIRECCIÓN ===
        document.getElementById("input-direccion").addEventListener("input", function (e) {
            const tablaDir = document.getElementById("input-tabla-direccion");
            tablaDir.value = e.target.value;
            tablaDir.style.height = '';
            tablaDir.style.height = tablaDir.scrollHeight + 'px';
        });

        const numFormatter = new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function cleanFormat(e) { e.target.value = e.target.value.replace(/[^0-9.-]+/g, ""); }
        function applyFormat(e) {
            let val = parseFloat(e.target.value.replace(/[^0-9.-]+/g, ""));
            e.target.value = !isNaN(val) ? numFormatter.format(val) : "";
        }

        [inputEnergia, inputCargo, inputSubtotal].forEach((input) => {
            input.addEventListener("focus", cleanFormat); input.addEventListener("blur", applyFormat);
        });

        // === CÁLCULO CFE ===
        function calcularCFE() {
            let energia = parseFloat(inputEnergia.value.replace(/[^0-9.-]+/g, "")) || 0;
            let cargo = parseFloat(inputCargo.value.replace(/[^0-9.-]+/g, "")) || 0;

            if (energia > 0 || cargo > 0) {
                let sumaBase = energia + cargo;
                let ivaCfe = sumaBase * 0.16;
                let totalCfe = sumaBase + ivaCfe;

                document.getElementById("iva-cfe-display").value = numFormatter.format(ivaCfe);
                inputTotalCfe.value = numFormatter.format(totalCfe);
            } else {
                document.getElementById("iva-cfe-display").value = "";
                inputTotalCfe.value = "";
            }
        }
        inputEnergia.addEventListener("input", calcularCFE);
        inputCargo.addEventListener("input", calcularCFE);

        inputSubtotal.addEventListener("input", function (e) {
            let subtotal = parseFloat(e.target.value.replace(/[^0-9.-]+/g, ""));
            if (isNaN(subtotal)) subtotal = 0;
            let iva = subtotal * 0.16, total = subtotal + iva;
            ivaDisplay.value = numFormatter.format(iva);
            totalDisplay.value = numFormatter.format(total);
            anticipoDisplay.innerText = numFormatter.format(total * 0.6);
            restanteDisplay.innerText = numFormatter.format(total * 0.4);
        });

        function recolectarDatos() {
            const curr = (val) => val.trim() !== "" ? "$" + val.trim() : "";
            return {
                id: <?php echo $id; ?>,
                folio: document.getElementById("folio-number").innerText,
                cliente: document.getElementById("input-cliente").value,
                direccion: document.getElementById("input-direccion").value,
                servicio: document.getElementById("input-servicio").value,
                energia: curr(inputEnergia.value),
                cargo: curr(inputCargo.value),
                total_cfe: curr(inputTotalCfe.value),
                subtotal: curr(inputSubtotal.value),
                iva: curr(ivaDisplay.value),
                total: curr(totalDisplay.value),
                anticipo: curr(anticipoDisplay.innerText),
                restante: curr(restanteDisplay.innerText),
                agente_nombre: document.getElementById("input-agente-nombre").value,
                agente_telefono: document.getElementById("input-agente-telefono").value
            };
        }

        async function guardarEImprimir() {
            try {
                await fetch("guardar.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(recolectarDatos()) });
            } catch (error) { console.error("Error:", error); }
            window.print();
        }
    </script>
</body>

</html>