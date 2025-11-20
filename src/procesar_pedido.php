<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Debes iniciar sesión para realizar una compra']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$productos = $input['productos'] ?? [];
$total = floatval($input['total'] ?? 0);

if (empty($productos) || $total <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Carrito vacío o total inválido']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=db;dbname=zenzotec_db;charset=utf8mb4",
        "zenzotec_user",
        "userpass123",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $pdo->beginTransaction();

    // 1. Generar número de pedido único
    $numero_pedido = 'PED-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // 2. Crear pedido
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (usuario_id, numero_pedido, total) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$_SESSION['usuario_id'], $numero_pedido, $total]);
    $pedido_id = $pdo->lastInsertId();

    // 3. Insertar productos y verificar stock
    foreach ($productos as $item) {
        $producto_id = intval($item['id']);
        $cantidad = intval($item['quantity']);
        $precio = floatval($item['price']);

        if ($cantidad <= 0) continue;

        // Verificar stock disponible
        $stmt = $pdo->prepare("SELECT stock, nombre FROM productos WHERE id = ?");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch();

        if (!$producto) {
            throw new Exception("Producto no encontrado: ID $producto_id");
        }

        if ($producto['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para: {$producto['nombre']}");
        }

        // Insertar en detalle
        $stmt = $pdo->prepare("
            INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, precio_unitario)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$pedido_id, $producto_id, $cantidad, $precio]);

        // Reducir stock
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$cantidad, $producto_id]);
    }

    $pdo->commit();

    // Limpiar carrito en frontend (no es necesario en backend)
    echo json_encode([
        'success' => true,
        'numero_pedido' => $numero_pedido,
        'mensaje' => '¡Compra realizada con éxito!'
    ]);

} catch (Exception $e) {
    $pdo->rollback();
    error_log("Error en pedido: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo completar la compra. ' . $e->getMessage()]);
}
?>
