<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if(!isset($_SESSION['login']) || ($_SESSION['user']['role'] ?? '') !== 'admin'){
    header('Location: ../public/login.php');
    exit;
}

// ambil semua order
$res = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = [];
while($r = $res->fetch_assoc()) $orders[] = $r;
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin - Orders</title></head>
<body>
<h1>Daftar Pesanan</h1>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>ID</th><th>Nama</th><th>Telepon</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach($orders as $o): ?>
<tr>
  <td><?=htmlspecialchars($o['id'])?></td>
  <td><?=htmlspecialchars($o['customer_name'] ?? $o['name'] ?? '')?></td>
  <td><?=htmlspecialchars($o['customer_phone'] ?? '')?></td>
  <td><?=number_format($o['total'] ?? ($o['total_price'] ?? $o['subtotal']),0,',','.')?></td>
  <td><?=htmlspecialchars($o['status'] ?? '')?></td>
  <td><?=htmlspecialchars($o['created_at'])?></td>
  <td><a href="order_detail.php?id=<?=urlencode($o['id'])?>">Detail</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</body>
</html>
