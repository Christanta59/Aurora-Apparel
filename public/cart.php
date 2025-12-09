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
  <title>Keranjang</title>
</head>
<body>
<h1>Keranjang Saya</h1>
<table border="1">
  <thead>
    <tr>
      <th>Produk</th>
      <th>Harga</th>
      <th>Jumlah</th>
      <th>Total</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody id="cart-body"></tbody>
</table>

<script>
function loadCart(){
    fetch('../api/cart/cart_list.php')
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('cart-body');
        tbody.innerHTML = '';
        let grandTotal = 0;
        data.forEach(item => {
            const total = item.price * item.qty;
            grandTotal += total;
            tbody.innerHTML += `
                <tr>
                    <td>${item.name}</td>
                    <td>Rp ${item.price.toLocaleString()}</td>
                    <td>${item.qty}</td>
                    <td>Rp ${total.toLocaleString()}</td>
                    <td>
                        <button onclick="removeCart(${item.cart_id})">Hapus</button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML += `<tr><td colspan="3">Grand Total</td><td colspan="2">Rp ${grandTotal.toLocaleString()}</td></tr>`;
    });
}

function removeCart(cartId){
    let form = new FormData();
    form.append('cart_id', cartId);
    fetch('../api/cart/cart_remove.php', {
        method: 'POST',
        body: form
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            loadCart();
        }
    });
}

loadCart();
</script>
</body>
</html>
