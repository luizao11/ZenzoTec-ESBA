<?php
// register.php - Registro de usuarios con redirección

// Obtener datos del formulario
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$direccion = $_POST['direccion'] ?? '';

// Validar campos obligatorios
if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($direccion)) {
    header('Location: register.html?error=missing_fields');
    exit;
}

// Validar contraseña
if (strlen($password) < 6) {
    header('Location: register.html?error=short_password');
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.html?error=invalid_email');
    exit;
}

try {
    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: register.html?error=email_exists');
        exit;
    }

    // Hashear contraseña y registrar
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, direccion) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $apellido, $email, $hashedPassword, $direccion]);

    // Redirigir a login con mensaje de éxito
    header('Location: index.html?success=registered');
    exit;

} catch (PDOException $e) {
    // Registrar error en logs (opcional)
    error_log("Error en registro: " . $e->getMessage());
    header('Location: register.html?error=db');
    exit;
}
?>
