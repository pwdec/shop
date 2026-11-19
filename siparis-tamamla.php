<?php
ob_start();
session_start();
require_once 'inc/db.php';
require_once 'inc/header.php';

// URL'den sipariş nosunu al (PayTR buraya yönlendirecek)
$order_no = $_GET['oid'] ?? '';

if (!$order_no) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// Siparişi Kontrol Et
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_no = ? AND user_id = ?");
$stmt->execute([$order_no, $_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    echo "Sipariş bulunamadı.";
    exit;
}

// Sepeti Temizle (Ödeme başarılı olduysa)
unset($_SESSION['cart']);
unset($_SESSION['coupon']);
?>

<div class="bg-gray-50 min-h-screen py-16">
    <div class="container mx-auto px-4 max-w-4xl text-center">
        
        <div class="bg-white rounded-3xl shadow-xl p-12 border border-slate-100">
            <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-check text-5xl text-emerald-500"></i>
            </div>
            
            <h1 class="text-3xl font-extrabold text-slate-800 mb-2">Ödeme Başarılı! 🎉</h1>
            <p class="text-slate-500 mb-6">Sipariş numaranız: <strong class="text-indigo-600">#<?php echo htmlspecialchars($order_no); ?></strong></p>
            
            <p class="text-slate-600 mb-8">
                Satın alma işleminiz onaylandı. Dosyalarınızı profil sayfanızdan indirebilirsiniz.
            </p>

            <a href="profil.php" class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                Dosyalarımı Görüntüle
            </a>
        </div>

    </div>
</div>

<?php require_once 'inc/footer.php'; ?>