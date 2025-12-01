<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar si es administrador
$admin_email = 'nueva123@zenzo.com'; // ← CAMBIA POR TU EMAIL DE ADMIN
try {
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario || $usuario['email'] !== $admin_email) {
        die('Acceso denegado');
    }
} catch (PDOException $e) {
    die("Error de autenticación");
}

// Manejar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado'])) {
    $id = intval($_POST['pedido_id']);
    $estado = $_POST['estado'];
    
    // Validar estado
    $estados_validos = ['pendiente', 'completado', 'cancelado', 'enviado'];
    if (!in_array($estado, $estados_validos)) {
        die('Estado no válido');
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        
        // Redirigir para evitar reenvío
        header("Location: pedidos.php?id=" . $id);
        exit;
    } catch (PDOException $e) {
        die("Error al actualizar el estado: " . $e->getMessage());
    }
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die('Pedido no especificado');
}

try {
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
        
        /* Estilos para los botones de estado */
        .estado-section {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 25px;
        }
        .estado-title {
            margin-bottom: 20px;
            color: var(--dark);
        }
        .estado-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .estado-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-completado {
            background: var(--success);
            color: white;
        }
        .btn-completado:hover {
            background: #0da16d;
            transform: translateY(-2px);
        }
        .btn-cancelado {
            background: var(--danger);
            color: white;
        }
        .btn-cancelado:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        .btn-enviado {
            background: var(--accent);
            color: white;
        }
        .btn-enviado:hover {
            background: #e08a0a;
            transform: translateY(-2px);
        }
        .btn-pendiente {
            background: var(--gray);
            color: white;
        }
        .btn-pendiente:hover {
            background: #7d8ca0;
            transform: translateY(-2px);
        }
        
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
        
        /* Estado actual destacado */
        .estado-actual {
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            text-align: center;
            margin: 15px 0;
        }
        .estado-pendiente { background: #e2e8f0; color: #475569; }
        .estado-completado { background: #d1fae5; color: #065f46; }
        .estado-cancelado { background: #fee2e2; color: #b91c1c; }
        .estado-enviado { background: #ffedd5; color: #c2410c; }
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
                <span class="label">Dirección de Envío:</span>
                <span class="value"><?= htmlspecialchars($pedido['direccion_envio']) ?></span>
            </div>
            <div class="detalle-row">
                <span class="label">Total:</span>
                <span class="value">$<?= number_format($pedido['total'], 2) ?></span>
            </div>
            
            <!-- Estado actual destacado -->
            <div class="detalle-row">
                <span class="label">Estado Actual:</span>
                <span class="estado-actual estado-<?= $pedido['estado'] ?>">
                    <?= ucfirst(htmlspecialchars($pedido['estado'])) ?>
                </span>
            </div>
        </div>

        <!-- Sección de gestión de estado -->
        <div class="estado-section">
            <h3 class="estado-title"><i class="fas fa-sync-alt"></i> Actualizar Estado</h3>
            <form method="POST">
                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                <div class="estado-buttons">
                    <?php if ($pedido['estado'] !== 'completado'): ?>
                        <button type="submit" name="estado" value="completado" class="estado-btn btn-completado">
                            <i class="fas fa-check"></i> Completar Pedido
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($pedido['estado'] !== 'cancelado'): ?>
                        <button type="submit" name="estado" value="cancelado" class="estado-btn btn-cancelado"
                                onclick="return confirm('¿Seguro que deseas cancelar este pedido?');">
                            <i class="fas fa-times"></i> Cancelar Pedido
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($pedido['estado'] !== 'enviado'): ?>
                        <button type="submit" name="estado" value="enviado" class="estado-btn btn-enviado">
                            <i class="fas fa-truck"></i> Marcar como Enviado
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($pedido['estado'] !== 'pendiente'): ?>
                        <button type="submit" name="estado" value="pendiente" class="estado-btn btn-pendiente">
                            <i class="fas fa-clock"></i> Volver a Pendiente
                        </button>
                    <?php endif; ?>
                </div>
            </form>
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