<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

try {
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar producto
    if (isset($_GET['eliminar'])) {
        $id = intval($_GET['eliminar']);
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: listar_productos.php?mensaje=eliminado');
        exit;
    }

    // Obtener producto para editar
    $id = $_GET['id'] ?? null;
    $producto = null;
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([intval($id)]);
        $producto = $stmt->fetch();
        if (!$producto) {
            die('Producto no encontrado');
        }
    }

    // Procesar actualización
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $imagen = trim($_POST['imagen'] ?? '');

        if (empty($nombre)) {
            $error = 'El nombre es obligatorio.';
        } elseif ($precio <= 0) {
            $error = 'El precio debe ser mayor a 0.';
        } elseif ($stock < 0) {
            $error = 'El stock no puede ser negativo.';
        } else {
            $stmt = $pdo->prepare("
                UPDATE productos 
                SET nombre = ?, descripcion = ?, precio = ?, stock = ?, imagen = ?
                WHERE id = ?
            ");
            $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen, $id]);
            $message = 'Producto actualizado exitosamente.';
            // Recargar datos
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            $producto = $stmt->fetch();
        }
    }

} catch (PDOException $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $producto ? 'Editar Producto' : 'Nuevo Producto' ?> - ZenzoTec Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container { padding: 20px; max-width: 800px; margin: 0 auto; }
        .form-card { background: white; border-radius: 12px; box-shadow: var(--shadow); padding: 30px; }
        .form-card h2 { text-align: center; margin-bottom: 25px; color: var(--dark); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark); }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .btn-submit { width: 100%; padding: 14px; font-size: 1.1rem; font-weight: 700; border: none; border-radius: 30px; background: var(--primary); color: white; cursor: pointer; transition: var(--transition); }
        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-delete { background: var(--danger); color: white; padding: 12px; border: none; border-radius: 30px; font-weight: 700; width: 100%; margin-top: 10px; cursor: pointer; }
        .btn-delete:hover { background: #dc2626; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .message.success { background: #D1FAE5; color: #065F46; }
        .message.error { background: #FEE2E2; color: #B91C1C; }
        .back-link { display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><i class="fas fa-microchip"></i> ZenzoTec Admin</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="listar_productos.php">Productos</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <div class="form-card">
            <h2><i class="fas fa-edit"></i> <?= $producto ? 'Editar Producto' : 'Nuevo Producto' ?></h2>

            <?php if ($message): ?>
                <div class="message success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($producto): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $producto['id'] ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?= htmlspecialchars($producto['nombre']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="precio">Precio ($) *</label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0.01" required 
                                   value="<?= htmlspecialchars($producto['precio']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock Actual *</label>
                            <input type="number" id="stock" name="stock" min="0" required 
                                   value="<?= htmlspecialchars($producto['stock']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="imagen">Nombre de la imagen (opcional)</label>
                        <input type="text" id="imagen" name="imagen" 
                               placeholder="ej: laptop.jpg"
                               value="<?= htmlspecialchars($producto['imagen']) ?>">
                        <small style="color: var(--gray); display: block; margin-top: 5px;">
                            Guarda la imagen en <code>src/images/</code> y escribe solo el nombre.
                        </small>
                        <?php if (!empty($producto['imagen'])): ?>
                            <div style="margin-top: 10px;">
                                <img src="../images/<?= htmlspecialchars($producto['imagen']) ?>" 
                                     alt="Vista previa" 
                                     style="max-width: 150px; max-height: 150px; border: 1px solid #eee; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Actualizar Producto
                    </button>
                </form>

                <form method="GET" style="margin-top: 15px;" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                    <input type="hidden" name="eliminar" value="<?= $producto['id'] ?>">
                    <button type="submit" class="btn-delete">
                        <i class="fas fa-trash"></i> Eliminar Producto
                    </button>
                </form>
            <?php else: ?>
                <p>Producto no encontrado.</p>
            <?php endif; ?>

            <a href="listar_productos.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </main>
</body>
</html>