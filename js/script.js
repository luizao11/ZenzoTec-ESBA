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
    document.getElementById('prevBtn').addEventListener('click', () => changeSlide(-1));
    document.getElementById('nextBtn').addEventListener('click', () => changeSlide(1));
    
    // Evento para el formulario de comentarios
    document.getElementById('comment-form').addEventListener('submit', addComment);
    
    // Evento para el formulario de pedidos
    document.getElementById('order-form').addEventListener('submit', submitOrder);
    
    // Iniciar carrusel automático
    setInterval(() => changeSlide(1), 5000);
    
    // Actualizar carrito desde localStorage si existe
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
    
    // Verificar si el producto ya está en el carrito
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
    
    // Guardar en localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Actualizar interfaz
    updateCartUI();
    
    // Mostrar notificación
    showNotification('Producto agregado al carrito');
}

function updateCartUI() {
    const cartItems = document.getElementById('cart-items');
    const cartCount = document.getElementById('cart-count');
    const cartTotal = document.getElementById('cart-total');
    
    // Actualizar contador
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    // Actualizar total
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotal.textContent = `$${total.toFixed(2)}`;
    
    // Limpiar contenedor
    cartItems.innerHTML = '';
    
    // Mostrar mensaje si está vacío
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
        return;
    }
    
    // Agregar productos al carrito
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
    
    // Agregar eventos a los botones de eliminar
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

function checkout() {
    if (cart.length === 0) {
        showNotification('Tu carrito está vacío');
        return;
    }
    
    showNotification('¡Pedido realizado con éxito! Gracias por tu compra.');
    cart = [];
    localStorage.removeItem('cart');
    updateCartUI();
    closeCart();
}

// Funciones del carrusel
function changeSlide(direction) {
    currentSlide += direction;
    
    if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    } else if (currentSlide >= totalSlides) {
        currentSlide = 0;
    }
    
    document.getElementById('carousel').style.transform = `translateX(-${currentSlide * 100}%)`;
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
    
    // Resetear formulario
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
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    
    // Estilos básicos
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = '#4F46E5';
    notification.style.color = 'white';
    notification.style.padding = '15px 25px';
    notification.style.borderRadius = '8px';
    notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    notification.style.zIndex = '10000';
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(20px)';
    notification.style.transition = 'all 0.3s ease';
    
    document.body.appendChild(notification);
    
    // Mostrar notificación
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 100);
    
    // Eliminar notificación después de 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}