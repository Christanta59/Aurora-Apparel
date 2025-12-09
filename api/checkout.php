<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config/db.php';

// baca JSON
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if(!$input){
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

// cek login
if(!isset($_SESSION['login'])){
    echo json_encode(['status'=>'login','message'=>'User not logged in']);
    exit;
}

// validasi input wajib
$required = ['customer_name','customer_address','customer_phone','payment_method','courier','cart'];
foreach($required as $r){
    if(!isset($input[$r]) || $input[$r] === ''){
        echo json_encode(['success'=>false,'message'=>"Field {$r} wajib diisi"]);
        exit;
    }
}

$user_id = $_SESSION['user']['id'];

// ambil cart
$cart_items = $input['cart'];
if(!is_array($cart_items) || count($cart_items) === 0){
    echo json_encode(['success'=>false,'message'=>'Keranjang kosong']);
    exit;
}

file_put_contents("debug_cart.txt", print_r($cart_items, true));

// hitung total
$subtotal = 0;
foreach($cart_items as $it){
    $subtotal += $it['price'] * $it['qty'];
}

// shipping
$courier = strtolower($input['courier']);
if(strpos($courier,'jne')!==false) $shipping = 15000;
elseif(strpos($courier,'jnt')!==false) $shipping = 12000;
elseif(strpos($courier,'pos')!==false) $shipping = 10000;
else $shipping = 15000;

$total = $subtotal + $shipping;

// transaksi
// transaksi
$conn->begin_transaction();

try {

    // === AUTO GENERATE TRACKING NUMBER ===
    $tracking_no = "TRK" . date("YmdHis") . rand(100,999);

    // insert ke orders
    $stmt = $conn->prepare("
        INSERT INTO orders
        (customer_name, customer_address, customer_phone, payment_method, courier, total_price, status, tracking_no)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
    ");

    $stmt->bind_param(
        "sssssis",
        $input['customer_name'],
        $input['customer_address'],
        $input['customer_phone'],
        $input['payment_method'],
        $input['courier'],
        $total,
        $tracking_no
    );


    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // insert items (sesuai tabel kamu!)
    $itemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    foreach($cart_items as $it){
    $pid = (int)$it['product_id'];
    $qty = (int)$it['qty'];
    $price = (int)$it['price'];

    $itemStmt->bind_param("iiii", $order_id, $pid, $qty, $price);
    $itemStmt->execute();
}

    $itemStmt->close();

    // hapus cart user
    $del = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $del->bind_param("i", $user_id);
    $del->execute();
    $del->close();

    $conn->commit();

    echo json_encode(['success'=>true,'order_id'=>$order_id]);

} catch(Exception $e){
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>'Gagal memproses order: '.$e->getMessage()]);
}
