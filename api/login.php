<?php
session_start();
include "../config/db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$user = $conn->query("SELECT * FROM users 
WHERE email='$email' AND password='$password'")->fetch_assoc();

if($user){
  $_SESSION['login'] = true;
  $_SESSION['user']  = $user;

  // ✅ ADMIN MASUK DASHBOARD
  if($user['role']=="admin"){
    header("Location: ../admin/dashboard.php");
  } 
  // ✅ USER MASUK TOKO
  else {
    header("Location: ../public/index.php");
  }

} else {
  echo "Login gagal";
}
