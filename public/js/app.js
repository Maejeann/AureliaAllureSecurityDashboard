// ------------------ Product Data ------------------
const products = [
  {
    id: 1,
    name: "Radiant Glow Serum",
    category: "Skincare",
    price: 899,
    image: "https://images.unsplash.com/photo-1612817159949-195b6eb9a4fd"
  },
  {
    id: 2,
    name: "Velvet Matte Lipstick",
    category: "Makeup",
    price: 499,
    image: "https://images.unsplash.com/photo-1617220064414-3b1f3a72b1c2"
  },
  {
    id: 3,
    name: "Silk Face Mask (5pcs)",
    category: "Skincare",
    price: 299,
    image: "https://images.unsplash.com/photo-1588776814546-ec4a83c2324c"
  },
  {
    id: 4,
    name: "Pearl Drop Earrings",
    category: "Jewelry",
    price: 699,
    image: "https://images.unsplash.com/photo-1600180758895-bd88e9b8c3f5"
  },
  {
    id: 5,
    name: "Blush Compact",
    category: "Makeup",
    price: 350,
    image: "https://images.unsplash.com/photo-1613082371743-1e609de61f82"
  },
  {
    id: 6,
    name: "Hydrating Toner",
    category: "Skincare",
    price: 420,
    image: "https://images.unsplash.com/photo-1612817159341-68be1a7cdb93"
  }
];

// ------------------ Render Products ------------------
function renderProducts() {
  const grid = document.getElementById("product-grid");
  if (!grid) return;

  grid.innerHTML = products.map(p => `
    <div class="card">
      <img src="${p.image}" alt="${p.name}">
      <div class="card-body">
        <div class="product-title">${p.name}</div>
        <small class="small">${p.category}</small>
        <div class="product-price">₱${p.price.toFixed(2)}</div>
        <div class="card-actions">
          <button class="btn btn-primary" onclick="addToCart(${p.id})">Add</button>
          <button class="btn btn-outline" onclick="viewProduct(${p.id})">View</button>
        </div>
      </div>
    </div>
  `).join("");
}

// ------------------ Cart System ------------------
let cart = [];

function addToCart(id) {
  const product = products.find(p => p.id === id);
  const item = cart.find(i => i.id === id);

  if (item) {
    item.qty++;
  } else {
    cart.push({...product, qty: 1});
  }
  updateCart();
}

function removeFromCart(id) {
  cart = cart.filter(item => item.id !== id);
  updateCart();
}

function changeQty(id, amount) {
  const item = cart.find(i => i.id === id);
  if (!item) return;
  item.qty += amount;
  if (item.qty <= 0) {
    removeFromCart(id);
  }
  updateCart();
}

function updateCart() {
  const count = cart.reduce((sum, i) => sum + i.qty, 0);
  document.getElementById("cart-count").textContent = count;

  const list = document.getElementById("cart-items");
  if (!list) return;

  list.innerHTML = cart.map(item => `
    <div class="item">
      <img src="${item.image}" alt="${item.name}">
      <div style="flex:1">
        <strong>${item.name}</strong>
        <div class="small">₱${item.price.toFixed(2)}</div>
        <div class="qty">
          <button onclick="changeQty(${item.id}, -1)">-</button>
          <span>${item.qty}</span>
          <button onclick="changeQty(${item.id}, 1)">+</button>
        </div>
      </div>
      <button class="icon-btn" onclick="removeFromCart(${item.id})">✖</button>
    </div>
  `).join("");
}

// ------------------ Drawer Toggle ------------------
function toggleCart() {
  document.getElementById("cart-drawer").classList.toggle("open");
}

// ------------------ View Product (placeholder) ------------------
function viewProduct(id) {
  const product = products.find(p => p.id === id);
  alert(`Viewing: ${product.name} - ₱${product.price.toFixed(2)}`);
}

// ------------------ Init ------------------
document.addEventListener("DOMContentLoaded", renderProducts);
