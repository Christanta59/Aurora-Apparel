<?php
session_start();
if(isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
?>

<link rel="stylesheet" href="assets/style.css">

<div class="container">
  <div class="card">
    <h2>Login Aurora</h2>

    <form method="POST" action="../api/login.php">
      <input name="email" placeholder="Email" required style="padding:10px;width:100%;margin:10px 0;">
      <input name="password" type="password" placeholder="Password" required style="padding:10px;width:100%;margin:10px 0;">
      <button class="btn">Login</button>
    </form>
  </div>
</div>
