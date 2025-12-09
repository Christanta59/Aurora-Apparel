<?php
session_start();
include "../../config/db.php";


if(!isset($_SESSION['login'])) {
    echo json_encode(['status'=>'error']);
    exit;
}

$cart_id = $_POST['cart_id'];
$stmt = $conn->prepare("DELETE FROM cart WHERE id=?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();

echo json_encode(['status'=>'success']);
?>
