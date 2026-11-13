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

        .campo-fijo {
            width: 100%;
            border-bottom: 2px solid var(--yellow-line);
            padding: 5px 0;
            font-weight: 600;
            text-transform: uppercase;
        }

        .campo-fijo-tabla {
            width: 100%;
            text-align: center;
            font-weight: 600;
            padding: 10px;
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

        .split-section {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 40px;
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
            padding-left: 10px;
            width: 40%;
        }

        .table-mini td.value {
            width: 60%;
            text-align: center;
            font-weight: 700;
            color: #7b291d;
        }

        .left-box,
        .right-box {
            width: 45%;
        }

        .cfe-title {
            font-weight: 800;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .finance-value {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
            color: #000 !important;
        }

        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
        }

        .payment-conditions {
            font-size: 12px;
        }

        .black-badge {
            background-color: #000;
            color: #fff;
            padding: 8px 15px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .payment-line {
            margin-bottom: 5px;
        }

        .payment-line strong {
            font-weight: 800;
        }

        .agent-info {
            width: 250px;
        }

        .agent-row {
            display: flex;
            margin-bottom: -1px;
        }

        .agent-label {
            background-color: #000;
            color: #fff;
            font-size: 10px;
            padding: 8px;
            width: 130px;
            font-weight: 700;
            display: flex;
            align-items: center;
            border: 1px solid #000;
        }

        .agent-input {
            flex-grow: 1;
            border: 1px solid #000;
            padding: 5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            text-transform: uppercase;
        }

        footer {
            background-color: #000;
            color: #fff;
            padding: 20px 40px;
            font-size: 11px;
            font-weight: 600;
        }

        footer p {
            margin: 5px 0;
            text-align: left;
            margin-left: 50px;
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

        /* Botón elegante para regresar al Panel */
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
    <button class="no-print print-btn" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    <a href="panel.php" class="no-print panel-btn">Volver al Panel</a>
    <div class="page">
        <header>
            <div class="logo-container">
                <img src="/img/energy.png" alt="EMS Energy Multiplier System" class="logo-ems" />
            </div>
            <div class="folio-container">
                <div class="folio-title">Cotización</div>
                <div class="folio-number"><?php echo htmlspecialchars($cotizacion['folio']); ?></div>
            </div>
        </header>

        <div class="content">
            <div class="customer-info">
                <label>NOMBRE:</label>
                <div class="campo-fijo"><?php echo htmlspecialchars($cotizacion['cliente']); ?></div>

                <label>DIRECCIÓN:</label>
                <div class="campo-fijo"><?php echo htmlspecialchars($cotizacion['direccion']); ?></div>

                <label>NO. DE SERVICIO:</label>
                <div class="campo-fijo"><?php echo htmlspecialchars($cotizacion['servicio_cfe']); ?></div>
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
                        <td>
                            <div class="campo-fijo-tabla">1</div>
                        </td>
                        <td>
                            <div class="campo-fijo-tabla">Aparato evoenergy</div>
                        </td>
                        <td>
                            <div class="campo-fijo-tabla">5 años</div>
                        </td>
                        <td>
                            <div class="campo-fijo-tabla">
                                <?php echo (trim($cotizacion['servicio_cfe']) != "") ? "Servicio " . htmlspecialchars($cotizacion['servicio_cfe']) : ""; ?>
                            </div>
                        </td>
                        <td>
                            <div class="campo-fijo-tabla">10 a 15 años</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="split-section">
                <div class="left-box">
                    <div class="cfe-title">Una vez colocado se espera un pago a CFE de</div>
                    <table class="table-mini">
                        <tr>
                            <td class="label">Energía:</td>
                            <td class="value"><?php echo htmlspecialchars($cotizacion['energia_cfe']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Cargo fijo:</td>
                            <td class="value"><?php echo htmlspecialchars($cotizacion['cargo_cfe']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Total a pagar:</td>
                            <td class="value" style="color: #7b291d;"><?php echo htmlspecialchars($cotizacion['total_cfe']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="right-box" style="margin-top: 20px">
                    <table class="table-mini">
                        <tr>
                            <td class="label">Subtotal:</td>
                            <td class="value finance-value"><?php echo htmlspecialchars($cotizacion['subtotal']); ?></td>
                        </tr>
                        <tr>
                            <td class="label">IVA:</td>
                            <td class="value finance-value"><span><?php echo htmlspecialchars($cotizacion['iva']); ?></span></td>
                        </tr>
                        <tr>
                            <td class="label">Total:</td>
                            <td class="value finance-value"><span><?php echo htmlspecialchars($cotizacion['total']); ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="bottom-section">
                <div class="payment-conditions">
                    <div class="black-badge">Condiciones de Pago</div>
                    <div class="payment-line">
                        Anticipo 60%: <strong><?php echo htmlspecialchars($cotizacion['anticipo']); ?></strong>
                    </div>
                    <div class="payment-line">
                        Una vez colocado,<br />el restante: <strong>40% <span><?php echo htmlspecialchars($cotizacion['restante']); ?></span></strong>
                    </div>
                </div>

                <div class="agent-info">
                    <div class="agent-row">
                        <div class="agent-label">Nombre del agente de venta:</div>
                        <div class="agent-input"><?php echo htmlspecialchars($cotizacion['agente_nombre']); ?></div>
                    </div>
                    <div class="agent-row">
                        <div class="agent-label">Teléfono del agente:</div>
                        <div class="agent-input"><?php echo htmlspecialchars($cotizacion['agente_telefono']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <p>(871) 417 8277</p>
            <p>BLVD. INDEPENDENCIA ESQ. CALZ. COLÓN</p>
        </footer>
    </div>
</body>


</html>