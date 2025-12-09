<?php
session_start();
include "../../config/db.php";

if(!isset($_SESSION['login'])){
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT 
    cart.id AS cart_id,
    cart.product_id,
    products.name,
    products.price,
    cart.qty
FROM cart
JOIN products ON cart.product_id = products.id
WHERE cart.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = [
    'id'         => $row['cart_id'],      // untuk update qty/delete
    'product_id' => $row['product_id'],
    'title'      => $row['name'],         // biar cocok dengan JS
    'price'      => $row['price'],
    'qty'        => $row['qty']
];
}

echo json_encode($data);
?>
