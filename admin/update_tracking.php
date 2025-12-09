<?php
session_start();
require '../config/db.php';

if($_SESSION['user']['role'] !== 'admin'){
    die("Forbidden");
}

$order_id = $_POST['order_id'];
$tracking = $_POST['tracking_no'];

$stmt = $conn->prepare("UPDATE orders SET tracking_no = ? WHERE id = ?");
$stmt->bind_param("si", $tracking, $order_id);
$stmt->execute();

echo json_encode(['success'=>true]);
