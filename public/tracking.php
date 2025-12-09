<?php // public/tracking.php ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tracking — Aurora Apparel</title>
  <link rel="stylesheet" href="assets/style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
<nav>
  <div class="brand">
    <div class="logo">A</div>
    <div><h1>Aurora Apparel</h1><div style="font-size:12px;color:var(--muted)">Lacak pesananmu</div></div>
  </div>
  <div class="navlinks"><a href="index.php">Home</a></div>
</nav>

<div class="container">
  <div class="card">
    <h2>Tracking Order</h2>
    <div style="margin:10px 0;">
      <input id="resi" class="input" placeholder="Masukkan nomor resi)">
    </div>
    <div style="display:flex;gap:10px">
      <button class="btn" onclick="cek()">Cek Status</button>
      <button class="btn secondary" onclick="document.getElementById('resi').value = ''">Reset</button>
    </div>

    <div id="statusWrap" style="margin-top:18px"></div>
  </div>
</div>

<script>
const statusWrap = document.getElementById('statusWrap');
function createRow(k,v){ return `<div style="margin:6px 0"><strong>${k}:</strong> ${v}</div>` }

async function cek(){
  const r = document.getElementById('resi').value.trim();
  if(!r){ alert('Masukkan resi'); return; }
  statusWrap.innerHTML = '<div style="padding:18px;text-align:center"><div class="spinner"></div></div>';
  try{
    const resp = await fetch('../api/tracking.php?tracking=' + encodeURIComponent(r));
    const j = await resp.json();
    if(!j || !j.id){ statusWrap.innerHTML = '<div class="card">Resi tidak ditemukan</div>'; return; }
    statusWrap.innerHTML = `
      <div class="card fade-in">
        ${createRow('Order ID', j.id)}
        ${createRow('Status', '<b>'+j.status+'</b>')}
        ${createRow('Tracking No', j.tracking_no || '-')}
        ${createRow('Tanggal', j.created_at)}
      </div>
    `;
  }catch(e){
    statusWrap.innerHTML = '<div class="card">Gagal cek resi.</div>';
  }
}
</script>
</body>
</html>
