<?php
include '../config/db.php';
$tracking = $_GET['tracking'];
$data = $conn->query("SELECT * FROM orders WHERE tracking_no='$tracking'")->fetch_assoc();
echo json_encode($data);
