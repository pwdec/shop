<?php
session_start();
require_once 'inc/db.php';

// JSON formatında cevap vereceğimizi belirtelim
header('Content-Type: application/json');

if (isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];

    // Sepet dizisi yoksa oluştur
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Ürün zaten var mı?
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id;
        $status = 'success';
        $message = 'Ürün sepete eklendi!';
    } else {
        $status = 'info';
        $message = 'Bu ürün zaten sepetinizde var.';
    }

    // Güncel sepet sayısını gönder
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'count' => count($_SESSION['cart'])
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Hata oluştu.']);
}
?>