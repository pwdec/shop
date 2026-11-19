<?php
session_start();

// Eğer ürün ID'si gelmediyse anasayfaya dön
if (!isset($_POST['product_id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_POST['product_id'];

// Sepet dizisi yoksa oluştur
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Dijital ürün olduğu için aynı üründen 10 tane eklemeye gerek yok.
// Eğer ürün zaten sepette yoksa ekle.
if (!in_array($product_id, $_SESSION['cart'])) {
    $_SESSION['cart'][] = $product_id;
}

// Sepet sayfasına yönlendir
header("Location: sepet.php");
exit;
?>