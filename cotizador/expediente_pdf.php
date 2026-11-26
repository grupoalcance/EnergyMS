<?php
session_start();
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: panel.php");
    exit;
}

include 'conexion.php';

$r_id = isset($_GET['r']) ? intval($_GET['r']) : 0;
$s_id = isset($_GET['s']) ? intval($_GET['s']) : 0;
$e_id = isset($_GET['e']) ? intval($_GET['e']) : 0;

$recibo = null;
$solar = null;
$ems = null;

if ($r_id > 0) {
    $res = @$conn->query("SELECT * FROM recibos_cfe WHERE id = $r_id LIMIT 1");
    if ($res && $res->num_rows > 0)
        $recibo = $res->fetch_assoc();
}
if ($s_id > 0) {
    $res = @$conn->query("SELECT * FROM cotizaciones_solares WHERE id = $s_id LIMIT 1");
    if ($res && $res->num_rows > 0)
        $solar = $res->fetch_assoc();
}
if ($e_id > 0) {
    $res = @$conn->query("SELECT * FROM cotizaciones WHERE id = $e_id LIMIT 1");
    if ($res && $res->num_rows > 0)
        $ems = $res->fetch_assoc();
}

$nombre_cliente = trim(preg_replace('/\s+/', ' ', (string) ($ems['cliente'] ?? $solar['cliente'] ?? $recibo['cliente'] ?? 'Cliente')));
$folio_principal = trim((string) ($ems['folio'] ?? $solar['folio'] ?? $recibo['folio'] ?? ''));
$titulo_pdf = trim('Cotizacion ' . ($folio_principal !== '' ? $folio_principal . ' ' : '') . $nombre_cliente);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo_pdf); ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    </script>
    <style>
        :root {
            --brand-green: #9bb556;
            --yellow-line: #fbbd30;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background-color: #e5e7eb;
            font-family: Arial, sans-serif;
            padding-top: 66px;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #111827;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            z-index: 100;
            border-bottom: 3px solid var(--brand-green);
        }

        .topbar-title {
            font-weight: 800;
            font-size: 15px;
        }

        .topbar-title span {
            color: var(--brand-green);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .chk-fab {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .btn-top {
            border: none;
            padding: 9px 18px;
            border-radius: 7px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-print {
            background: var(--brand-green);
            color: #000;
        }

        .btn-print:disabled {
            opacity: 0.65;
            cursor: wait;
        }

        .btn-close {
            background: #374151;
            color: #fff;
        }

        .sheet {
            background: #fff;
            width: 8.5in;
            min-height: 11in;
            margin: 20px auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            page-break-after: always;
            break-after: page;
        }

        .sheet:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .sheet header {
            background: #000;
            color: #fff;
            padding: 15px 38px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-doc {
            max-width: 155px;
            height: auto;
        }

        .folio-title {
            font-size: 19px;
            font-weight: 700;
        }

        .folio-number {
            font-size: 17px;
            font-weight: 800;
            color: var(--brand-green);
        }

        .sheet .content {
            padding: 24px 38px;
            flex-grow: 1;
        }

        .sheet footer {
            background: #000;
            color: #fff;
            padding: 14px 38px;
            font-size: 11px;
            font-weight: 600;
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sheet footer p {
            margin: 2px 0;
        }

        .sheet footer a {
            color: var(--brand-green);
            text-decoration: none;
        }

        .customer-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            row-gap: 10px;
            margin-bottom: 20px;
            align-items: end;
        }

        .customer-info label {
            font-weight: 800;
            font-size: 13px;
        }

        .customer-val {
            border-bottom: 2px solid var(--yellow-line);
            padding: 3px 0;
            font-weight: 700;
            font-size: 13.5px;
            text-transform: uppercase;
        }

        /* Recibo */
        .recibo-sheet-body {
            padding: 14px 20px;
            display: block;
            text-align: center;
        }

        .recibo-sheet-body img {
            display: block;
            margin: 0 auto;
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 8.8in;
            object-fit: contain;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Solar */
        .solar-box {
            border: 1.5px solid #111827;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
        }

        .solar-bar {
            background: #111827;
            height: 14px;
            border-bottom: 3px solid var(--brand-green);
        }

        table.solar-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        table.solar-tbl td {
            border-top: 1px solid #d1d5db;
            padding: 15px 20px;
            font-size: 15px;
        }

        table.solar-tbl tr:first-child td {
            border-top: none;
        }

        table.solar-tbl td.lbl {
            width: 58%;
            font-weight: 600;
            background: #f9fafb;
            border-right: 1px solid #d1d5db;
        }

        table.solar-tbl td.val {
            width: 42%;
            text-align: center;
            font-weight: 800;
            font-size: 18px;
        }

        table.solar-tbl tr.hl td {
            background: #f0fdf4;
            font-weight: 800;
        }

        /* EMS */
        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        table.products th {
            background: var(--brand-green);
            color: #000;
            padding: 8px;
            font-size: 12.5px;
            border: 1px solid #ccc;
        }

        table.products td {
            border: 1px solid #ccc;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
        }

        .split {
            display: flex;
            justify-content: space-between;
            gap: 32px;
            margin-bottom: 18px;
        }

        .col {
            width: 47%;
        }

        .red-t {
            color: #e53e3e;
            font-weight: 800;
            font-size: 13.5px;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .green-t {
            color: var(--brand-green);
            font-weight: 800;
            font-size: 13.5px;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .cfe-t {
            font-weight: 800;
            font-size: 11.5px;
            text-align: center;
            margin-bottom: 5px;
        }

        table.mini {
            width: 100%;
            border-collapse: collapse;
        }

        table.mini td {
            border: 1px solid #000;
            padding: 6px 12px;
            font-size: 13px;
        }

        table.mini td.lbl {
            background: var(--brand-green);
            font-weight: 700;
            width: 44%;
        }

        table.mini td.val {
            font-weight: 700;
            color: #7b291d;
        }

        table.mini.fin td.val {
            color: #000;
        }

        .money-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .pay-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 24px;
            width: 84%;
            margin: 0 auto 14px auto;
        }

        .pay-title {
            font-weight: 800;
            font-size: 12.5px;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .pay-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            padding: 4px 0;
            border-bottom: 1px dashed #cbd5e0;
        }

        .pay-row:last-child {
            border-bottom: none;
        }

        .pay-val-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-width: 130px;
        }

        .fab-box {
            background: #f8fafc;
            border: 1.5px solid #111827;
            border-radius: 8px;
            width: 84%;
            margin: 0 auto 16px auto;
            overflow: hidden;
        }

        .fab-head {
            background: #111827;
            color: #fff;
            padding: 7px 16px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 800;
            border-bottom: 2px solid var(--brand-green);
        }

        .fab-head span.t {
            color: var(--brand-green);
            text-transform: uppercase;
        }

        .fab-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            font-size: 13px;
        }

        .fab-total {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 15px;
        }

        .agent-row {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 16px;
        }

        .agent-col {
            width: 250px;
            text-align: center;
        }

        .agent-val {
            border-bottom: 1px solid #000;
            font-weight: 800;
            font-size: 13px;
            padding-bottom: 3px;
            text-transform: uppercase;
            min-height: 18px;
        }

        .agent-lbl {
            font-size: 10.5px;
            font-weight: 700;
            color: #6b7280;
            margin-top: 4px;
        }

        body.ocultar-fab .fab-box {
            display: none !important;
        }

        @media print {
            @page {
                size: letter;
                margin: 0;
            }

            body {
                background: #fff;
                padding-top: 0;
            }

            .topbar {
                display: none !important;
            }

            .sheet {
                margin: 0;
                box-shadow: none;
                width: 8.5in;
                height: 10.95in;
                min-height: 10.95in;
                overflow: hidden;
                position: relative;
                display: block;
            }

            .sheet footer {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
            }

            .recibo-sheet-body img {
                max-height: 8.8in !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="topbar-title">📁 Expediente: <span><?php echo htmlspecialchars($nombre_cliente); ?></span></div>
        <div class="topbar-actions">
            <?php if ($ems): ?>
                <label class="chk-fab">
                    <input type="checkbox" checked onchange="document.body.classList.toggle('ocultar-fab', !this.checked)">
                    Incluir Costo de Fabricación
                </label>
            <?php endif; ?>
            <button type="button" class="btn-top btn-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
            <button type="button" class="btn-top btn-close" onclick="window.close()">Cerrar</button>
        </div>
    </div>

    <!-- HOJA 1: RECIBO CFE -->
    <?php if ($recibo): ?>
        <section class="sheet">
            <header>
                <div style="text-align:left;">
                    <div class="folio-title">Recibo de Luz CFE</div>
                    <div class="folio-number">
                        <?php echo htmlspecialchars($recibo['folio']); ?>
                        <?php echo $recibo['tipo_demanda'] ? '• ' . htmlspecialchars($recibo['tipo_demanda']) : ''; ?>
                    </div>
                </div>
                <img src="../img/energy.png" alt="EMS" class="logo-doc">
            </header>
            <div class="recibo-sheet-body" id="recibo-container">
                <?php if ($recibo['archivo_tipo'] === 'pdf'): ?>
                    <div id="pdf-loading" style="color:#6b7280; font-size:14px;">Cargando recibo PDF para impresión...</div>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($recibo['archivo_ruta']); ?>" alt="Recibo CFE">
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- HOJA 2: COTIZACIÓN PANELES SOLARES -->
    <?php if ($solar): ?>
        <section class="sheet">
            <header>
                <div style="text-align:left;">
                    <div class="folio-title">Cotización Paneles Solares</div>
                    <div class="folio-number"><?php echo htmlspecialchars($solar['folio']); ?></div>
                </div>
            </header>
            <div class="content">
                <div class="customer-info">
                    <label>NOMBRE:</label>
                    <div class="customer-val"><?php echo htmlspecialchars($solar['cliente']); ?></div>
                    <label>NO. DE SERVICIO:</label>
                    <div class="customer-val"><?php echo htmlspecialchars($solar['servicio_cfe']); ?></div>
                </div>

                <div class="solar-box">
                    <div class="solar-bar"></div>
                    <table class="solar-tbl">
                        <tr>
                            <td class="lbl">Paneles de <?php echo intval($solar['watts_panel'] ?: 600); ?>W Necesarios:</td>
                            <td class="val" style="color:#dc2626;"><?php echo intval($solar['paneles']); ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Potencia Instalada Real (kW):</td>
                            <td class="val"><?php echo htmlspecialchars($solar['potencia_kw']); ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Superficie Requerida (M²):</td>
                            <td class="val"><?php echo htmlspecialchars($solar['metros_cuadrados'] ?? '—'); ?> m²</td>
                        </tr>
                        <tr class="hl">
                            <td class="lbl">INVERSIÓN TOTAL ESTIMADA (Rango):</td>
                            <td class="val"><?php echo htmlspecialchars($solar['inversion_rango']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <footer>
                <div>
                    <p>BLVD. INDEPENDENCIA ESQ. CALZ. COLÓN</p>
                    <p>TORREÓN, COAHUILA</p>
                </div>
                <div>
                    <p><a href="https://energymultipliersystem.com/">energymultipliersystem.com</a></p>
                </div>
            </footer>
        </section>
    <?php endif; ?>

    <!-- HOJA 3: COTIZACIÓN EMS + COSTO DE FABRICACIÓN -->
    <?php if ($ems): ?>
        <?php
        $fmtMoney = function ($v) {
            $n = floatval(preg_replace('/[^0-9.-]/', '', (string)$v));
            return number_format($n, 2, '.', ',');
        };
        $e_num = floatval(preg_replace('/[^0-9.-]/', '', $ems['energia_cfe']));
        $c_num = floatval(preg_replace('/[^0-9.-]/', '', $ems['cargo_cfe']));
        $iva_cfe_num = number_format(($e_num + $c_num) * 0.16, 2, '.', ',');
        $dem_ems = $ems['tipo_demanda'] ?? '';
        $lbl_fab = ($dem_ems === 'Baja Demanda') ? 'Lectura total' : 'Demanda contratada (kW)';
        ?>
        <section class="sheet">
            <header>
                <img src="../img/energy.png" alt="EMS" class="logo-doc">
                <div style="text-align:right;">
                    <div class="folio-title">Cotización</div>
                    <div class="folio-number"><?php echo htmlspecialchars($ems['folio']); ?></div>
                </div>
            </header>
            <div class="content">
                <div class="customer-info">
                    <label>NOMBRE:</label>
                    <div class="customer-val"><?php echo htmlspecialchars($ems['cliente']); ?></div>
                    <label>DIRECCIÓN:</label>
                    <div class="customer-val"><?php echo htmlspecialchars($ems['direccion']); ?></div>
                    <label>NO. DE SERVICIO:</label>
                    <div class="customer-val"><?php echo htmlspecialchars($ems['servicio_cfe']); ?></div>
                </div>

                <table class="products">
                    <thead>
                        <tr>
                            <th style="width:8%">cant.</th>
                            <th style="width:24%">Descripción</th>
                            <th style="width:19%">Garantía de fábrica</th>
                            <th style="width:28%">Para colocarlo en</th>
                            <th style="width:21%">Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Aparato EMS</td>
                            <td>10 años</td>
                            <td style="text-transform:uppercase;"><?php echo htmlspecialchars($ems['direccion']); ?></td>
                            <td>10 a 15 años</td>
                        </tr>
                    </tbody>
                </table>

                <div class="split">
                    <div class="col">
                        <div class="red-t">Ahorro Estimado</div>
                        <div class="cfe-t">Una vez colocado se espera un pago a CFE de</div>
                        <table class="mini">
                            <tr>
                                <td class="lbl">Energía:</td>
                                <td class="val">
                                    <div class="money-flex"><span>$</span><span><?php echo $fmtMoney($ems['energia_cfe']); ?></span></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Cargo fijo:</td>
                                <td class="val">
                                    <div class="money-flex"><span>$</span><span><?php echo $fmtMoney($ems['cargo_cfe']); ?></span></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">IVA:</td>
                                <td class="val">
                                    <div class="money-flex"><span>$</span><span><?php echo $iva_cfe_num; ?></span></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Total a pagar:</td>
                                <td class="val">
                                    <div class="money-flex"><span>$</span><span><?php echo $fmtMoney($ems['total_cfe']); ?></span></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col">
                        <div class="green-t">Precio</div>
                        <table class="mini fin" style="margin-top:18px;">
                            <tr>
                                <td class="lbl">Subtotal:</td>
                                <td class="val">
                                    <div class="money-flex"><span>$</span><span><?php echo $fmtMoney($ems['subtotal']); ?></span></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">IVA:</td>
                                <td class="val">
                                    <div class="money-flex"><span>$</span><span><?php echo $fmtMoney($ems['iva']); ?></span></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Total:</td>
                                <td class="val">
                                    <div class="money-flex"><span>$</span><span><?php echo $fmtMoney($ems['total']); ?></span></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="pay-box">
                    <div class="pay-title">Condiciones de Pago</div>
                    <div class="pay-row">
                        <span>Anticipo 60%:</span>
                        <span class="pay-val-wrap"><span>$</span><strong><?php echo $fmtMoney($ems['anticipo']); ?></strong></span>
                    </div>
                    <div class="pay-row">
                        <span>Una vez colocado, el restante 40%:</span>
                        <span class="pay-val-wrap"><span>$</span><strong><?php echo $fmtMoney($ems['restante']); ?></strong></span>
                    </div>
                </div>

                <?php if (!empty($ems['costo_fabricacion']) && $ems['costo_fabricacion'] !== '$0.00'): ?>
                    <div class="fab-box">
                        <div class="fab-head">
                            <span class="t">🛠️ Costo de Fabricación</span>
                            <span><?php echo htmlspecialchars($dem_ems ?: 'EMS'); ?></span>
                        </div>
                        <div class="fab-grid">
                            <div><strong><?php echo $lbl_fab; ?>:</strong>
                                <?php echo htmlspecialchars($ems['dato_fabricacion'] ?: '—'); ?></div>
                            <div class="fab-total">Total: <?php echo htmlspecialchars($ems['costo_fabricacion']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="agent-row">
                    <div class="agent-col">
                        <div class="agent-val"><?php echo htmlspecialchars($ems['agente_nombre']); ?></div>
                        <div class="agent-lbl">AGENTE DE VENTAS</div>
                    </div>
                    <div class="agent-col">
                        <div class="agent-val"><?php echo htmlspecialchars($ems['agente_telefono']); ?></div>
                        <div class="agent-lbl">TELÉFONO</div>
                    </div>
                </div>
            </div>
            <footer>
                <div>
                    <p>BLVD. INDEPENDENCIA ESQ. CALZ. COLÓN</p>
                    <p>TORREÓN, COAHUILA</p>
                </div>
                <div>
                    <p><a href="https://energymultipliersystem.com/">energymultipliersystem.com</a></p>
                </div>
            </footer>
        </section>
    <?php endif; ?>

    <?php if ($recibo && $recibo['archivo_tipo'] === 'pdf'): ?>
        <script>
            (async function renderReciboPDF() {
                const btnPrint = document.querySelector('.btn-print');
                const loading = document.getElementById('pdf-loading');
                const container = document.getElementById('recibo-container');

                if (btnPrint) {
                    btnPrint.disabled = true;
                    btnPrint.innerText = '⏳ Cargando recibo...';
                }

                try {
                    const url = <?php echo json_encode($recibo['archivo_ruta']); ?>;
                    const pdf = await pdfjsLib.getDocument(url).promise;
                    const page = await pdf.getPage(1);
                    const viewport = page.getViewport({
                        scale: 2.0
                    });

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    await page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    }).promise;

                    const img = document.createElement('img');
                    img.src = canvas.toDataURL('image/png');
                    img.alt = 'Recibo CFE';
                    img.onload = () => {
                        if (loading) loading.style.display = 'none';
                        container.appendChild(img);
                        if (btnPrint) {
                            btnPrint.disabled = false;
                            btnPrint.innerText = '🖨️ Imprimir / Guardar PDF';
                        }
                    };
                } catch (err) {
                    if (loading) {
                        loading.innerText = 'No se pudo cargar el PDF del recibo. Verifica que el archivo exista en cotizador/recibos/.';
                        loading.style.color = '#dc2626';
                    }
                    if (btnPrint) {
                        btnPrint.disabled = false;
                        btnPrint.innerText = '🖨️ Imprimir / Guardar PDF';
                    }
                    console.error(err);
                }
            })();
        </script>
    <?php endif; ?>

</body>

</html>