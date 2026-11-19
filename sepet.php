<?php
ob_start();
session_start();
require_once 'inc/db.php';

// --- MANTIK KISMI ---

$coupon_message = "";
$coupon_status = "";

// 1. Kupon Uygula
if (isset($_POST['apply_coupon'])) {
    $code = strtoupper(trim($_POST['coupon_code']));
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        $_SESSION['coupon'] = [
            'code' => $coupon['code'],
            'rate' => $coupon['discount_rate']
        ];
        $coupon_message = 'Kupon başarıyla uygulandı! %' . $coupon['discount_rate'] . ' indirim kazandınız.';
        $coupon_status = "success";
    } else {
        $coupon_message = 'Girdiğiniz kupon kodu geçersiz veya süresi dolmuş.';
        $coupon_status = "error";
    }
}

// 2. Kupon Kaldır
if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['coupon']);
    header("Location: sepet.php");
    exit;
}

// Sepet Verileri
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products = [];
$subtotal = 0;

if (!empty($cart_items)) {
    $ids = implode(',', $cart_items);
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once 'inc/header.php'; 
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <div class="flex items-center gap-4 mb-8">
            <h1 class="text-3xl font-extrabold text-slate-800">Alışveriş Sepeti</h1>
            <span class="bg-slate-200 text-slate-600 text-xs font-bold px-3 py-1 rounded-full"><?php echo count($products); ?> Ürün</span>
        </div>

        <?php if (empty($products)): ?>
            
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-16 text-center">
                <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-basket-shopping text-4xl text-indigo-300"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Sepetinizde ürün bulunmuyor.</h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto">Hemen mağazamıza göz atın ve projeniz için en uygun scriptleri keşfedin.</p>
                <a href="index.php" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white transition-all duration-200 bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 transform hover:-translate-y-1">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Alışverişe Başla
                </a>
            </div>

        <?php else: ?>
            
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <div class="w-full lg:w-2/3">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="divide-y divide-slate-100">
                            <?php foreach ($products as $product): ?>
                                <?php $subtotal += $product['price']; ?>
                                <div class="p-6 flex flex-col sm:flex-row items-center gap-6 hover:bg-slate-50 transition group">
                                    
                                    <div class="w-full sm:w-24 h-24 flex-shrink-0 bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                                        <img class="w-full h-full object-cover" src="<?php echo $product['image'] ? $product['image'] : 'https://via.placeholder.com/150'; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    </div>

                                    <div class="flex-1 text-center sm:text-left">
                                        <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition">
                                            <a href="detay.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['title']); ?></a>
                                        </h3>
                                        <p class="text-sm text-slate-500 line-clamp-1"><?php echo strip_tags($product['description']); ?></p>
                                    </div>

                                    <div class="flex items-center gap-6">
                                        <span class="text-xl font-bold text-slate-900"><?php echo number_format($product['price'], 2); ?> ₺</span>
                                        <a href="sepet-sil.php?id=<?php echo $product['id']; ?>" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-400 hover:text-red-500 hover:bg-red-50 transition" title="Sepetten Kaldır">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="bg-slate-50 p-4 border-t border-slate-200 flex justify-between items-center">
                            <a href="index.php" class="text-slate-600 font-bold text-sm hover:text-indigo-600 flex items-center gap-2">
                                <i class="fa-solid fa-arrow-left"></i> Alışverişe Devam Et
                            </a>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/3 sticky top-24">
                    
                    <?php 
                        $discount_amount = 0;
                        if (isset($_SESSION['coupon'])) {
                            $rate = $_SESSION['coupon']['rate'];
                            $discount_amount = ($subtotal * $rate) / 100;
                        }
                        $total_price = $subtotal - $discount_amount;
                    ?>

                    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-6">Sipariş Özeti</h3>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-slate-600">
                                <span>Ara Toplam</span>
                                <span class="font-bold"><?php echo number_format($subtotal, 2); ?> ₺</span>
                            </div>

                            <?php if (isset($_SESSION['coupon'])): ?>
                                <div class="flex justify-between text-emerald-600 bg-emerald-50 p-2 rounded-lg">
                                    <span class="flex items-center gap-2">
                                        <i class="fa-solid fa-tag"></i> İndirim (<?php echo $_SESSION['coupon']['code']; ?>)
                                    </span>
                                    <span class="font-bold">-<?php echo number_format($discount_amount, 2); ?> ₺</span>
                                </div>
                                <div class="text-right">
                                    <a href="?remove_coupon=1" class="text-xs text-red-500 hover:underline font-bold">Kuponu Kaldır</a>
                                </div>
                            <?php endif; ?>

                            <div class="border-t border-slate-100 pt-3 mt-3 flex justify-between items-center">
                                <span class="text-lg font-bold text-slate-800">Toplam</span>
                                <span class="text-2xl font-extrabold text-indigo-600"><?php echo number_format($total_price, 2); ?> ₺</span>
                            </div>
                        </div>

                        <?php if (!isset($_SESSION['coupon'])): ?>
                            <div class="mb-6">
                                <form action="" method="POST" class="relative">
                                    <input type="text" name="coupon_code" placeholder="İndirim Kodu" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-4 pr-24 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition uppercase text-sm font-bold placeholder:font-normal">
                                    <button type="submit" name="apply_coupon" class="absolute right-2 top-2 bottom-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold px-4 rounded-lg transition">
                                        Uygula
                                    </button>
                                </form>
                                <?php if($coupon_message): ?>
                                    <p class="text-xs mt-2 font-bold <?php echo $coupon_status == 'success' ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                        <?php echo $coupon_message; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="odeme.php" method="POST">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-bold text-lg shadow-xl shadow-indigo-200 transition transform active:scale-95 flex items-center justify-center gap-2 group">
                                <span>Sepeti Onayla</span>
                                <i class="fa-solid fa-chevron-right text-sm group-hover:translate-x-1 transition"></i>
                            </button>
                        </form>
                        
                        <div class="mt-4 flex justify-center gap-4 text-slate-400 text-2xl">
                            <i class="fa-brands fa-cc-visa hover:text-indigo-600 transition"></i>
                            <i class="fa-brands fa-cc-mastercard hover:text-red-500 transition"></i>
                            <i class="fa-brands fa-cc-amex hover:text-blue-500 transition"></i>
                        </div>
                        
                        <p class="text-center text-xs text-slate-400 mt-4 flex items-center justify-center gap-1">
                            <i class="fa-solid fa-lock"></i> Güvenli Ödeme Altyapısı
                        </p>
                    </div>

                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php 
require_once 'inc/footer.php'; 
ob_end_flush(); 
?>