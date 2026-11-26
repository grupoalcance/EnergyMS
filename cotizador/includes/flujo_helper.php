<?php
if (!function_exists('obtenerClaveExpediente')) {
    function obtenerClaveExpediente($cliente, $servicio)
    {
        $serv_limpio = preg_replace('/[^0-9]/', '', (string)$servicio);
        if (strlen($serv_limpio) >= 6) {
            return 'srv_' . $serv_limpio;
        }
        $nom_limpio = strtolower(trim(preg_replace('/\s+/', ' ', (string)$cliente)));
        return 'cli_' . md5($nom_limpio ?: 'sin_nombre');
    }
}

if (!function_exists('avanzarEstatusExpediente')) {
    function avanzarEstatusExpediente($conn, $cliente, $servicio, $nuevo_estatus)
    {
        $clave = $conn->real_escape_string(obtenerClaveExpediente($cliente, $servicio));
        $nuevo_esc = $conn->real_escape_string($nuevo_estatus);

        $orden = [
            'Prospecto'             => 1,
            'Pendiente Solar'       => 2,
            'Pendiente EMS'         => 3,
            'Cotización Completa'   => 4,
            'Enviada al Cliente'    => 5,
            'Anticipo 60% Pagado'   => 6,
            'En Fabricación'        => 7,
            'Instalado / Liquidado' => 8,
            'Descartado'            => 99
        ];

        $res = @$conn->query("SELECT estatus FROM expedientes_seguimiento WHERE clave_exp = '$clave' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $actual = $row['estatus'] ?? 'Pendiente Solar';
            $nivel_actual = $orden[$actual] ?? 1;
            $nivel_nuevo  = $orden[$nuevo_estatus] ?? 1;

            // Solo avanza si el expediente estaba en una etapa anterior
            if ($nivel_nuevo > $nivel_actual && $actual !== 'Descartado') {
                @$conn->query("UPDATE expedientes_seguimiento SET estatus = '$nuevo_esc' WHERE clave_exp = '$clave'");
            }
        } else {
            @$conn->query("INSERT INTO expedientes_seguimiento (clave_exp, estatus) VALUES ('$clave', '$nuevo_esc')");
        }
    }
}
