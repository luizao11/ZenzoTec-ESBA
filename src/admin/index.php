<?php
session_start();

// Verificar que haya sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// 🔑 EMAIL DEL ADMINISTRADOR - CAMBIA ESTO POR TU EMAIL REAL
$admin_email = 'nueva123@zenzo.com';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=db;dbname=zenzotec_db;charset=utf8mb4", "zenzotec_user", "userpass123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si el usuario es el administrador autorizado
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ? AND email = ?");
    $stmt->execute([$_SESSION['usuario_id'], $admin_email]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        die("<h2 style='text-align:center; margin-top:50px; color:#EF4444;'>Acceso denegado. Solo el administrador puede acceder a esta sección.</h2>");
    }
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener resumen estadístico
$pedidos_count = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$usuarios_count = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$productos_count = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$ventas_totales = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado = 'completado'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - ZenzoTec</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/admin.css">
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
                    <li><a href="../index.html">Tienda</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                    <li><a href="agregar_producto.php">Nuevo Producto</a></li>
                    <li><a href="listar_productos.php">Gestionar Productos</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <!-- Resumen estadístico -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <div class="stat-value"><?= $pedidos_count ?></div>
                <div>Pedidos</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-value"><?= $usuarios_count ?></div>
                <div>Usuarios</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-boxes"></i>
                <div class="stat-value"><?= $productos_count ?></div>
                <div>Productos</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign"></i>
                <div class="stat-value">$<?= number_format($ventas_totales, 2) ?></div>
                <div>Ventas Totales</div>
            </div>
        </div>

        <!-- Pedidos recientes -->
        <div class="section">
            <h2><i class="fas fa-list"></i> Pedidos Recientes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("
                        SELECT p.id, p.numero_pedido, p.fecha_pedido, p.total, p.estado, 
                               CONCAT(u.nombre, ' ', u.apellido) as usuario
                        FROM pedidos p
                        JOIN usuarios u ON p.usuario_id = u.id
                        ORDER BY p.fecha_pedido DESC
                        LIMIT 10
                    ");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['numero_pedido']) ?></td>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['fecha_pedido'])) ?></td>
                        <td>$<?= number_format($row['total'], 2) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['estado'])) ?></td>
                        <td>
                            <a href="pedidos.php?id=<?= $row['id'] ?>" class="btn-small btn-view">
                                Ver
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Gestión de Stock -->
        <div class="section">
            <h2><i class="fas fa-warehouse"></i> Gestión de Stock</h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock Actual</th>
                        <th>Nuevo Stock</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $productos = $pdo->query("SELECT id, nombre, precio, stock FROM productos ORDER BY stock ASC");
                    while ($prod = $productos->fetch()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['nombre']) ?></td>
                        <td>$<?= number_format($prod['precio'], 2) ?></td>
                        <td><?= $prod['stock'] ?></td>
                        <td>
                            <input type="number" 
                                   class="stock-input" 
                                   data-producto-id="<?= $prod['id'] ?>" 
                                   placeholder="<?= $prod['stock'] ?>">
                        </td>
                        <td>
                            <button class="save-btn" onclick="actualizarStock(<?= $prod['id'] ?>)">
                                Guardar
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Usuarios Registrados -->
        <div class="section">
            <h2><i class="fas fa-users"></i> Usuarios Registrados</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Dirección</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $usuarios = $pdo->query("SELECT nombre, apellido, email, direccion FROM usuarios");
                    while ($usr = $usuarios->fetch()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($usr['nombre'] . ' ' . $usr['apellido']) ?></td>
                        <td><?= htmlspecialchars($usr['email']) ?></td>
                        <td><?= htmlspecialchars($usr['direccion']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function actualizarStock(id) {
            const input = document.querySelector(`input[data-producto-id="${id}"]`);
            const nuevoStock = input.value.trim();
            
            if (nuevoStock === '' || isNaN(nuevoStock) || nuevoStock < 0) {
                alert('Por favor, ingresa un stock válido.');
                return;
            }

            fetch('actualizar_stock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, stock: parseInt(nuevoStock) })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Stock actualizado correctamente.');
                    input.placeholder = nuevoStock;
                    input.value = '';
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(() => alert('Error de conexión.'));
        }
    </script>
</body>
</html>