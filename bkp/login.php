<?php
session_start();

// 🔑 EMAIL DEL ADMINISTRADOR - CAMBIA ESTO POR TU EMAIL REAL
$admin_email = 'nueva123@zenzo.com';

// Si ya está logueado, redirigir según su rol
if (isset($_SESSION['usuario_id'])) {
    try {
        $pdo = new PDO(
            "mysql:host=db;dbname=zenzotec_db;charset=utf8mb4",
            "zenzotec_user",
            "userpass123",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();
        
        if ($usuario && $usuario['email'] === $admin_email) {
            header('Location: admin/index.php');
        } else {
            header('Location: index.html');
        }
        exit;
    } catch (PDOException $e) {
        header('Location: index.html');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=db;dbname=zenzotec_db;charset=utf8mb4",
                "zenzotec_user",
                "userpass123",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $stmt = $pdo->prepare("SELECT id, nombre, apellido, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['usuario_email'] = $email;

                // 🔑 Redirección por rol
                if ($email === $admin_email) {
                    header('Location: admin/index.php');
                } else {
                    if (isset($_SESSION['checkout_pending']) && $_SESSION['checkout_pending']) {
                        unset($_SESSION['checkout_pending']);
                        header('Location: checkout.html');
                    } else {
                        header('Location: index.html');
                    }
                }
                exit;
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error interno. Inténtalo más tarde.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - ZenzoTec</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/login.css"> <!-- ✅ Nuevo archivo CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><h1><i class="fas fa-microchip"></i> ZenzoTec</h1></div>
            <nav><ul><li><a href="index.html">Inicio</a></li></ul></nav>
        </div>
    </header>

    <main class="login-container">
        <div class="login-box">
            <h2><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <button type="submit" class="btn-login">Ingresar</button>
            </form>
            <div class="login-footer">
                <p>¿No tienes cuenta? <a href="register.html">Regístrate</a></p>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section"><h4>ZenzoTec</h4><p>Tu tienda de tecnología.</p></div>
                <div class="footer-section"><p><i class="fas fa-envelope"></i> info@zenzotec.com</p></div>
            </div>
            <div class="footer-bottom"><p>&copy; 2025 ZenzoTec</p></div>
        </div>
    </footer>
</body>
</html>