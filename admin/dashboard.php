<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['user']['role']!="admin"){
    header("Location: ../public/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard — Aurora</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../public/assets/style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
nav {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 28px;
    border-bottom:1px solid #ddd;
    background:#fff;
}

nav .brand {
    display:flex;
    align-items:center;
    gap:12px;
}

nav .brand .logo {
    width:40px;
    height:40px;
    background:#8b5cf6;
    border-radius:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:20px;
    font-weight:700;
}

nav .navlinks button {
    font-family:'Poppins';
    font-weight:500;
    margin-left:20px;
    text-decoration:none;
    color:#333;
    background:none;
    border:none;
    cursor:pointer;
}

nav .navlinks button:hover {
    color:#8b5cf6;
}

.content {
    padding:20px 40px;
}

.status-select {
    padding:6px;
    border-radius:6px;
}
.save-btn {
    padding:6px 12px;
    background:#8b5cf6;
    border:none;
    color:white;
    border-radius:6px;
    cursor:pointer;
}
.save-btn:hover {
    background:#6d28d9;
}
</style>

</head>
<body>

<!-- NAVBAR TANPA ORDERS -->
<nav>
  <div class="brand">
    <div class="logo">A</div>
    <div>
      <h1 style="margin:0;font-size:20px;">Admin — Aurora</h1>
      <div style="font-size:12px;color:#777">Dashboard</div>
    </div>
  </div>

  <div class="navlinks">
    <form method="POST" action="../api/logout.php" style="display:inline">
      <button type="submit">Logout</button>
    </form>
  </div>
</nav>

<!-- CONTENT UTAMA -->
<div class="content">

  <div class="stats" style="display:flex;gap:20px;margin-bottom:20px;">
    <div class="stat card" style="flex:1;padding:20px;">
      <h4>Total Transaksi</h4>
      <div id="totalTrans" style="font-size:24px;font-weight:800">-</div>
    </div>
    <div class="stat card" style="flex:1;padding:20px;">
      <h4>Produk Terdaftar</h4>
      <div id="totalProd" style="font-size:24px;font-weight:800">-</div>
    </div>
  </div>

  <div class="card" style="padding:20px;">
    <h3>Grafik Penjualan (30 hari)</h3>
    <canvas id="salesChart" height="120"></canvas>
  </div>

  <div class="table card" style="margin-top:18px; padding:20px;">
    <h3>Transaksi Terakhir</h3>
    <div id="recent"></div>
  </div>

</div>

<script>
// UPDATE STATUS
async function updateStatus(id){
    const select = document.getElementById('status_'+id);
    const newStatus = select.value;

    const res = await fetch('../api/update_status.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id, status:newStatus})
    });

    const result = await res.json();

    if(result.success){
        alert('Status berhasil diperbarui!');
    } else {
        alert('Gagal update status');
    }
}

// LOAD DASHBOARD
async function loadDashboard(){
  try{
    const d = await (await fetch('../api/dashboard.php')).json();
    document.getElementById('totalTrans').innerText = d.total_transaksi || 0;

    const pl = await (await fetch('../api/products.php')).json();
    document.getElementById('totalProd').innerText = pl.length || 0;

    const labels = d.grafik.map(x=>x.tgl);
    const vals = d.grafik.map(x=>Number(x.total));

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
      type:'line',
      data:{
        labels,
        datasets:[{
          label:'Penjualan (unit)',
          data: vals,
          fill:true,
          tension:0.3,
          backgroundColor: 'rgba(139,92,246,0.12)',
          borderColor: '#8b5cf6',
          pointBackgroundColor:'#ec4899'
        }]
      },
      options:{responsive:true,scales:{y:{beginAtZero:true}}}
    });

    const recent = await (await fetch('../api/recent_orders.php')).json();
    let html = `
    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Tracking</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>`;

    recent.forEach(o=>{
      html += `
      <tr>
        <td>${o.id}</td>
        <td>${o.customer||'-'}</td>
        <td>
            <select id="status_${o.id}" class="status-select">
                <option value="On Progress" ${o.status=="On Progress"?"selected":""}>On Progress</option>
                <option value="Shipped" ${o.status=="Shipped"?"selected":""}>Shipped</option>
                <option value="Completed" ${o.status=="Completed"?"selected":""}>Completed</option>
            </select>
        </td>
        <td>${o.tracking_no || '-'}</td>
        <td>${o.created_at}</td>
        <td>
            <button class="save-btn" onclick="updateStatus(${o.id})">Save</button>
        </td>
      </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('recent').innerHTML = html;

  }catch(e){
    console.error(e);
    alert('Gagal memuat dashboard');
  }
}
loadDashboard();
</script>

</body>
</html>
