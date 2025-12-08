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
</head>
<body>
<nav>
  <div class="brand">
    <div class="logo">A</div>
    <div>
      <h1>Aurora Apparel</h1>
      <div style="font-size:12px;color:var(--muted)">Local fashion • Modern vibes</div>
    </div>
  </div>
  <div class="navlinks">
    <a href="index.php">Home</a>
    <a href="tracking.php">Tracking</a>
    <a href="../admin/dashboard.php">Admin</a>
    <a href="../api/logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="hero fade-in">
    <div class="hero-card">
      <h2>Produk Terlaris ✨</h2>
      <p>Desain stylish, bahan nyaman. Pilih favoritmu dan checkout cepat — stok langsung terpotong saat checkout.</p>
      <div style="margin-top:12px;">
        <input id="q" class="input" placeholder="Cari produk...">
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

  <div id="loading" style="display:none;text-align:center;margin-top:18px;">
    <div class="spinner"></div>
  </div>
</div>

<!-- toast -->
<div id="toast" class="toast"></div>

<script>
const productsEl = document.getElementById('products');
const loadingEl = document.getElementById('loading');
const toastEl = document.getElementById('toast');
const q = document.getElementById('q');

// show toast helper
function showToast(msg){
  toastEl.textContent = msg;
  toastEl.classList.add('show');
  setTimeout(()=>toastEl.classList.remove('show'),3000);
}

// fetch products
async function loadProducts(){
  loadingEl.style.display='block';
  productsEl.innerHTML='';
  try{
    const res = await fetch('../api/products.php');
    const data = await res.json();
    renderProducts(data);
  }catch(e){
    productsEl.innerHTML = '<div class="card">Gagal memuat produk.</div>';
  }finally{
    loadingEl.style.display='none';
  }
}

function currency(x){ return new Intl.NumberFormat('id-ID').format(x) }

function renderProducts(list){
  if(!list || list.length===0){
    productsEl.innerHTML='<div class="card">Belum ada produk.</div>';
    return;
  }
  const html = list.map(p=>`
    <div class="card fade-in">
      <div class="img">${p.name}</div>
      <div class="product-name">${p.name}</div>
      <div class="price">Rp ${currency(p.price)}</div>
      <div style="display:flex;justify-content:space-between;align-items:center">
        <span class="badge">Stok: ${p.stock}</span>
        <button class="btn" onclick="checkout('${p.sku}', '${p.name}', ${p.price})">Checkout</button>
      </div>
    </div>
  `).join('');
  productsEl.innerHTML = html;
}

// checkout (reserve stock & auto generate resi)
async function checkout(sku, name, price){
  showToast('Memproses checkout...');
  const form = new FormData();
  form.append('sku', sku);
  form.append('qty', 1);

  try{
    const res = await fetch('../api/checkout.php', { 
      method:'POST', 
      body: form 
    });

    const j = await res.json();

    // ✅ BELUM LOGIN → ARAHKAN KE LOGIN
    if(j.status === 'login'){
      alert('Silakan login terlebih dahulu');
      window.location.href = 'login.php';
      return;
    }

    // ✅ STOK HABIS / ERROR
    if(j.status === 'error'){
      showToast('Gagal: ' + j.msg);
      return;
    }

    // ✅ SUKSES
    if(j.status === 'success'){
      showToast('Berhasil! Resi: ' + j.tracking);
      loadProducts(); // refresh stok
    }

  }catch(err){
    showToast('Kesalahan jaringan');
  }
}

q.addEventListener('input', ()=> {
  const val = q.value.toLowerCase();
  const cards = Array.from(document.querySelectorAll('.card'));
  cards.forEach(c=>{
    const txt = c.querySelector('.product-name') ? c.querySelector('.product-name').innerText.toLowerCase() : '';
    c.style.display = txt.includes(val) ? '' : 'none';
  });
});

loadProducts();
</script>

<!-- PWA: register service worker -->
<script>
if('serviceWorker' in navigator){
  navigator.serviceWorker.register('/sw.js').then(()=>console.log('SW registered')).catch(()=>console.log('SW failed'));
}
</script>
</body>
</html>
