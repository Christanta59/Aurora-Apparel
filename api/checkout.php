<?php
// api/checkout.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config/db.php';

// baca JSON
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

// jika request kosong, kembalikan error
if(!$input){
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

// jika user tidak login, minta login
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

// ambil user id dari session
$user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

// ambil cart: di client kita terima lewat payload (cart array), tapi kita tetap bisa gunakan cart DB jika perlu
$cart_items = $input['cart'];
if(!is_array($cart_items) || count($cart_items) === 0){
    echo json_encode(['success'=>false,'message' => 'Keranjang kosong']);
    exit;
}

// hitung subtotal
$subtotal = 0.0;
foreach($cart_items as $it){
    $price = isset($it['price']) ? (float)$it['price'] : 0;
    $qty = isset($it['qty']) ? (int)$it['qty'] : (isset($it['quantity']) ? (int)$it['quantity'] : 1);
    $subtotal += $price * $qty;
}

// shipping cost sederhana (bisa disesuaikan)
$courier = strtolower($input['courier']);
$shipping = 0.0;
if(strpos($courier,'jne')!==false) $shipping = 15000;
elseif(strpos($courier,'jnt')!==false) $shipping = 12000;
elseif(strpos($courier,'pos')!==false) $shipping = 10000;
else $shipping = 15000;

$total = $subtotal + $shipping;

// simpan order ke DB (transaction)
$conn->begin_transaction();
try {
    $status = 'pending';
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_address, customer_phone, payment_method, courier, subtotal, shipping_cost, total, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())");
    if(!$stmt) throw new Exception('Prepare orders failed: '.$conn->error);
    $stmt->bind_param('ssssddds', 
        $input['customer_name'],
        $input['customer_address'],
        $input['customer_phone'],
        $input['payment_method'],
        $input['courier'],
        $subtotal,
        $shipping,
        $total
    );
    $stmt->execute();
    if($stmt->errno) throw new Exception('Insert order failed: '.$stmt->error);
    $order_id = $conn->insert_id;
    $stmt->close();

    // insert order items
    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal, created_at) VALUES (?,?,?,?,?,?,NOW())");
    if(!$itemStmt) throw new Exception('Prepare order_items failed: '.$conn->error);

    // optional: update product stock if table products exists and has id column
    $updateStockStmt = null;
    $res = $conn->query("SHOW TABLES LIKE 'products'");
    if($res && $res->num_rows > 0){
        // try prepare update (best-effort)
        $updateStockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        if(!$updateStockStmt) $updateStockStmt = null;
    }

    foreach($cart_items as $it){
        $product_id = isset($it['product_id']) ? (int)$it['product_id'] : (isset($it['productId']) ? (int)$it['productId'] : 0);
        $name = isset($it['name']) ? $it['name'] : (isset($it['product_name']) ? $it['product_name'] : 'Product');
        $qty = isset($it['qty']) ? (int)$it['qty'] : (isset($it['quantity']) ? (int)$it['quantity'] : 1);
        $price = isset($it['price']) ? (float)$it['price'] : 0;
        $line = $price * $qty;

        $itemStmt->bind_param('iissdd', $order_id, $product_id, $name, $qty, $price, $line);
        $itemStmt->execute();
        if($itemStmt->errno) throw new Exception('Insert item failed: '.$itemStmt->error);
        $itemStmt->store_result();

        if($updateStockStmt && $product_id){
            $updateStockStmt->bind_param('ii', $qty, $product_id);
            $updateStockStmt->execute();
            // ignore potential stock errors (you can add checks)
        }
    }
    if($itemStmt) $itemStmt->close();
    if($updateStockStmt) $updateStockStmt->close();

    // kosongkan cart server-side jika kamu menyimpan cart di DB (cart table name: cart)
    if($user_id){
        $del = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        if($del){
            $del->bind_param('i', $user_id);
            $del->execute();
            $del->close();
        }
    }

    $conn->commit();
    echo json_encode(['success'=>true,'order_id'=>$order_id]);

} catch(Exception $e){
    $conn->rollback();
    error_log('Checkout error: '.$e->getMessage());
    echo json_encode(['success'=>false,'message'=>'Gagal memproses order: '.$e->getMessage()]);
}
