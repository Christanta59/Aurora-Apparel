<?php
header("Content-Type: application/json");
include "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "Invalid data"]);
    exit;
}

// Ambil data input
$name = $data["name"];
$address = $data["address"];
$phone = $data["phone"];
$payment = $data["payment_method"];
$courier = $data["courier"];
$items = $data["items"];
$total = $data["total_price"];

// 1. Insert ke tabel orders
$stmt = $conn->prepare("
    INSERT INTO orders (name, address, phone, payment_method, courier, total_price)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("sssssi", $name, $address, $phone, $payment, $courier, $total);
$stmt->execute();

$order_id = $conn->insert_id;

// 2. Insert item pesanan
$stmt_item = $conn->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, price)
    VALUES (?, ?, ?, ?)
");

foreach ($items as $item) {
    $stmt_item->bind_param("iiii", 
        $order_id,
        $item["id"],
        $item["quantity"],
        $item["price"]
    );
    $stmt_item->execute();
}

echo json_encode(["success" => true, "order_id" => $order_id]);
?>
