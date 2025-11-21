<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Verificar si hay sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401); // No autorizado
    echo json_encode(['error' => 'No hay sesión activa']);
    exit;
}

try {
    // Conexión a la base de datos
    $pdo = new PDO(
        "mysql:host=db;dbname=zenzotec_db;charset=utf8mb4",
        "zenzotec_user",
        "userpass123",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Obtener datos del usuario
    $stmt = $pdo->prepare("
        SELECT id, nombre, apellido, email, direccion 
        FROM usuarios 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        // Sesión inválida (usuario eliminado)
        session_destroy();
        http_response_code(401);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }

    // Devolver datos del perfil
    echo json_encode([
        'success' => true,
        'id' => (int)$usuario['id'],
        'nombre' => htmlspecialchars($usuario['nombre']),
        'apellido' => htmlspecialchars($usuario['apellido']),
        'email' => htmlspecialchars($usuario['email']),
        'direccion' => htmlspecialchars($usuario['direccion'])
    ]);

} catch (PDOException $e) {
    error_log("Error en obtener_perfil.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>