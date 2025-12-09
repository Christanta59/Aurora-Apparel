<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: login.php");
  exit;
}

if($_SESSION['user']['role'] !== 'user'){
  header("Location: ../admin/dashboard.php");
  exit;
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Aurora Apparel — Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    *{font-family:'Poppins',sans-serif;}
    .navlinks a{margin-left:12px;text-decoration:none;color:var(--primary);font-weight:500;}
    .toast{
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: #ec4899;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      opacity: 0;
      transition: opacity 0.3s;
      z-index: 999;
    }
    .toast.show{opacity:1;}
  </style>
</head>
<body>

<nav class="flex items-center justify-between p-4 bg-white shadow">
  <div class="flex items-center gap-2">
    <div class="logo font-bold text-xl text-pink-500">A</div>
    <div>
      <h1 class="text-lg font-semibold">Aurora Apparel</h1>
      <div class="text-xs text-gray-500">Local fashion • Modern vibes</div>
    </div>
  </div>

  <div class="navlinks flex items-center">
    <a href="index.php">Home</a>
    <a href="tracking.php">Tracking</a>
    <!-- Ikon Keranjang dengan Jumlah Item -->
    <a href="cart.php" class="flex items-center gap-1">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="9" cy="20" r="1"></circle>
        <circle cx="17" cy="20" r="1"></circle>
        <path d="M1 1h4l2.68 12.39A2 2 0 0 0 9.62 15h7.76a2 2 0 0 0 1.94-1.61L22 6H6"></path>
      </svg>
      <span id="cart-count">0</span>
    </a>
    <a href="../api/logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="hero fade-in">
    <div class="hero-card">
      <h2>Produk Terlaris</h2>
      <p>Desain stylish, bahan nyaman. Pilih favoritmu dan checkout cepat — stok langsung terpotong saat checkout.</p>
      <div style="margin-top:12px;">
        <input id="q" class="input" placeholder="Cari produk...">
        <select id="sort" class="input" style="width:160px;margin-top:10px;">
    <option value="">Urutkan</option>
    <option value="low">Harga: Rendah → Tinggi</option>
    <option value="high">Harga: Tinggi → Rendah</option>
</select>

      </div>
    </div>

    <div style="width:360px">
      <div class="card" style="text-align:center">
        <div style="font-size:12px;color:var(--muted)">Promo</div>
        <div style="font-size:20px;font-weight:700;margin:8px 0">Diskon 20% untuk pembelian pertama</div>
        <button class="btn" onclick="showToast('Gunakan kode: AURORA20')">Dapatkan Kode</button>
      </div>
    </div>
  </div>

  <div id="products" class="grid"></div>
  <div id="loading" style="display:none;text-align:center;margin-top:18px;"><div class="spinner"></div></div>
</div>

<div id="toast" class="toast"></div>

<script>
const productsEl = document.getElementById('products');
const loadingEl = document.getElementById('loading');
const toastEl = document.getElementById('toast');
const q = document.getElementById('q');

// Toast
function showToast(msg){
  toastEl.textContent = msg;
  toastEl.classList.add('show');
  setTimeout(()=>toastEl.classList.remove('show'),3000);
}

// Load Products
async function loadProducts(){
  loadingEl.style.display='block';
  productsEl.innerHTML='';

  try{
    const res = await fetch('../api/products.php');
    let data = await res.json();

    // Ambil jenis sorting dari dropdown
    const sortType = document.getElementById('sort').value;

    // Jalankan sorting
    data = sortProducts(data, sortType);

    renderProducts(data);

  }catch(e){
    productsEl.innerHTML = '<div class="card">Gagal memuat produk.</div>';
  }finally{
    loadingEl.style.display='none';
  }
}


// Format Rupiah
function currency(x){ return new Intl.NumberFormat('id-ID').format(x); }

// Render Produk
function renderProducts(list){
  if(!list || list.length===0){
    productsEl.innerHTML='<div class="card">Belum ada produk.</div>';
    return;
  }

  const html = list.map(p=>`
    <div class="card fade-in">
      <div class="img" style="width:100%;height:180px;overflow:hidden;border-radius:8px;">
  <img 
  src="assets/img/${p.id}.png"
  style="width:100%;height:100%;object-fit:cover;"
  onerror="this.src='assets/img/default.png'"
>

  </div>

      <div class="product-name">${p.name}</div>
      <div class="price">Rp ${currency(p.price)}</div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px">
        <!-- Tombol Add to Cart -->
        <button class="btn" onclick="addCart('${p.id}',1)">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M0 1a1 1 0 0 1 1-1h1.5a.5.5 0 0 1 .485.379L3.89 3H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.49-.402L1.61 1H1a1 1 0 0 1-1-1zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
          </svg> Add
        </button>
      </div>
    </div>
  `).join('');

  productsEl.innerHTML = html;
}
function sortProducts(list, type){
    if(type === "low"){
        return list.sort((a,b) => a.price - b.price);
    }
    if(type === "high"){
        return list.sort((a,b) => b.price - a.price);
    }
    return list;
}


// Tambah ke Cart
async function addCart(product_id, qty = 1){
  const form = new FormData();
  form.append('product_id', product_id);
  form.append('qty', qty);

  try{
    const res = await fetch('../api/cart/cart_add.php', {
      method: 'POST',
      body: form,
      credentials: 'include'
  });

    const data = await res.json();

    if(data.status === 'success'){
      showToast('Berhasil ditambahkan ke keranjang!');
      updateCartCount();
    } else {
      showToast('Gagal: ' + data.message);
    }

  } catch (err){
    showToast('Kesalahan jaringan');
  }
}


// Update jumlah item di navbar
async function updateCartCount(){
  try{
    const res = await fetch('../api/cart/cart_list.php', { credentials: 'include' });
    const data = await res.json();
    document.getElementById('cart-count').innerText = data.length;
  }catch(err){
    console.error('Gagal memuat jumlah keranjang', err);
  }
}


// Panggil saat load page
updateCartCount();

// Search
q.addEventListener('input',()=>{
  const val = q.value.toLowerCase();
  const cards = Array.from(document.querySelectorAll('.card'));
  cards.forEach(c=>{
    const txt = c.querySelector('.product-name')?.innerText.toLowerCase()||'';
    c.style.display = txt.includes(val)?'':'none';
  });
});

// Inisialisasi
document.getElementById('sort').addEventListener('change', loadProducts);
loadProducts();
</script>

<script>
if('serviceWorker' in navigator){
  navigator.serviceWorker.register('/sw.js').then(()=>console.log('SW registered')).catch(()=>console.log('SW failed'));
}
</script>

</body>
</html>
