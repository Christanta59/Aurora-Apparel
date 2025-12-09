<?php
session_start();
include '../config/db.php';
header("Content-Type: application/json");

if(!isset($_SESSION['login']) || $_SESSION['user']['role'] !== 'admin'){
    echo json_encode(["success"=>false]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['id']);
$status = $data['status'];

$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);
$ok = $stmt->execute();

echo json_encode(["success"=>$ok]);
