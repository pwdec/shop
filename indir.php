<?php
session_start();
require_once 'inc/db.php';

// 1. Giriş Kontrolü
if (!isset($_SESSION['user_id'])) {
    die("Lütfen giriş yapın.");
}

// 2. ID Kontrolü
if (!isset($_GET['id'])) {
    die("Dosya belirtilmedi.");
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_GET['id'];

// 3. SATIN ALMA KONTROLÜ (En Önemli Kısım)
// Kullanıcı bu ürünü sipariş etmiş mi?
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$order = $stmt->fetch();

if (!$order) {
    // Eğer admin değilse ve satın almamışsa engelle
    // (Admin kendi ürününü indirebilsin diye opsiyonel admin kontrolü eklenebilir ama şimdilik gerek yok)
    die("HATA: Bu ürünü satın almadığınız için indiremezsiniz!");
}

// 4. Dosya Yolunu Bulma
// Ürün bilgilerini çekip dosya yolunu alalım
$prodStmt = $pdo->prepare("SELECT download_link, title FROM products WHERE id = ?");
$prodStmt->execute([$product_id]);
$product = $prodStmt->fetch();

if (!$product || empty($product['download_link'])) {
    die("Dosya bulunamadı.");
}

$file_path = $product['download_link']; // Veritabanındaki yol: uploads/files/dosya.zip

// 5. Dosyayı Sunucu Üzerinden Gönderme
if (file_exists($file_path)) {
    // Dosya indirme başlıklarını gönder
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    
    // Dosyayı oku ve kullanıcıya gönder
    readfile($file_path);
    exit;
} else {
    die("Dosya sunucuda bulunamadı (Silinmiş olabilir).");
}
?>