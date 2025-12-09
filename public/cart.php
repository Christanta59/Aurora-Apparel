<?php
session_start();
if(!isset($_SESSION['login'])){
  header("Location: login.php");
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Keranjang - Aurora Apparel</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    /* Styling ringan agar rapi dan konsisten */
    .container{max-width:1000px;margin:28px auto;padding:0 16px;font-family:Arial,Helvetica,sans-serif;}
    h1{margin-bottom:12px}
    table{width:100%;border-collapse:collapse;margin-bottom:12px}
    th,td{padding:10px;border:1px solid #e5e7eb;text-align:left}
    .muted{color:#6b7280;font-size:0.95rem}
    .checkout-card{border:1px solid #e6eef8;padding:16px;border-radius:8px;background:#fff;margin-top:16px}
    .form-row{margin-bottom:10px}
    input,textarea,select{width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px}
    .btn{background:#0ea5a4;color:#fff;border:none;padding:10px 14px;border-radius:7px;cursor:pointer}
    .btn[disabled]{background:#94a3b8;cursor:not-allowed}
    .small{font-size:0.9rem;color:#374151}
    .right{text-align:right}
  </style>
</head>
<body>
<?php
// jika ada header include milikmu, kamu bisa aktifkan:
// include 'header.php';
?>
<div class="container">
  <h1>Keranjang Saya</h1>

  <div id="cart-area">
    <table aria-describedby="cart-items">
      <thead>
        <tr>
          <th>Produk</th>
          <th>Harga</th>
          <th>Jumlah</th>
          <th>Subtotal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="cart-body">
        <tr><td colspan="5" class="muted">Memuat...</td></tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="right small">Grand Total</td>
          <td colspan="2" id="cart-total" class="small">Rp 0</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="checkout-card" aria-label="Form Checkout">
    <h2>Informasi Pengiriman & Pembayaran</h2>

    <form id="checkout-form" onsubmit="return false;">
      <div class="form-row">
        <label class="small">Nama Lengkap</label>
        <input type="text" id="customer_name" required>
      </div>

      <div class="form-row">
        <label class="small">Alamat Lengkap</label>
        <textarea id="customer_address" rows="3" required></textarea>
      </div>

      <div class="form-row">
        <label class="small">Nomor Telepon</label>
        <input type="tel" id="customer_phone" required>
      </div>

      <div class="form-row">
        <label class="small">Metode Pembayaran</label>
        <select id="payment_method" required>
          <option value="">Pilih metode pembayaran</option>
          <option value="transfer_bank">Transfer Bank</option>
          <option value="ewallet">E-Wallet</option>
          <option value="cod">COD (Bayar di Tempat)</option>
        </select>
      </div>

      <div class="form-row">
        <label class="small">Kurir Pengiriman</label>
        <select id="courier" required>
          <option value="">Pilih kurir</option>
          <option value="jne">JNE</option>
          <option value="jnt">J&T</option>
          <option value="pos">POS</option>
          <option value="sicepat">SiCepat</option>
        </select>
      </div>

      <div style="display:flex;gap:12px;align-items:center;margin-top:8px;">
        <button id="checkout-btn" class="btn" disabled>Checkout Sekarang</button>
        <div id="checkout-info" class="small muted"></div>
      </div>
    </form>
  </div>

</div>

<script>
/*
  Script ini memanggil endpoint cart di repo-mu:
  NOTE: endpoint cart list di repo-mu ada di ../api/cart/cart_list.php
  Endpoint checkout adalah ../api/checkout.php (sesuaikan jika berbeda)
*/
const CART_API = '../api/cart/cart_list.php';
const REMOVE_API = '../api/cart/cart_remove.php';
const CHECKOUT_API = '../api/checkout.php';

const cartBody = document.getElementById('cart-body');
const cartTotalEl = document.getElementById('cart-total');
const checkoutBtn = document.getElementById('checkout-btn');
const checkoutInfo = document.getElementById('checkout-info');

let cartItems = [];

function formatRp(n){ return 'Rp ' + Number(n).toLocaleString('id-ID'); }

async function loadCart(){
  cartBody.innerHTML = '<tr><td colspan="5" class="muted">Memuat...</td></tr>';
  try{
    const res = await fetch(CART_API, { credentials: 'same-origin' });
    const data = await res.json();
    // repo-mu mengembalikan array langsung (lihat cart_list.php), jadi handle array
    cartItems = Array.isArray(data) ? data : (data.cart || data.items || []);
    if(!Array.isArray(cartItems) || cartItems.length === 0){
      cartBody.innerHTML = '<tr><td colspan="5" class="muted">Keranjang kosong</td></tr>';
      cartTotalEl.textContent = formatRp(0);
      checkFormValidity();
      return;
    }

    let html = '';
    let total = 0;
    cartItems.forEach(it => {
      const price = parseFloat(it.price || 0);
      const qty = parseInt(it.qty || it.quantity || 1);
      const sub = price * qty;
      total += sub;

      html += `<tr>
        <td>${escapeHtml(it.name || it.product_name || 'Product')}</td>
        <td>${formatRp(price)}</td>
        <td>${qty}</td>
        <td>${formatRp(sub)}</td>
        <td><button onclick="removeCart(${it.cart_id})" class="small">Hapus</button></td>
      </tr>`;
    });
    cartBody.innerHTML = html;
    cartTotalEl.textContent = formatRp(total);
    checkFormValidity();
  }catch(err){
    console.error(err);
    cartBody.innerHTML = '<tr><td colspan="5" class="muted">Gagal memuat keranjang</td></tr>';
    cartTotalEl.textContent = formatRp(0);
    checkFormValidity();
  }
}

function escapeHtml(str){
  return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; });
}

async function removeCart(cartId){
  if(!confirm('Hapus item dari keranjang?')) return;
  const form = new FormData();
  form.append('cart_id', cartId);
  try{
    const res = await fetch(REMOVE_API, { method: 'POST', body: form, credentials: 'same-origin' });
    const data = await res.json();
    if(data.status === 'success') {
      await loadCart();
    } else {
      alert('Gagal menghapus item');
    }
  }catch(e){ console.error(e); alert('Error jaringan'); }
}

// VALIDASI FORM & aktifkan tombol checkout
const inputs = ['customer_name','customer_address','customer_phone','payment_method','courier'];
inputs.forEach(id=>{
  const el = document.getElementById(id);
  if(el) el.addEventListener('input', checkFormValidity);
});

function checkFormValidity(){
  const name = document.getElementById('customer_name').value.trim();
  const addr = document.getElementById('customer_address').value.trim();
  const phone = document.getElementById('customer_phone').value.trim();
  const pay = document.getElementById('payment_method').value;
  const courier = document.getElementById('courier').value;
  const cartOk = Array.isArray(cartItems) && cartItems.length > 0;
  const ok = name && addr && phone && pay && courier && cartOk;
  checkoutBtn.disabled = !ok;
  checkoutInfo.textContent = ok ? '' : (cartOk ? 'Lengkapi form untuk aktifkan tombol.' : 'Keranjang kosong.');
  return ok;
}

// HANDLE CHECKOUT
checkoutBtn.addEventListener('click', async () => {
  if(!checkFormValidity()) return alert('Lengkapi data checkout terlebih dahulu.');

  // prepare payload
  payload = {
    customer_name: document.getElementById('customer_name').value.trim(),
    customer_address: document.getElementById('customer_address').value.trim(),
    customer_phone: document.getElementById('customer_phone').value.trim(),
    payment_method: document.getElementById('payment_method').value,
    courier: document.getElementById('courier').value,
    cart: cartItems.map(it => ({
        cart_id: it.cart_id,
        product_id: parseInt(it.product_id),
        name: it.name,
        qty: it.qty,
        price: it.price
    }))
};


  checkoutBtn.disabled = true;
  checkoutBtn.textContent = 'Memproses...';

  try{
    const res = await fetch(CHECKOUT_API, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if(data.success){
      alert('Checkout berhasil! Order ID: ' + data.order_id);
      // redirect ke halaman sukses / home
      window.location.href = 'index.php';
    } else if(data.status === 'login'){
      alert('Silakan login ulang dahulu.');
      window.location.href = 'login.php';
    } else {
      alert('Checkout gagal: ' + (data.message || 'Server error'));
    }
  }catch(err){
    console.error(err);
    alert('Kesalahan jaringan saat checkout.');
  } finally {
    checkoutBtn.disabled = false;
    checkoutBtn.textContent = 'Checkout Sekarang';
    loadCart(); // refresh cart
  }
});

window.addEventListener('DOMContentLoaded', loadCart);
</script>
</body>
</html>
