<?php
// Bu dosyaya sadece PayTR erişir, ekrana bir şey yazdırmaz.
require_once '../inc/db.php';

// Ayarları Çek
$settings = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);

$merchant_key = $settings['paytr_merchant_key'];
$merchant_salt = $settings['paytr_merchant_salt'];

// PayTR'dan gelen POST verileri
$post = $_POST;

// Hash Kontrolü (Güvenlik)
$hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'], $merchant_key, true));

if ($hash != $post['hash']) {
    die('PAYTR notification failed: bad hash');
}

// Ödeme Durumu Kontrolü
if ($post['status'] == 'success') {
    // ÖDEME BAŞARILI!
    
    // Sipariş numarasını al (merchant_oid)
    $order_no = $post['merchant_oid'];
    
    // Veritabanında bu sipariş numarasını bul ve durumunu 'completed' yap
    $stmt = $pdo->prepare("UPDATE orders SET status = 'completed', paid_at = NOW() WHERE order_no = ?");
    $stmt->execute([$order_no]);

} else {
    // ÖDEME BAŞARISIZ
    // İstersen veritabanında 'failed' olarak işaretle
    $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE order_no = ?");
    $stmt->execute([$post['merchant_oid']]);
}

echo "OK"; // PayTR'a "Ben mesajı aldım" diyoruz.
exit;
?>