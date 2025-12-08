<?php
include '../config/db.php';

$today = $conn->query("SELECT COUNT(*) total FROM orders")->fetch_assoc();
$sales = $conn->query("
  SELECT DATE(created_at) tgl, COUNT(*) total 
  FROM orders 
  GROUP BY tgl
");

echo json_encode([
  "total_transaksi"=>$today['total'],
  "grafik"=>$sales->fetch_all(MYSQLI_ASSOC)
]);
