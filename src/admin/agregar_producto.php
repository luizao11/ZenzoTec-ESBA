<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $imagen = trim($_POST['imagen'] ?? '');

    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre del producto es obligatorio.';
    } elseif ($precio <= 0) {
        $error = 'El precio debe ser mayor a 0.';
    } elseif ($stock < 0) {
        $error = 'El stock no puede ser negativo.';
    } else {
        try {
            $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("
                INSERT INTO productos (nombre, descripcion, precio, stock, imagen)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen]);

            $message = 'Producto agregado exitosamente.';
            // Resetear el formulario
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Error al guardar el producto. Inténtalo más tarde.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto - ZenzoTec Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/agregar_producto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><i class="fas fa-microchip"></i> ZenzoTec Admin</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Panel</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <div class="form-card">
            <h2><i class="fas fa-plus-circle"></i> Agregar Nuevo Producto</h2>

            <?php if ($message): ?>
                <div class="message success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="precio">Precio ($) *</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0.01" required 
                               value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock Inicial *</label>
                        <input type="number" id="stock" name="stock" min="0" required 
                               value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
                    </div>
                </div>

                <!-- Campo de imagen actualizado -->
                <div class="form-group">
                    <label for="imagen">Nombre de la imagen (opcional)</label>
                    <input type="text" id="imagen" name="imagen" 
                           placeholder="ej: laptop.jpg, auriculares.webp"
                           value="<?= htmlspecialchars($_POST['imagen'] ?? '') ?>">
                    <small style="color: var(--gray); display: block; margin-top: 5px;">
                        Guarda la imagen en la carpeta <code>src/images/</code> y escribe solo el nombre del archivo.
                    </small>
                    
                    <?php if (!empty($_POST['imagen'] ?? '')): ?>
                        <div style="margin-top: 10px;">
                            <img src="../images/<?= htmlspecialchars($_POST['imagen']) ?>" 
                                 alt="Vista previa" 
                                 style="max-width: 150px; max-height: 150px; border: 1px solid #eee; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </form>

            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al Panel de Administración
            </a>
        </div>
    </main>
</body>
</html>