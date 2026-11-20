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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #000000;
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
        <h2>EMS<span>⚡</span></h2>
        <?php if ($error != "")
            echo "<div class='error-msg'>$error</div>"; ?>
        <form method="POST" action="">
            <div class="input-group"><label>Usuario</label><input type="text" name="usuario" required
                    autocomplete="off"></div>
            <div class="input-group"><label>Contraseña</label><input type="password" name="password" required></div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>
</body>

</html>