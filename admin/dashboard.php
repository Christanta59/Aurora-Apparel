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
/* Navbar & Logout Button */
nav .navlinks a,
nav .navlinks button {
    font-family: 'Poppins', sans-serif;
    font-weight:500;
    color: var(--dark);
    background: none;
    border: none;
    cursor: pointer;
    margin-left: 18px;
    padding: 0;
    text-decoration: none;
    transition: color .2s;
}

nav .navlinks a:hover,
nav .navlinks button:hover {
    color: var(--secondary);
}
</style>
</head>
<body>
<nav>
  <div class="brand">
    <div class="logo">A</div>
    <div>
      <h1>Admin — Aurora</h1>
      <div style="font-size:12px;color:var(--muted)">Dashboard</div>
    </div>
  </div>
  <div class="navlinks">
    <a href="../public/index.php">Lihat Toko</a>
    <form method="POST" action="../api/logout.php" style="display:inline">
      <button type="submit" class="nav-btn">Logout</button>
    </form>
  </div>
</nav>

<div class="container">
  <div class="stats">
    <div class="stat card">
      <h4>Total Transaksi</h4>
      <div id="totalTrans" style="font-size:24px;font-weight:800">-</div>
    </div>
    <div class="stat card">
      <h4>Produk Terdaftar</h4>
      <div id="totalProd" style="font-size:24px;font-weight:800">-</div>
    </div>
  </div>

  <div class="card">
    <h3>Grafik Penjualan (30 hari)</h3>
    <canvas id="salesChart" height="120"></canvas>
  </div>

  <div class="table card" style="margin-top:18px">
    <h3>Transaksi Terakhir</h3>
    <div id="recent"></div>
  </div>
</div>

<script>
async function loadDashboard(){
  try{
    // Dashboard summary
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
      options:{
        responsive:true,
        scales:{y:{beginAtZero:true}}
      }
    });

    const recent = await (await fetch('../api/recent_orders.php')).json();
    let html = '<table><thead><tr><th>ID</th><th>Customer</th><th>Status</th><th>Tracking</th><th>Tanggal</th></tr></thead><tbody>';
    recent.forEach(o=>{
      html += `<tr><td>${o.id}</td><td>${o.customer||'-'}</td><td>${o.status}</td><td>${o.tracking_no||'-'}</td><td>${o.created_at}</td></tr>`;
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
