<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

try {
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $productos = $pdo->query("SELECT * FROM productos ORDER BY nombre")->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - ZenzoTec Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/listar_productos.css">
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
                    <li><a href="agregar_producto.php">Nuevo Producto</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <div class="section">
            <h2><i class="fas fa-boxes"></i> Gestión de Productos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="../images/<?= htmlspecialchars($p['imagen']) ?>" 
                                     alt="<?= htmlspecialchars($p['nombre']) ?>" 
                                     class="product-img">
                            <?php else: ?>
                                <i class="fas fa-image" style="color: var(--gray);"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td>$<?= number_format($p['precio'], 2) ?></td>
                        <td>
                            <?php if ($p['stock'] <= 5): ?>
                                <span class="stock-low"><?= $p['stock'] ?></span>
                            <?php else: ?>
                                <?= $p['stock'] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar_producto.php?id=<?= $p['id'] ?>" class="btn-small btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <button class="btn-delete" onclick="eliminarProducto(<?= $p['id'] ?>, '<?= addslashes($p['nombre']) ?>')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function eliminarProducto(id, nombre) {
            if (confirm('¿Seguro que deseas eliminar "' + nombre + '"? Esta acción no se puede deshacer.')) {
                window.location.href = 'editar_producto.php?eliminar=' + id;
            }
        }
    </script>
</body>
</html>