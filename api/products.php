<?php
include '../config/db.php';
$data = $conn->query("SELECT * FROM products");
echo json_encode($data->fetch_all(MYSQLI_ASSOC));
