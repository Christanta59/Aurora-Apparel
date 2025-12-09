<?php
session_start();
include "../../config/db.php";


if(!isset($_SESSION['login'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user']['id'];

$query = "SELECT c.id as cart_id, p.id as product_id, p.name, p.price, p.image, c.qty 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($cart);
?>
