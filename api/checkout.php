<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['login'])){
  echo json_encode(["status"=>"login"]);
  exit;
}

$user_id   = $_SESSION['user']['id'];
$customer  = $_SESSION['user']['name'];

$items = $conn->query("
  SELECT c.*, p.price
  FROM carts c
  JOIN products p ON c.sku=p.sku
  WHERE c.user_id=$user_id
");

if($items->num_rows == 0){
  echo json_encode(["status"=>"empty"]);
  exit;
}

// Generate tracking
$tracking = "AUR-".substr(time(), -6);

// Insert order
$conn->query("
  INSERT INTO orders (customer, status, tracking_no)
  VALUES ('$customer','Dikirim','$tracking')
");

$order_id = $conn->insert_id;

// Insert order items + kurangi stok
foreach($items as $row){
  $sku = $row['sku'];
  $qty = $row['qty'];
  $price = $row['price'];

  $conn->query("
    INSERT INTO order_items (order_id, sku, qty, price)
    VALUES ($order_id, '$sku', $qty, $price)
  ");

  $conn->query("
    UPDATE products SET stock = stock - $qty WHERE sku='$sku'
  ");
}

// Kosongkan cart user
$conn->query("DELETE FROM carts WHERE user_id=$user_id");

echo json_encode([
  "status" => "success",
  "tracking" => $tracking
]);
