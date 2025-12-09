<?php
include '../config/db.php';

$res = $conn->query("
    SELECT 
        id, 
        customer_name AS customer, 
        status, 
        tracking_no, 
        created_at
    FROM orders
    ORDER BY created_at DESC
");

echo json_encode($res->fetch_all(MYSQLI_ASSOC));
