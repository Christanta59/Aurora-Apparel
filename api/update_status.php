<?php
header("Content-Type: application/json");
require_once "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if(!$id || !$status){
    echo json_encode(["success"=>false, "msg"=>"Invalid input"]);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);

if($stmt->execute()){
    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["success"=>false]);
}
