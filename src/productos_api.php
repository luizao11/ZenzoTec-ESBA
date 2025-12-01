<?php
header('Content-Type: application/json; charset=utf-8');
//obtner producto 
try {
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id, nombre, descripcion, precio, stock, imagen FROM productos WHERE stock > 0 ORDER BY id DESC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear para el frontend
    $resultado = [];
    foreach ($productos as $p) {
        $resultado[] = [
            'id' => (int)$p['id'],
            'name' => htmlspecialchars($p['nombre']),
            'description' => htmlspecialchars($p['descripcion']),
            'price' => (float)$p['precio'],
            'stock' => (int)$p['stock'],
            'image' => $p['imagen'] 
        ];
    }

    echo json_encode($resultado);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar productos']);
}
?>