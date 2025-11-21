<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$stock = intval($input['stock'] ?? -1);

if ($id <= 0 || $stock < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE productos SET stock = ? WHERE id = ?");
    $result = $stmt->execute([$stock, $id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No se pudo actualizar']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos']);
}
?>