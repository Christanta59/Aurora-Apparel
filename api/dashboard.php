<?php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

// hanya admin
if(!isset($_SESSION['login']) || $_SESSION['user']['role'] !== 'admin'){
    echo json_encode(['error'=>true, 'message'=>'unauthorized']);
    exit;
}

// total transaksi
$total = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc();

// grafik 30 hari
$sales = $conn->query("
    SELECT DATE(created_at) AS tgl, COUNT(*) AS total
    FROM orders
    WHERE created_at >= DATE(NOW() - INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY tgl ASC
");

echo json_encode([
    'total_transaksi' => $total['total'],
    'grafik' => $sales->fetch_all(MYSQLI_ASSOC)
]);
