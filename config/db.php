<?php
$conn = new mysqli("localhost", "root", "", "aurora");
if ($conn->connect_error) {
  die("DB Error: " . $conn->connect_error);
}
?>
