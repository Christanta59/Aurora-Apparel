<?php
session_start();
include "../../config/db.php";

// Cek login
if(!isset($_SESSION['login'])){
    echo json_encode(['status' => 'error', 'message' => 'User belum login']);
    exit;
}

// Validasi POST
if(!isset($_POST['product_id'])){
    echo json_encode(['status' => 'error', 'message' => 'product_id tidak dikirim']);
    exit;
}

$user_id    = $_SESSION['user']['id'];
$product_id = intval($_POST['product_id']);
$qty        = isset($_POST['qty']) ? intval($_POST['qty']) : 1;

if ($product_id <= 0){
    echo json_encode(['status'=>'error','message'=>'Product ID tidak valid']);
    exit;
}

// Cek apakah produk sudah ada di cart
$stmt = $conn->prepare("SELECT id, qty FROM cart WHERE user_id=? AND product_id=?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $newQty = $row['qty'] + $qty;

    $stmt = $conn->prepare("UPDATE cart SET qty=? WHERE id=?");
    $stmt->bind_param("ii", $newQty, $row['id']);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, qty) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $qty);
    $stmt->execute();
}

echo json_encode(['status' => 'success', 'message' => 'Produk ditambahkan ke keranjang']);
?>
