const cartEl = document.getElementById('cart');
const totalEl = document.getElementById('total');
const checkoutBtn = document.getElementById('checkoutBtn');

// Format angka
function currency(x){ return new Intl.NumberFormat('id-ID').format(x); }

// Load Cart Items
async function loadCart(){
  cartEl.innerHTML = '<div class="text-center py-4">Memuat...</div>';
  try{
    const res = await fetch('../api/cart_get.php');
    const data = await res.json();

    if(!Array.isArray(data) || data.length === 0){
      cartEl.innerHTML = '<div class="text-center py-4">Keranjang kosong</div>';
      totalEl.textContent = '0';
      return;
    }

    renderCart(data);
  }catch(e){
    cartEl.innerHTML = '<div class="text-center py-4 text-red-500">Gagal memuat keranjang.</div>';
    totalEl.textContent = '0';
  }
}

// Render Cart
function renderCart(items){
  let html = '';
  let total = 0;

  items.forEach(item=>{
    total += item.price*item.qty;
    html += `
      <div class="flex items-center justify-between mb-3 border-b pb-2">
        <div>
          <div class="font-semibold">${item.title}</div>
          <div class="text-gray-500">Rp ${currency(item.price)}</div>
        </div>
        <div class="flex items-center gap-2">
          <button class="bg-gray-200 px-2 rounded" onclick="updateQty(${item.id}, -1)">-</button>
          <span>${item.qty}</span>
          <button class="bg-gray-200 px-2 rounded" onclick="updateQty(${item.id}, 1)">+</button>
          <button class="text-red-500 ml-3" onclick="deleteItem(${item.id})">Hapus</button>
        </div>
      </div>
    `;
  });

  cartEl.innerHTML = html;
  totalEl.textContent = currency(total);
}

// Update Qty
async function updateQty(id, change){
  const form = new FormData();
  form.append('id', id);
  form.append('change', change);

  try{
    const res = await fetch('../api/cart_update.php',{method:'POST',body:form});
    const data = await res.json();
    if(data.status==='success') loadCart();
  }catch(e){console.log(e);}
}

// Hapus Item
async function deleteItem(id){
  if(!confirm('Hapus item ini dari keranjang?')) return;

  const form = new FormData();
  form.append('id',id);

  try{
    const res = await fetch('../api/cart_delete.php',{method:'POST',body:form});
    const data = await res.json();
    if(data.status==='deleted') loadCart();
  }catch(e){console.log(e);}
}

// Checkout

    // validasi simple
    if(!payload.customer_name || !payload.customer_address || !payload.customer_phone){
        alert("Mohon isi data pembeli dengan lengkap.");
        return;
    }

    try{
        const res = await fetch('../api/cart/checkout.php', {
            method: 'POST',
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        console.log("Checkout response:", data);

        if(data.success){
            alert("Checkout berhasil! Order ID: " + data.order_id);
            loadCart();
        } else {
            alert("Checkout gagal: " + data.message);
        }
    } catch(e){
        alert("Terjadi kesalahan server.");
    }
});


// Initial load
loadCart();

