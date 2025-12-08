<?php
session_start();
$customer = $_SESSION['user']['name'];
if(!isset($_SESSION['login'])){
  echo json_encode(["status"=>"login"]);
  exit;
}

include '../config/db.php';

$customer = $_POST['customer'];
$sku = $_POST['sku'];
$qty = $_POST['qty'];

$product = $conn->query("SELECT * FROM products WHERE sku='$sku'")->fetch_assoc();

if ($product['stock'] < $qty) {
  echo json_encode(["status"=>"error","msg"=>"Stok habis"]);
  exit;
}

// potong stok
$conn->query("UPDATE products SET stock=stock-$qty WHERE sku='$sku'");

// buat order
$tracking = "AUTO".time();
$conn->query("INSERT INTO orders (customer,status,tracking_no) 
VALUES ('$customer','Dikirim','$tracking')");
$order_id = $conn->insert_id;

$conn->query("INSERT INTO order_items (order_id,sku,qty,price)
VALUES ($order_id,'$sku',$qty,{$product['price']})");

echo json_encode([
  "status"=>"success",
  "tracking"=>$tracking
]);
