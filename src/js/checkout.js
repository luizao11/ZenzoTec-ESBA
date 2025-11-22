document.addEventListener('DOMContentLoaded', async function() {
    // === 1. Actualizar menú de navegación ===
    const nav = document.getElementById('main-nav');
    if (nav) {
        // Verificar sesión para el menú
        try {
            const authRes = await fetch('obtener_perfil.php', { method: 'HEAD' });
            if (authRes.ok) {
                nav.insertAdjacentHTML('beforeend', `
                    <li><a href="checkout.html">Mi Cuenta</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                `);
            } else {
                nav.insertAdjacentHTML('beforeend', `
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="registrarse.html">Registrarse</a></li>
                `);
            }
        } catch {
            nav.insertAdjacentHTML('beforeend', `
                <li><a href="login.php">Iniciar Sesión</a></li>
                <li><a href="registrarse.html">Registrarse</a></li>
            `);
        }
    }

    // === 2. Cargar carrito y dirección ===
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const checkoutItems = document.getElementById('checkout-items');
    const checkoutTotal = document.getElementById('checkout-total');
    const direccionInput = document.getElementById('direccion_envio');

    // Validar carrito
    if (cart.length === 0) {
        checkoutItems.innerHTML = '<p class="empty-checkout">No hay productos en tu carrito.</p>';
        checkoutTotal.textContent = '$0.00';
        return;
    }

    // Mostrar productos
    let html = '';
    let total = 0;
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        html += `
            <div class="checkout-item">
                <div>
                    <strong>${item.name}</strong><br>
                    <small>${item.quantity} x $${item.price.toFixed(2)}</small>
                </div>
                <div>$${itemTotal.toFixed(2)}</div>
            </div>
        `;
    });
    checkoutItems.innerHTML = html;
    checkoutTotal.textContent = `$${total.toFixed(2)}`;

    // === 3. Verificar sesión y cargar dirección ===
    try {
        const profileRes = await fetch('obtener_perfil.php');
        
        // Verificar que la respuesta sea JSON
        const contentType = profileRes.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Sesión no válida');
        }

        const profileData = await profileRes.json();
        
        if (profileData.success) {
            direccionInput.value = profileData.direccion;
            direccionInput.disabled = false;
        } else {
            throw new Error('Sesión expirada');
        }
    } catch (error) {
        alert('Debes iniciar sesión para continuar con la compra.');
        window.location.href = 'login.php';
        return;
    }

    // === 4. Confirmar compra ===
    document.getElementById('confirm-purchase').addEventListener('click', async function() {
        const direccion = document.getElementById('direccion_envio').value.trim();
        if (!direccion) {
            alert('Por favor, verifica tu dirección de envío.');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        btn.disabled = true;

        try {
            const response = await fetch('procesar_pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    productos: cart, 
                    total: total,
                    direccion_envio: direccion
                })
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Error en el servidor');
            }

            const result = await response.json();

            if (result.success) {
                alert(`✅ ¡Compra realizada con éxito!\nNúmero de pedido: ${result.numero_pedido}`);
                localStorage.removeItem('cart');
                window.location.href = 'index.html';
            } else {
                alert('❌ Error: ' + (result.error || 'No se pudo procesar la compra.'));
            }
        } catch (error) {
            alert('❌ Error de conexión. Inténtalo más tarde.');
            console.error('Error:', error);
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
});