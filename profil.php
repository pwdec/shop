<?php
ob_start();
session_start();
require_once 'inc/db.php';

// Güvenlik: Giriş yapılmamışsa yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Kullanıcı Bilgilerini Çek
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmtUser->execute(['id' => $user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// 2. Siparişleri Çek
$stmtOrders = $pdo->prepare("
    SELECT o.*, p.title as product_title, p.image as product_image
    FROM orders o 
    LEFT JOIN products p ON o.product_id = p.id 
    WHERE o.user_id = :uid 
    ORDER BY o.created_at DESC
");
$stmtOrders->execute(['uid' => $user_id]);
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

// 3. İstatistikleri Hesapla
$toplam_harcama = 0;
$toplam_siparis = count($orders);

foreach ($orders as $order) {
    $toplam_harcama += $order['price']; // DÜZELTİLDİ: price
}

include 'inc/header.php';
?>

<div class="max-w-7xl mx-auto mt-10 mb-20 px-4">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                <div class="p-6 bg-slate-900 text-white text-center">
                    <div class="w-16 h-16 bg-white text-slate-900 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-3 border-4 border-indigo-500">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h2 class="text-lg font-bold"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-slate-400 text-xs">Üye Hesabı</p>
                </div>
                
                <nav class="p-3 space-y-1">
                    <a href="profil.php" class="block px-4 py-3 bg-indigo-50 text-indigo-700 font-bold rounded-xl flex items-center gap-3">
                        <i class="fa-solid fa-box-open w-5"></i> Siparişlerim
                    </a>
                    <a href="hesap-ayarlari.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-user-gear w-5"></i> Hesap Ayarları
                    </a>
                    <a href="destek.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-headset w-5"></i> Destek Talepleri
                    </a>
                    <?php if($_SESSION['role'] == 'admin'): ?>
                        <a href="admin/index.php" class="block px-4 py-3 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition flex items-center gap-3">
                            <i class="fa-solid fa-gauge-high w-5"></i> Yönetici Paneli
                        </a>
                    <?php endif; ?>
                    <div class="border-t border-gray-100 my-2"></div>
                    <a href="cikis.php" class="block px-4 py-3 text-red-500 hover:bg-red-50 hover:text-red-700 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-right-from-bracket w-5"></i> Çıkış Yap
                    </a>
                </nav>
            </div>
        </div>

        <div class="w-full md:w-3/4">
            
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-indigo-600"></i> Siparişlerim ve Özet
            </h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Toplam Harcama</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($toplam_harcama, 2); ?> ₺</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Toplam Sipariş</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $toplam_siparis; ?> Adet</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">Sipariş Geçmişi</h3>
                </div>
                
                <?php if (count($orders) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-xs tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Ürün</th>
                                    <th class="px-6 py-4">Sipariş No</th>
                                    <th class="px-6 py-4">Tutar</th>
                                    <th class="px-6 py-4">Tarih</th>
                                    <th class="px-6 py-4 text-right">Durum & İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($orders as $order): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded bg-gray-100 flex-shrink-0 overflow-hidden">
                                                    <?php if(!empty($order['product_image'])): ?>
                                                        <img src="<?php echo $order['product_image']; ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-code w-full h-full flex items-center justify-center text-gray-400"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="font-bold text-gray-700"><?php echo htmlspecialchars($order['product_title'] ?? 'Silinmiş Ürün'); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-gray-500">#<?php echo htmlspecialchars($order['order_no']); ?></td>
                                        <td class="px-6 py-4 font-bold text-emerald-600"><?php echo number_format($order['price'], 2); ?> ₺</td>
                                        <td class="px-6 py-4 text-gray-500"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex flex-col items-end gap-1">
                                                <?php 
                                                if($order['status'] == 'completed') {
                                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Tamamlandı</span>';
                                                } elseif($order['status'] == 'pending') {
                                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Bekliyor</span>';
                                                } else {
                                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">İptal</span>';
                                                }
                                                ?>
                                                <?php if($order['status'] == 'completed'): ?>
                                                    <a href="indir.php?id=<?php echo $order['product_id']; ?>" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold hover:underline mt-1">
                                                        <i class="fa-solid fa-download"></i> Dosyayı İndir
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-10 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                            <i class="fa-solid fa-basket-shopping text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700">Henüz siparişiniz yok.</h3>
                        <p class="text-gray-500 text-sm mb-4">Mağazamızdaki harika yazılımlara göz atmak ister misin?</p>
                        <a href="index.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 transition">Alışverişe Başla</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<?php 
require_once 'inc/footer.php'; 
ob_end_flush();
?>