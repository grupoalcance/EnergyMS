<?php
session_start();

$usuario_correcto = "admin";
$password_correcto = "Energy2026";

// Si es servidor local de PHP (localhost:8000), conserva ".php"; en Hostinger usa URL limpia
$ext = (php_sapi_name() === 'cli-server') ? '.php' : '';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel" . $ext);
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = $_POST['usuario'] ?? '';
    $password_ingresada = $_POST['password'] ?? '';

    if ($usuario_ingresado === $usuario_correcto && $password_ingresada === $password_correcto) {
        $_SESSION['logeado'] = true;
        header("Location: dashboard" . $ext);
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}

if (isset($_SESSION['logeado']) && $_SESSION['logeado'] === true) {
    header("Location: dashboard" . $ext);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | EMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #050505;
            background-image: radial-gradient(circle at 50% 0%, rgba(155, 181, 86, 0.14) 0%, transparent 55%);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            background-color: #121417;
            border: 1px solid #26292e;
            border-top: 4px solid #9bb556;
            padding: 42px 36px 38px;
            border-radius: 14px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.85);
            width: 100%;
            max-width: 370px;
            text-align: center;
        }

        .login-logo {
            max-width: 175px;
            height: auto;
            margin-bottom: 10px;
            display: inline-block;
        }

        .login-subtitle {
            color: #9ca3af;
            font-size: 13px;
            margin: 0 0 28px 0;
            font-weight: 500;
        }

        .input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 7px;
            color: #d1d5db;
        }

        .input-group input {
            width: 100%;
            padding: 12px 14px;
            background-color: #1c1f24;
            border: 1px solid #32363e;
            border-radius: 8px;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            color: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-group input:focus {
            border-color: #9bb556;
            box-shadow: 0 0 0 3px rgba(155, 181, 86, 0.18);
        }

        .btn-login {
            background-color: #9bb556;
            color: #090a0b;
            border: none;
            width: 100%;
            padding: 13px;
            border-radius: 8px;
            font-weight: 800;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            font-size: 15px;
            font-family: inherit;
            margin-top: 8px;
        }

        .btn-login:hover {
            background-color: #acc763;
        }

        .btn-login:active {
            transform: scale(0.99);
        }

        .error-msg {
            background-color: rgba(239, 68, 68, 0.14);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <img src="../img/energy.png" alt="EMS Logo" class="login-logo">
        <p class="login-subtitle">Sistema de Cotizaciones y Recibos</p>
        <?php if ($error != "")
            echo "<div class='error-msg'>$error</div>"; ?>
        <form method="POST" action="">
            <div class="input-group">
                <label>Usuario</label>
                <input type="text" name="usuario" required autocomplete="off">
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>
</body>

</html>