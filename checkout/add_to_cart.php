<?php
session_start();
include_once '../classes/Database.php';
include_once '../classes/Product.php';

// Fungsi untuk mengirim respons JSON
function sendJsonResponse($status, $message, $cartItemCount) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'cartItemCount' => $cartItemCount]);
    exit;
}

// Fungsi untuk menghitung jumlah item di keranjang
function getCartItemCount() {
    $count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $action = isset($_GET['action']) ? $_GET['action'] : 'add'; // Set default action to 'add'

    // Validasi ID produk
    if (!is_numeric($product_id)) {
        sendJsonResponse('error', 'ID produk tidak valid!', getCartItemCount());
    }

    $database = new Database();
    $db = $database->getConnection();

    $product = new Product($db);
    $product->id = $product_id;
    $product_info = $product->readOne();

    if ($product_info) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        if ($action == 'add') {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = array(
                    'id' => $product_info['id'],
                    'name' => $product_info['name'],
                    'price' => $product_info['price'],
                    'image' => $product_info['image'],
                    'quantity' => 1
                );
            }
            sendJsonResponse('success', 'Produk berhasil ditambahkan ke keranjang!', getCartItemCount());
        } elseif ($action == 'remove') {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']--;
                if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            sendJsonResponse('success', 'Produk berhasil dikurangi dari keranjang!', getCartItemCount());
        } elseif ($action == 'remove_all') {
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            sendJsonResponse('success', 'Produk berhasil dihapus dari keranjang!', getCartItemCount());
        } else {
            sendJsonResponse('error', 'Aksi tidak valid!', getCartItemCount());
        }
    } else {
        sendJsonResponse('error', 'Produk tidak ditemukan di database!', getCartItemCount());
    }
} else {
    sendJsonResponse('error', 'ID produk atau aksi tidak valid!', getCartItemCount());
}
?>