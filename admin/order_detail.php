<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['login']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Order ID tidak ditemukan");
}

$order_id = (int)$_GET['id'];

// ambil data order
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$order) die("Order tidak ditemukan");

// ambil item
$items = $conn->query("
    SELECT oi.*, p.name AS product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Detail Order #<?= $order_id ?></title>
<link rel="stylesheet" href="../public/assets/style.css">
<style>
.container { padding:20px; }
.card { background:#fff;padding:20px;border-radius:10px;margin-bottom:20px; }
table { width:100%; border-collapse:collapse; margin-top:10px;}
table th, table td { padding:8px; border:1px solid #ddd; text-align:left; }
input, select { padding:8px; width:100%; margin-top:5px;}
button { padding:10px 20px; margin-top:10px; cursor:pointer; }
</style>
</head>
<body>

<div class="container">

<h2>Detail Order #<?= $order_id ?></h2>

<div class="card">
    <h3>Informasi Customer</h3>
    <p><b>Nama:</b> <?= htmlspecialchars($order['customer_name']) ?></p>
    <p><b>Alamat:</b> <?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
    <p><b>Telepon:</b> <?= htmlspecialchars($order['customer_phone']) ?></p>
</div>

<div class="card">
    <h3>Detail Pesanan</h3>
    <p><b>Metode Pembayaran:</b> <?= $order['payment_method'] ?></p>
    <p><b>Kurir:</b> <?= $order['courier'] ?></p>
    <p><b>Status:</b> <?= $order['status'] ?></p>
    <p><b>Tracking No:</b> <?= $order['tracking_no'] ?: '-' ?></p>
    <p><b>Total:</b> Rp <?= number_format($order['total_price'],0,',','.') ?></p>
</div>

<div class="card">
    <h3>Item Pesanan</h3>
    <table>
        <tr>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga</th>
        </tr>
        <?php foreach($items as $it): ?>
        <tr>
            <td><?= htmlspecialchars($it['product_name']) ?></td>
            <td><?= $it['quantity'] ?></td>
            <td>Rp <?= number_format($it['price'],0,',','.') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h3>Update Status & Tracking</h3>
    <form method="POST" action="../api/update_order.php">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">

        <label>Status:</label>
        <select name="status">
            <option value="On progress" <?= $order['status']=='On progress'?'selected':'' ?>>On progress</option>
            <option value="Shipped" <?= $order['status']=='Shipped'?'selected':'' ?>>Shipped</option>
            <option value="Complete" <?= $order['status']=='Complete'?'selected':'' ?>>Complete</option>
        </select>

        <label>Tracking Number:</label>
        <input type="text" name="tracking_no" value="<?= htmlspecialchars($order['tracking_no']) ?>">

        <button type="submit">Update</button>
    </form>
</div>

</div>
</body>
</html>
