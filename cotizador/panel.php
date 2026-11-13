<?php
session_start();

$usuario_correcto = "admin";
$password_correcto = "Energy2026";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = $_POST['usuario'];
    $password_ingresada = $_POST['password'];

    if ($usuario_ingresado === $usuario_correcto && $password_ingresada === $password_correcto) {
        $_SESSION['logeado'] = true;
        header("Location: panel.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso | Evoenergy</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #1a202c;
                height: 100vh;
                margin: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-box {
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
                width: 100%;
                max-width: 350px;
                text-align: center;
            }

            .login-box h2 {
                margin-top: 0;
                color: #1a202c;
                font-weight: 800;
            }

            .login-box span {
                color: #9bb556;
            }

            .input-group {
                margin-bottom: 20px;
                text-align: left;
            }

            .input-group label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                margin-bottom: 5px;
                color: #4a5568;
            }

            .input-group input {
                width: 100%;
                padding: 10px;
                border: 1px solid #cbd5e0;
                border-radius: 4px;
                outline: none;
                box-sizing: border-box;
                font-family: inherit;
            }

            .input-group input:focus {
                border-color: #9bb556;
            }

            .btn-login {
                background-color: #9bb556;
                color: #000;
                border: none;
                width: 100%;
                padding: 12px;
                border-radius: 4px;
                font-weight: 700;
                cursor: pointer;
                transition: 0.2s;
                font-size: 15px;
            }

            .btn-login:hover {
                background-color: #8aa34a;
            }

            .error-msg {
                color: #e53e3e;
                font-size: 13px;
                margin-bottom: 15px;
                font-weight: 600;
            }
        </style>
    </head>

    <body>
        <div class="login-box">
            <h2>EMS<span>⚡</span> Admin</h2>
            <?php if ($error != "") echo "<div class='error-msg'>$error</div>"; ?>
            <form method="POST" action="">
                <div class="input-group"><label>Usuario</label><input type="text" name="usuario" required autocomplete="off"></div>
                <div class="input-group"><label>Contraseña</label><input type="password" name="password" required></div>
                <button type="submit" class="btn-login">Entrar</button>
            </form>
        </div>
    </body>

    </html>
<?php
    exit;
}

include 'conexion.php';

// --- LÓGICA PARA ELIMINAR UN REGISTRO ---
if (isset($_GET['eliminar'])) {
    $id_a_eliminar = intval($_GET['eliminar']);
    $sql_delete = "DELETE FROM cotizaciones WHERE id = $id_a_eliminar";
    $conn->query($sql_delete);
    // Recargar la página limpia después de borrar
    header("Location: panel.php");
    exit;
}
// ----------------------------------------

$sql = "SELECT * FROM cotizaciones ORDER BY fecha DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | Evoenergy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #e5e7eb;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #9bb556;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-left h1 {
            margin: 0;
            color: #1a202c;
            font-size: 24px;
        }

        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-nueva {
            background-color: #1a202c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: 600;
            border-radius: 5px;
            transition: 0.2s;
            font-size: 14px;
        }

        .btn-nueva:hover {
            background-color: #2d3748;
        }

        .btn-salir {
            color: #e53e3e;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-salir:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f3f4f6;
            color: #4a5568;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 15px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1a202c;
            font-size: 14px;
        }

        tr:hover {
            background-color: #f7fafc;
        }

        .folio-badge {
            background-color: #000;
            color: #fbbd30;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .monto {
            font-weight: 700;
            color: #9bb556;
            font-size: 16px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #718096;
        }

        /* Estilos de botones de acción */
        .acciones-container {
            display: flex;
            gap: 8px;
        }

        .btn-ver-pdf {
            background-color: #1a202c;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            transition: 0.2s;
        }

        .btn-ver-pdf:hover {
            background-color: #2d3748;
        }

        .btn-eliminar {
            background-color: #e53e3e;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            transition: 0.2s;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-eliminar:hover {
            background-color: #c53030;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div class="header-left">
                <h1>Historial de Cotizaciones</h1>
            </div>
            <div class="header-right">
                <a href="./" class="btn-nueva" target="_blank">+ Nueva Cotización</a>
                <a href="panel.php?logout=true" class="btn-salir">Cerrar Sesión</a>
            </div>
        </header>

        <table>
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha y Hora</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Monto Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $fecha_formateada = date("d/m/Y h:i A", strtotime($row["fecha"]));

                        echo "<tr>";
                        echo "<td><span class='folio-badge'>" . htmlspecialchars($row["folio"]) . "</span></td>";
                        echo "<td>" . $fecha_formateada . "</td>";
                        echo "<td><strong>" . htmlspecialchars($row["cliente"]) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($row["agente_nombre"]) . "</td>";
                        echo "<td class='monto'>" . htmlspecialchars($row["total"]) . "</td>";

                        // Contenedor de botones (Ver PDF y Eliminar)
                        echo "<td>
                                <div class='acciones-container'>
                                    <a href='ver_pdf.php?id=" . $row["id"] . "' class='btn-ver-pdf' target='_blank'>Ver PDF</a>
                                    <a href='panel.php?eliminar=" . $row["id"] . "' class='btn-eliminar' onclick='return confirm(\"¿Estás seguro de que deseas eliminar la cotización folio " . htmlspecialchars($row["folio"]) . "? Esta acción no se puede deshacer.\");'>Eliminar</a>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='empty-state'>Aún no hay cotizaciones registradas.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php $conn->close(); ?>