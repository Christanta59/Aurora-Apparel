<?php
include '../config/db.php';
$res = $conn->query("SELECT id, customer, status, tracking_no, created_at FROM orders ORDER BY created_at DESC LIMIT 10");
echo json_encode($res->fetch_all(MYSQLI_ASSOC));
