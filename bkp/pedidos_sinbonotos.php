<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die('Pedido no especificado');
}

try {
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener pedido
    $stmt = $pdo->prepare("
        SELECT p.*, CONCAT(u.nombre, ' ', u.apellido) as usuario, u.email
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        die('Pedido no encontrado');
    }

    // Obtener productos del pedido
    $stmt = $pdo->prepare("
        SELECT pp.*, pr.nombre as producto_nombre
        FROM pedido_productos pp
        JOIN productos pr ON pp.producto_id = pr.id
        WHERE pp.pedido_id = ?
    ");
    $stmt->execute([$id]);
    $productos = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?> - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detalle-container { padding: 20px; max-width: 800px; margin: 0 auto; }
        .detalle-card { background: white; border-radius: 12px; box-shadow: var(--shadow); padding: 25px; margin-bottom: 25px; }
        .detalle-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .label { font-weight: 600; color: var(--dark); }
        .value { color: var(--gray); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #F8FAFC; }
        .btn-back {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn-back:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><i class="fas fa-microchip"></i> ZenzoTec Admin</h1>
            </div>
            <nav><ul><li><a href="index.php">Volver al Panel</a></li></ul></nav>
        </div>
    </header>

    <main class="detalle-container">
        <div class="detalle-card">
            <h2><i class="fas fa-receipt"></i> Detalle del Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?></h2>
            
            <div class="detalle-row">
                <span class="label">Cliente:</span>
                <span class="value"><?= htmlspecialchars($pedido['usuario']) ?> (<?= htmlspecialchars($pedido['email']) ?>)</span>
            </div>
            <div class="detalle-row">
                <span class="label">Fecha:</span>
                <span class="value"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></span>
            </div>
            <div class="detalle-row">
                <span class="label">Estado:</span>
                <span class="value"><?= ucfirst(htmlspecialchars($pedido['estado'])) ?></span>
            </div>
            <div class="detalle-row">
                <span class="label">Dirección de Envío:</span>
                <span class="value"><?= htmlspecialchars($pedido['direccion_envio']) ?></span>
            </div>
            <div class="detalle-row">
                <span class="label">Total:</span>
                <span class="value">$<?= number_format($pedido['total'], 2) ?></span>
            </div>
        </div>

        <div class="detalle-card">
            <h3><i class="fas fa-box"></i> Productos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['producto_nombre']) ?></td>
                        <td><?= $p['cantidad'] ?></td>
                        <td>$<?= number_format($p['precio_unitario'], 2) ?></td>
                        <td>$<?= number_format($p['cantidad'] * $p['precio_unitario'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver al Panel
        </a>
    </main>
</body>
</html>