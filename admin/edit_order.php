<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['user']['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

include "../config.php";

if (!isset($_GET['id'])) {
    die("Order ID tidak ditemukan");
}

$id = intval($_GET['id']);

// Ambil data order
$q = $conn->query("SELECT * FROM orders WHERE id = $id");
$order = $q->fetch_assoc();

if (!$order) {
    die("Order tidak ditemukan");
}

// Jika form submit
if (isset($_POST['update'])) {
    $status = $_POST['status'];
    $tracking = $_POST['tracking_no'];

    $stmt = $conn->prepare("UPDATE orders SET status=?, tracking_no=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $tracking, $id);
    $stmt->execute();

    header("Location: orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Order</title>

<style>
    body { font-family: Arial, sans-serif; padding:20px; background:#f1f1f1; }
    form { background:white; padding:20px; border-radius:8px; width:350px; }
    input, select { width:100%; padding:8px; margin-bottom:15px; }
    button { padding:10px 20px; background:#0275d8; color:white; border:none; cursor:pointer; }
</style>

</head>
<body>

<h2>Edit Order #<?php echo $order['id']; ?></h2>

<form method="post">

    <label>Status:</label>
    <select name="status">
        <option value="pending"    <?php if($order['status']=="pending") echo "selected"; ?>>Pending</option>
        <option value="processing" <?php if($order['status']=="processing") echo "selected"; ?>>Processing</option>
        <option value="shipped"    <?php if($order['status']=="shipped") echo "selected"; ?>>Shipped</option>
    </select>

    <label>No Resi:</label>
    <input type="text" name="tracking_no" value="<?php echo $order['tracking_no']; ?>">

    <button type="submit" name="update">Simpan</button>
</form>

</body>
</html>
