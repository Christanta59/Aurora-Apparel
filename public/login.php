<?php
session_start();
if(isset($_SESSION['login'])){
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Aurora</title>
  
  <!-- Google Fonts Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="assets/style.css">
  <style>
    /* Tambahan khusus login page */
    body.login-page {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: linear-gradient(120deg, rgba(236,72,153,0.08), rgba(139,92,246,0.05));
    }

    .login-card {
      background: white;
      padding: 40px 30px;
      border-radius: 16px;
      box-shadow: 0 16px 40px rgba(31,41,51,0.08);
      width: 100%;
      max-width: 400px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      text-align: center;
    }

    .login-card h2 {
      margin-bottom: 20px;
      color: var(--primary);
      font-weight: 600;
    }

    .login-card input {
      width: 100%;
      padding: 12px 15px;
      border-radius: 10px;
      border: 1px solid #e6e9ee;
      font-size: 14px;
      transition: all 0.2s;
    }

    .login-card input:focus {
      border-color: var(--secondary);
      outline: none;
      box-shadow: 0 0 6px rgba(139,92,246,0.2);
    }

    .login-card .btn {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.2s;
    }

    .login-card .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(236,72,153,0.2);
    }

    @media (max-width: 480px) {
      .login-card {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body class="login-page">

  <div class="login-card fade-in">
    <h2>Login Aurora</h2>
    <form method="POST" action="../api/login.php">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" class="btn">Login</button>
    </form>
  </div>

</body>
</html>
