<?php
session_start();

$usuario_correcto = "admin";
$password_correcto = "Energy2026";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = $_POST['usuario'] ?? '';
    $password_ingresada = $_POST['password'] ?? '';

    if ($usuario_ingresado === $usuario_correcto && $password_ingresada === $password_correcto) {
        $_SESSION['logeado'] = true;
        header("Location: dashboard");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}

// Si ya inició sesión, redirigir automáticamente al nuevo dashboard
if (isset($_SESSION['logeado']) && $_SESSION['logeado'] === true) {
    header("Location: dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | EMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #000000;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-box {
            background-color: #9bb556;
            padding: 40px 36px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
            width: 100%;
            max-width: 360px;
            text-align: center;
            box-sizing: border-box;
        }

        .login-logo {
            max-width: 175px;
            height: auto;
            margin-bottom: 24px;
            display: inline-block;
        }

        .input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #111827;
        }

        .input-group input {
            width: 100%;
            padding: 11px 12px;
            background-color: #ffffff;
            border: 1px solid #111827;
            border-radius: 6px;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 14px;
            color: #111827;
        }

        .input-group input:focus {
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.35);
        }

        .btn-login {
            background-color: #000000;
            color: #ffffff;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 15px;
            font-family: inherit;
            margin-top: 4px;
        }

        .btn-login:hover {
            background-color: #1f2937;
        }

        .error-msg {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 16px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <img src="../img/energyblack.jpeg" alt="EMS Logo" class="login-logo">
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