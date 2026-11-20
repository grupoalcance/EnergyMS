<?php
if (!isset($conn)) {
    header("Location: ../dashboard.php?seccion=historial");
    exit;
}
?>

<h1 class="section-heading">Historial de Documentos</h1>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0; font-size:18px; color: var(--text-main);">Documentos Guardados</h2>
    </div>
    <div class="table-container">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Cliente / Agente</th>
                    <th>Tipo de Documento</th>
                    <th>Monto / Rango</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $docs = [];
                $f_n = function ($v) {
                    $n = floatval(preg_replace('/[^0-9.-]/', '', $v));
                    return number_format($n, 2, '.', ',');
                };

                // 1. Cotizaciones EMS
                $re = $conn->query("SELECT * FROM cotizaciones ORDER BY fecha DESC");
                if ($re && $re->num_rows > 0) {
                    while ($r = $re->fetch_assoc()) {
                        $e = floatval(preg_replace('/[^0-9.-]/', '', $r["energia_cfe"]));
                        $c = floatval(preg_replace('/[^0-9.-]/', '', $r["cargo_cfe"]));
                        $ic = ($e + $c) * 0.16;
                        $docs[] = [
                            'tipo' => 'ems',
                            'id' => $r['id'],
                            'f' => $r['folio'],
                            'fecha' => $r['fecha'],
                            'c' => $r['cliente'],
                            'a' => $r['agente_nombre'],
                            'm' => $r['total'],
                            'p' => [
                                'tipo' => 'ems',
                                'id' => $r['id'],
                                'folio' => $r['folio'],
                                'cliente' => $r['cliente'],
                                'direccion' => $r['direccion'],
                                'servicio' => $r['servicio_cfe'],
                                'energia' => $f_n($r['energia_cfe']),
                                'cargo' => $f_n($r['cargo_cfe']),
                                'iva_cfe' => number_format($ic, 2, '.', ','),
                                'total_cfe' => $f_n($r['total_cfe']),
                                'subtotal' => $f_n($r['subtotal']),
                                'iva' => $f_n($r['iva']),
                                'total' => $f_n($r['total']),
                                'anticipo' => $f_n($r['anticipo']),
                                'restante' => $f_n($r['restante']),
                                'agente' => $r['agente_nombre'],
                                'telefono' => $r['agente_telefono']
                            ]
                        ];
                    }
                }

                // 2. Cotizaciones Paneles Solares
                $rs = @$conn->query("SELECT * FROM cotizaciones_solares ORDER BY fecha DESC");
                if ($rs && $rs->num_rows > 0) {
                    while ($r = $rs->fetch_assoc()) {
                        $docs[] = [
                            'tipo' => 'solar',
                            'id' => $r['id'],
                            'f' => $r['folio'],
                            'fecha' => $r['fecha'],
                            'c' => $r['cliente'],
                            'a' => $r['agente_nombre'],
                            'm' => $r['inversion_rango'],
                            'p' => [
                                'tipo' => 'solar',
                                'id' => $r['id'],
                                'folio' => $r['folio'],
                                'cliente' => $r['cliente'],
                                'servicio' => $r['servicio_cfe'],
                                'paneles' => $r['paneles'],
                                'watts_panel' => $r['watts_panel'],
                                'potencia_kw' => $r['potencia_kw'],
                                'inversion_rango' => $r['inversion_rango'],
                                'agente' => $r['agente_nombre'],
                                'telefono' => $r['agente_telefono']
                            ]
                        ];
                    }
                }

                // 3. Recibos CFE
                $rr = @$conn->query("SELECT * FROM recibos_cfe ORDER BY fecha DESC");
                if ($rr && $rr->num_rows > 0) {
                    while ($r = $rr->fetch_assoc()) {
                        $docs[] = [
                            'tipo' => 'recibo',
                            'id' => $r['id'],
                            'f' => $r['folio'],
                            'fecha' => $r['fecha'],
                            'c' => $r['cliente'],
                            'a' => $r['agente_nombre'],
                            'm' => '—',
                            'p' => [
                                'tipo' => 'recibo',
                                'id' => $r['id'],
                                'folio' => $r['folio'],
                                'cliente' => $r['cliente'],
                                'direccion' => $r['direccion'],
                                'servicio' => $r['servicio_cfe'],
                                'monto' => '—',
                                'agente' => $r['agente_nombre'],
                                'archivo_ruta' => $r['archivo_ruta'],
                                'archivo_tipo' => $r['archivo_tipo']
                            ]
                        ];
                    }
                }

                // Ordenar todos del más reciente al más antiguo
                usort($docs, function ($a, $b) {
                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                });

                if (count($docs) > 0) {
                    foreach ($docs as $d) {
                        $f_fmt = date("d/m/Y h:i A", strtotime($d["fecha"]));
                        $dp = htmlspecialchars(json_encode($d['p']), ENT_QUOTES, 'UTF-8');

                        echo "<tr id='row-" . $d['tipo'] . "-" . $d['id'] . "'>";
                        echo "<td><strong>" . htmlspecialchars($d["f"]) . "</strong></td>";
                        echo "<td style='color: var(--text-muted); font-size: 13px;'>" . $f_fmt . "</td>";
                        echo "<td>
                                <strong class='row-cliente'>" . htmlspecialchars($d["c"]) . "</strong><br>
                                <span class='row-agente' style='color: var(--text-muted); font-size: 12px;'>" . htmlspecialchars($d["a"]) . "</span>
                              </td>";

                        if ($d['tipo'] === 'ems') {
                            echo "<td><span class='badge ems'>Cotización EMS</span></td>";
                            echo "<td><strong class='row-total' style='color: var(--brand-green); font-size: 15px;'>" . htmlspecialchars($d["m"]) . "</strong></td>";
                            $del = "dashboard.php?seccion=historial&eliminar=" . $d["id"];
                        } elseif ($d['tipo'] === 'solar') {
                            echo "<td><span class='badge solar'>Cotización Solar</span></td>";
                            echo "<td><strong class='row-total' style='color: #111827; font-size: 14px;'>" . htmlspecialchars($d["m"]) . "</strong></td>";
                            $del = "dashboard.php?seccion=historial&eliminar_solar=" . $d["id"];
                        } else {
                            echo "<td><span class='badge recibo'>Recibo CFE</span></td>";
                            echo "<td><strong style='color: var(--text-main); font-size: 14px;'>—</strong></td>";
                            $del = "dashboard.php?seccion=historial&eliminar_recibo=" . $d["id"];
                        }

                        echo "<td>
                                <div class='action-btns'>
                                    <button type='button' id='btn-view-" . $d['tipo'] . "-" . $d['id'] . "' class='btn-action btn-view' onclick='abrirModal(this)' data-doc='" . $dp . "'>Ver</button>
                                    <a href='" . $del . "' class='btn-action btn-delete' onclick='return confirm(\"¿Seguro que deseas eliminar el documento " . htmlspecialchars($d["f"]) . "?\");'>Borrar</a>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding:40px; color: var(--text-muted);'>No hay documentos registrados aún.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>