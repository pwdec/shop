<?php
ob_start();
session_start();
require_once 'inc/db.php';
require_once 'inc/header.php';

// Güvenlik: Sepet boşsa veya giriş yoksa anasayfaya at
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$user_id]);
$user = $user->fetch(PDO::FETCH_ASSOC);

// Ayarları Çek
$settings = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);

// Sepet Toplamını Hesapla
$cart_items = $_SESSION['cart'];
$ids = implode(',', $cart_items);
$products = $pdo->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);

$total_price = 0;
foreach ($products as $product) {
    $total_price += $product['price'];
}

// İndirim varsa düş
if (isset($_SESSION['coupon'])) {
    $total_price = $total_price - ($total_price * $_SESSION['coupon']['rate'] / 100);
}

// Sipariş Numarası Oluştur
$merchant_oid = time() . rand(100, 999); 

// #######################################################################################################
// SİMÜLASYON MODU KONTROLÜ (PAYTR HESABI OLMAYANLAR İÇİN)
// #######################################################################################################

$merchant_id    = $settings['paytr_merchant_id'];
$merchant_key   = $settings['paytr_merchant_key'];
$merchant_salt  = $settings['paytr_merchant_salt'];

// EĞER SİMÜLASYON İSE (Admin panelinden ID yerine 'test' yazıldıysa)
if ($merchant_id == 'test') {
    
    // Simülasyon Ödemesini İşle
    if (isset($_POST['simulate_payment'])) {
        
        // 1. Siparişi 'completed' (Başarılı) olarak kaydet
        foreach ($products as $product) {
            $stmt = $pdo->prepare("INSERT INTO orders (order_no, user_id, product_id, price, status, payment_status, paid_at) VALUES (?, ?, ?, ?, 'completed', 'completed', NOW())");
            $stmt->execute([$merchant_oid, $user_id, $product['id'], $product['price']]);
        }

        // 2. Başarılı sayfasına yönlendir
        header("Location: siparis-tamamla.php?oid=" . $merchant_oid);
        exit;
    }

    // Simülasyon Ekranını Göster
    ?>
    <div class="bg-slate-50 min-h-screen py-16 flex items-center justify-center">
        <div class="bg-white p-8 rounded-3xl shadow-xl border border-slate-200 max-w-md w-full relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-amber-400"></div>
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-flask"></i>
                </div>
                <h1 class="text-2xl font-black text-slate-800">Geliştirici Modu</h1>
                <p class="text-slate-500 text-sm mt-2">PayTR entegrasyonu simüle ediliyor. Gerçek para çekilmeyecektir.</p>
            </div>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-6 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Tutar:</span>
                    <span class="font-bold text-slate-800"><?php echo number_format($total_price, 2); ?> TL</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Sipariş No:</span>
                    <span class="font-mono text-slate-800"><?php echo $merchant_oid; ?></span>
                </div>
            </div>

            <form method="POST">
                <button type="submit" name="simulate_payment" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-slate-800 transition shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> Başarılı Ödeme Simüle Et
                </button>
            </form>
            
            <a href="sepet.php" class="block text-center mt-4 text-sm text-rose-500 font-bold hover:underline">İptal Et ve Dön</a>
        </div>
    </div>
    <?php
    require_once 'inc/footer.php';
    ob_end_flush();
    exit; // Kodun geri kalanı (PayTR API) çalışmasın
}

// #######################################################################################################
// GERÇEK PAYTR ENTEGRASYONU (ID 'test' değilse burası çalışır)
// #######################################################################################################

// 1. Kullanıcı Bilgilerini ve IP'yi Tanımla
$email = $user['email'];
$payment_amount = number_format($total_price * 100, 0, '', ''); 
$user_name = $user['username'];
$user_address = "Teslimat Adresi Yok (Dijital Ürün)";
$user_phone = !empty($user['phone']) ? $user['phone'] : "05555555555";

if( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
    $user_ip = $_SERVER["HTTP_CLIENT_IP"];
} elseif( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
    $user_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else {
    $user_ip = $_SERVER["REMOTE_ADDR"];
}

// 2. Sepet İçeriğini Hazırla
$user_basket = [];
foreach ($products as $p) {
    $user_basket[] = [$p['title'], $p['price'], 1]; 
}
$user_basket_json = json_encode($user_basket);
$user_basket_encoded = base64_encode($user_basket_json);

// Ayarlar
$debug_on = 1;
$test_mode = $settings['paytr_test_mode'];
$no_installment = 1; 
$max_installment = 0;
$currency = "TL";

$site_url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$merchant_ok_url = $site_url . "/siparis-tamamla.php?oid=" . $merchant_oid;
$merchant_fail_url = $site_url . "/odeme-basarisiz.php?oid=" . $merchant_oid;

// 3. Hash Oluşturma
$hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket_json . $no_installment . $max_installment . $currency . $test_mode;
$paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

// 4. Token İsteği Gönder
$post_vals = [
    'merchant_id' => $merchant_id,
    'user_ip' => $user_ip,
    'merchant_oid' => $merchant_oid,
    'email' => $email,
    'payment_amount' => $payment_amount,
    'paytr_token' => $paytr_token,
    'user_basket' => $user_basket_encoded,
    'debug_on' => $debug_on,
    'no_installment' => $no_installment,
    'max_installment' => $max_installment,
    'user_name' => $user_name,
    'user_address' => $user_address,
    'user_phone' => $user_phone,
    'merchant_ok_url' => $merchant_ok_url,
    'merchant_fail_url' => $merchant_fail_url,
    'timeout_limit' => 30,
    'currency' => $currency,
    'test_mode' => $test_mode
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$result = curl_exec($ch);
curl_close($ch);

$result = json_decode($result, 1);

if (isset($result['status']) && $result['status'] == 'success') {
    $token = $result['token'];
    
    // Gerçek işlemde siparişi 'pending' olarak kaydet (Callback bekleyeceğiz)
    foreach ($products as $product) {
        $stmt = $pdo->prepare("INSERT INTO orders (order_no, user_id, product_id, price, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$merchant_oid, $user_id, $product['id'], $product['price']]);
    }

} else {
    die("PAYTR API HATASI: " . ($result['reason'] ?? 'Bilinmeyen Hata'));
}
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
            <i class="fa-solid fa-lock text-indigo-600"></i> Güvenli Ödeme
        </h1>
        
        <div class="bg-white p-4 rounded-2xl shadow-lg border border-slate-200">
            <iframe src="https://www.paytr.com/odeme/guvenli/<?php echo $token; ?>" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%; height: 600px;"></iframe>
        </div>
    </div>
</div>

<script>iFrameResize({}, '#paytriframe');</script>
<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>

<?php require_once 'inc/footer.php'; ob_end_flush(); ?>