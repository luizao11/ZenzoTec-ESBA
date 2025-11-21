// script.js
// Variables globales
let cart = [];
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
const totalSlides = slides.length;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Eventos para el carrito
    document.querySelector('.cart-icon').addEventListener('click', openCart);
    document.getElementById('close-cart').addEventListener('click', closeCart);
    document.getElementById('checkout-btn').addEventListener('click', checkout);
    
    // Eventos para los botones de agregar al carrito
    document.querySelectorAll('.btn-add-to-cart').forEach(button => {
        button.addEventListener('click', addToCart);
    });
    
    // Eventos para el carrusel
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => changeSlide(-1));
        nextBtn.addEventListener('click', () => changeSlide(1));
    }
    
    // Evento para el formulario de comentarios
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', addComment);
    }
    
    // Evento para el formulario de pedidos
    const orderForm = document.getElementById('order-form');
    if (orderForm) {
        orderForm.addEventListener('submit', submitOrder);
    }
    
    // Iniciar carrusel automático (solo si hay slides)
    if (totalSlides > 0) {
        setInterval(() => changeSlide(1), 5000);
    }
    
    // Cargar carrito desde localStorage
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartUI();
    }
});

// Funciones del carrito
function addToCart(e) {
    const button = e.currentTarget;
    const id = button.dataset.id;
    const name = button.dataset.name;
    const price = parseFloat(button.dataset.price);
    
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id,
            name,
            price,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
    showNotification('Producto agregado al carrito');
}

function updateCartUI() {
    const cartItems = document.getElementById('cart-items');
    const cartCount = document.getElementById('cart-count');
    const cartTotal = document.getElementById('cart-total');
    
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotal.textContent = `$${total.toFixed(2)}`;
    
    cartItems.innerHTML = '';
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
        return;
    }
    
    cart.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-info">
                <h4>${item.name}</h4>
                <p>Cantidad: ${item.quantity}</p>
                <p class="cart-item-price">$${(item.price * item.quantity).toFixed(2)}</p>
            </div>
            <button class="remove-item" data-id="${item.id}">
                <i class="fas fa-trash"></i>
            </button>
        `;
        cartItems.appendChild(cartItem);
    });
    
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', removeItem);
    });
}

function removeItem(e) {
    const id = e.currentTarget.dataset.id;
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

function openCart() {
    document.getElementById('cart-overlay').classList.add('active');
}

function closeCart() {
    document.getElementById('cart-overlay').classList.remove('active');
}

/*function checkout() {
    if (cart.length === 0) {
        showNotification('Tu carrito está vacío');
        return;
    }

    // Verificar si el usuario ha iniciado sesión
    const user = localStorage.getItem('user');
    if (!user) {
        // Redirigir al login
        window.location.href = 'login.html';
        return;
    }

    // Simular compra exitosa
    showNotification(`¡Gracias por tu compra, ${user}!`);
    cart = [];
    localStorage.removeItem('cart');
    updateCartUI();
    closeCart();
}*/
//Funcion Checkout
function checkout() {
    if (cart.length === 0) {
        showNotification('Tu carrito está vacío');
        return;
    }

    const user = localStorage.getItem('user');
    if (!user) {
        // Guardar intención de compra y redirigir al login
        localStorage.setItem('checkoutPending', 'true');
        window.location.href = 'login.html';
    } else {
        // Ir directamente al resumen de compra
        window.location.href = 'checkout.html';
    }
}


// Funciones del carrusel
function changeSlide(direction) {
    currentSlide += direction;
    
    if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    } else if (currentSlide >= totalSlides) {
        currentSlide = 0;
    }
    
    const carousel = document.getElementById('carousel');
    if (carousel) {
        carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
    }
}

// Funciones de comentarios
function addComment(e) {
    e.preventDefault();
    
    const rating = document.querySelector('input[name="rating"]:checked');
    const commentText = document.getElementById('comment-text').value;
    
    if (!rating || !commentText.trim()) {
        showNotification('Por favor, selecciona una calificación y escribe un comentario');
        return;
    }
    
    const commentsList = document.getElementById('comments-list');
    const newComment = document.createElement('div');
    newComment.className = 'comment';
    newComment.innerHTML = `
        <div class="comment-header">
            <span class="user">Tú</span>
            <div class="stars">
                ${generateStars(rating.value)}
            </div>
        </div>
        <p>${commentText}</p>
    `;
    
    commentsList.insertBefore(newComment, commentsList.firstChild);
    
    document.getElementById('comment-form').reset();
    showNotification('Comentario agregado exitosamente');
}

function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star"></i>';
        } else {
            stars += '<i class="far fa-star"></i>';
        }
    }
    return stars;
}

// Función para el formulario de pedidos
function submitOrder(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const name = formData.get('name');
    
    showNotification(`¡Gracias ${name}! Hemos recibido tu pedido.`);
    e.target.reset();
}

// Función para mostrar notificaciones
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    
    Object.assign(notification.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        backgroundColor: '#4F46E5',
        color: 'white',
        padding: '15px 25px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '10000',
        opacity: '0',
        transform: 'translateY(20px)',
        transition: 'all 0.3s ease'
    });
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 100);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
// === Cargar productos dinámicamente ===
function cargarProductos() {
    const container = document.getElementById('products-container');
    if (!container) return;

    fetch('productos_api.php')
        .then(response => response.json())
        .then(productos => {
            if (!Array.isArray(productos)) {
                container.innerHTML = '<p class="loading-products">No se pudieron cargar los productos.</p>';
                return;
            }

            if (productos.length === 0) {
                container.innerHTML = '<p class="loading-products">No hay productos disponibles.</p>';
                return;
            }

            let html = '';
            productos.forEach(producto => {
                // Si no hay imagen, usa un placeholder
                const imgSrc = producto.image 
                    ? `images/${producto.image}` 
                    : 'https://placehold.co/300x200/4F46E5/FFFFFF?text=Sin+Imagen';

                html += `
                    <div class="product-card">
                        <img src="${imgSrc}" 
                             alt="${producto.name}" 
                             onerror="this.src='https://placehold.co/300x200/4F46E5/FFFFFF?text=Sin+Imagen'">
                        <h3>${producto.name}</h3>
                        <p class="price">$${producto.price.toFixed(2)}</p>
                        <button class="btn-add-to-cart" 
                                data-id="${producto.id}" 
                                data-name="${producto.name}" 
                                data-price="${producto.price}">
                            Agregar al Carrito
                        </button>
                    </div>
                `;
            });
            container.innerHTML = html;

            // Re-vincular eventos de "Agregar al Carrito"
            document.querySelectorAll('.btn-add-to-cart').forEach(button => {
                button.addEventListener('click', addToCart);
            });
        })
        .catch(error => {
            console.error('Error al cargar productos:', error);
            container.innerHTML = '<p class="loading-products">Error al cargar los productos.</p>';
        });
}

// Ejecutar al cargar la página
if (document.getElementById('products-container')) {
    document.addEventListener('DOMContentLoaded', cargarProductos);
}