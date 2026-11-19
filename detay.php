<?php
ob_start();
session_start();
require_once 'inc/db.php';
require_once 'inc/header.php';

// 1. ID KONTROLÜ
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int)$_GET['id'];
$message = "";

// 2. ÜRÜN VE GALERİ BİLGİLERİNİ ÇEK
$stmt = $pdo->prepare("SELECT products.*, categories.name as cat_name FROM products LEFT JOIN categories ON products.category_id = categories.id WHERE products.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo '<div class="container mx-auto mt-20 text-center"><h1 class="text-2xl font-bold text-slate-700">Ürün bulunamadı!</h1><a href="index.php" class="text-indigo-600 hover:underline mt-4 block">Mağazaya Dön</a></div>';
    require_once 'inc/footer.php';
    exit;
}

// Admin panelinden eklenen özellikleri çöz
$features = !empty($product['features']) ? json_decode($product['features'], true) : [];

// Galeri Resimleri
$stmtImg = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmtImg->execute([$product_id]);
$gallery = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

// 3. SATIN ALMA KONTROLÜ
$has_purchased = false;
if (isset($_SESSION['user_id'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$_SESSION['user_id'], $product_id]);
    if ($checkStmt->rowCount() > 0) $has_purchased = true;
}

// 4. YORUM KAYDETME
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $message = '<div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 border border-rose-100 font-medium">Yorum yapmak için giriş yapmalısınız.</div>';
    } elseif (!$has_purchased) {
        $message = '<div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 border border-rose-100 font-medium"><i class="fa-solid fa-lock mr-2"></i>Bu ürünü satın almadan yorum yapamazsınız.</div>';
    } else {
        $rating = (int)$_POST['rating'];
        $comment_text = trim($_POST['comment']);
        $user_id = $_SESSION['user_id'];
        
        $checkReview = $pdo->prepare("SELECT id FROM comments WHERE user_id = ? AND product_id = ?");
        $checkReview->execute([$user_id, $product_id]);
        
        if($checkReview->rowCount() > 0){
             $message = '<div class="bg-amber-50 text-amber-600 p-4 rounded-xl mb-6 border border-amber-100 font-medium">Zaten yorum yaptınız.</div>';
        } else {
            if ($rating > 0 && !empty($comment_text)) {
                $stmt = $pdo->prepare("INSERT INTO comments (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$product_id, $user_id, $rating, $comment_text]);
                $message = '<div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-6 border border-emerald-100 font-medium"><i class="fa-solid fa-check-circle mr-2"></i>Değerlendirmeniz yayınlandı!</div>';
            }
        }
    }
}

// 5. YORUMLAR VE İSTATİSTİKLER
$commentsQuery = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE product_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($commentsQuery);
$stmt->execute([$product_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$avgQuery = "SELECT AVG(rating) as avg_rate, COUNT(*) as total_review FROM comments WHERE product_id = ?";
$stmt = $pdo->prepare($avgQuery);
$stmt->execute([$product_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
$avg_rating = $stats['avg_rate'] ? round($stats['avg_rate'], 1) : 0;
$total_review = $stats['total_review'];
?>

<div class="bg-gray-50 min-h-screen pb-12">
    
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <nav class="flex text-sm font-medium text-slate-500">
                <a href="index.php" class="hover:text-indigo-600 transition">Anasayfa</a>
                <span class="mx-2 text-slate-300">/</span>
                <a href="market.php" class="hover:text-indigo-600 transition">Mağaza</a>
                <span class="mx-2 text-slate-300">/</span>
                <span class="text-slate-800 truncate"><?php echo htmlspecialchars($product['title']); ?></span>
            </nav>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <div class="lg:col-span-8 space-y-10">
                
                <div class="bg-white rounded-2xl p-2 shadow-sm border border-slate-200">
                    <div class="relative rounded-xl overflow-hidden bg-gray-100 aspect-video group cursor-zoom-in">
                        <img id="mainImage" src="<?php echo $product['image']; ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </div>
                    
                    <?php if(count($gallery) > 0): ?>
                    <div class="flex gap-3 mt-3 overflow-x-auto pb-2 px-1 custom-scrollbar">
                        <button onclick="changeImage('<?php echo $product['image']; ?>')" class="relative w-20 h-16 rounded-lg overflow-hidden border-2 border-indigo-600 flex-shrink-0 focus:outline-none transition-all hover:opacity-80">
                            <img src="<?php echo $product['image']; ?>" class="w-full h-full object-cover">
                        </button>
                        <?php foreach($gallery as $img): ?>
                        <button onclick="changeImage('<?php echo $img['image_path']; ?>')" class="relative w-20 h-16 rounded-lg overflow-hidden border-2 border-transparent hover:border-indigo-400 flex-shrink-0 focus:outline-none transition-all">
                            <img src="<?php echo $img['image_path']; ?>" class="w-full h-full object-cover">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ tab: 'desc' }">
                    <div class="flex border-b border-slate-100">
                        <button @click="tab = 'desc'" :class="{ 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/50': tab === 'desc', 'text-slate-500 hover:text-slate-700': tab !== 'desc' }" class="flex-1 py-4 text-sm font-bold transition-all">
                            Ürün Açıklaması
                        </button>
                        <button @click="tab = 'reviews'" :class="{ 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/50': tab === 'reviews', 'text-slate-500 hover:text-slate-700': tab !== 'reviews' }" class="flex-1 py-4 text-sm font-bold transition-all flex items-center justify-center gap-2">
                            Değerlendirmeler 
                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-xs"><?php echo $total_review; ?></span>
                        </button>
                    </div>

                    <div class="p-8">
                        <div x-show="tab === 'desc'" class="prose max-w-none text-slate-600 leading-relaxed">
                            <?php echo $product['description']; ?>
                        </div>

                        <div x-show="tab === 'reviews'" style="display: none;">
                            
                            <div class="flex items-center gap-6 mb-8 bg-slate-50 p-6 rounded-xl border border-slate-100">
                                <div class="text-center">
                                    <div class="text-5xl font-black text-slate-800"><?php echo $avg_rating; ?></div>
                                    <div class="flex text-yellow-400 text-sm justify-center my-2">
                                        <?php for($i=1;$i<=5;$i++) echo ($i<=$avg_rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star text-slate-300"></i>'; ?>
                                    </div>
                                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wide"><?php echo $total_review; ?> Yorum</div>
                                </div>
                                <div class="h-16 w-px bg-slate-200 hidden sm:block"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-slate-600 italic">"Bu ürün için yapılan tüm değerlendirmeler doğrulanmış satın alımlardan gelmektedir."</p>
                                </div>
                            </div>

                            <?php echo $message; ?>

                            <?php if($has_purchased): ?>
                                <div class="mb-10">
                                    <h3 class="font-bold text-slate-800 mb-4">Puan Ver</h3>
                                    <form action="" method="POST" class="space-y-4">
                                        <div class="flex flex-row-reverse justify-end gap-2 group w-fit">
                                            <?php for($i=5; $i>=1; $i--): ?>
                                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" class="hidden peer">
                                                <label for="star<?php echo $i; ?>" class="cursor-pointer text-slate-200 text-3xl transition-colors peer-checked:text-yellow-400 hover:text-yellow-400 peer-hover:text-yellow-400">
                                                    <i class="fa-solid fa-star"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                        
                                        <textarea name="comment" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition resize-none" placeholder="Ürün nasıldı? Düşüncelerini yaz..."></textarea>
                                        <button type="submit" name="submit_review" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-slate-800 transition">Yorumu Gönder</button>
                                    </form>
                                </div>
                            <?php elseif(isset($_SESSION['user_id'])): ?>
                                <div class="bg-blue-50 text-blue-700 p-4 rounded-xl text-sm font-medium mb-8 flex items-center gap-2">
                                    <i class="fa-solid fa-circle-info"></i> Bu ürünü değerlendirmek için satın almanız gerekmektedir.
                                </div>
                            <?php endif; ?>

                            <div class="space-y-6">
                                <?php if(count($comments) > 0): ?>
                                    <?php foreach($comments as $cmt): ?>
                                        <div class="flex gap-4 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center font-bold text-indigo-600 flex-shrink-0 shadow-sm">
                                                <?php echo strtoupper(substr($cmt['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-bold text-slate-800"><?php echo htmlspecialchars($cmt['username']); ?></h4>
                                                    <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold flex items-center gap-1"><i class="fa-solid fa-check"></i> Satın Aldı</span>
                                                </div>
                                                <div class="flex text-yellow-400 text-xs mb-2">
                                                    <?php for($i=0; $i<$cmt['rating']; $i++) echo '<i class="fa-solid fa-star"></i>'; ?>
                                                </div>
                                                <p class="text-slate-600 text-sm leading-relaxed"><?php echo htmlspecialchars($cmt['comment']); ?></p>
                                                <span class="text-xs text-slate-400 mt-2 block"><?php echo date("d F Y", strtotime($cmt['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-8 text-slate-400">Henüz değerlendirme yapılmamış.</div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-indigo-100 sticky top-24">
                    
                    <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full mb-4 inline-block">
                        <?php echo htmlspecialchars($product['cat_name']); ?>
                    </span>
                    
                    <h1 class="text-2xl font-extrabold text-slate-900 leading-tight mb-2"><?php echo htmlspecialchars($product['title']); ?></h1>
                    
                    <div class="flex items-center gap-2 mb-6">
                        <div class="flex text-yellow-400 text-sm">
                            <?php for($i=1;$i<=5;$i++) echo ($i<=$avg_rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star text-slate-300"></i>'; ?>
                        </div>
                        <span class="text-sm text-slate-500 font-medium">(<?php echo $avg_rating; ?>)</span>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 mb-6 border border-slate-100">
                        <div class="flex items-end gap-1">
                            <span class="text-4xl font-black text-slate-900 tracking-tight"><?php echo number_format($product['price'], 2); ?></span>
                            <span class="text-xl font-bold text-slate-500 mb-1.5">₺</span>
                        </div>
                        <div class="text-xs text-emerald-600 font-bold mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-check-circle"></i> Tek seferlik ödeme
                        </div>
                    </div>

                    <div class="space-y-6 mb-6">
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-bold text-lg shadow-xl shadow-indigo-200 transition transform active:scale-95 flex items-center justify-center gap-2 group">
                            <span>Sepete Ekle</span>
                            <i class="fa-solid fa-cart-shopping group-hover:translate-x-1 transition"></i>
                        </button>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <?php if(!empty($product['site_demo'])): ?>
                                <a href="<?php echo $product['site_demo']; ?>" target="_blank" class="flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-slate-100 font-bold text-slate-600 hover:border-indigo-600 hover:text-indigo-600 transition bg-white">
                                    <i class="fa-solid fa-desktop"></i> Site Demo
                                </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($product['admin_demo'])): ?>
                                <a href="<?php echo $product['admin_demo']; ?>" target="_blank" class="flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-slate-100 font-bold text-slate-600 hover:border-rose-600 hover:text-rose-600 transition bg-white">
                                    <i class="fa-solid fa-lock"></i> Admin Demo
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-4 pt-6 border-t border-slate-100">
                        <?php if(!empty($features)): ?>
                            <?php foreach($features as $feat): ?>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-50 text-indigo-600 flex items-center justify-center flex-shrink-0 mt-0.5 border border-slate-100">
                                        <i class="fa-solid <?php echo htmlspecialchars($feat['icon']); ?>"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($feat['title']); ?></h4>
                                        <p class="text-xs text-slate-500 leading-relaxed"><?php echo htmlspecialchars($feat['desc']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Anında Teslimat</h4>
                                    <p class="text-xs text-slate-500 leading-relaxed">Ödeme sonrası dosyanız anında panelinize düşer.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-code"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">%100 Açık Kaynak</h4>
                                    <p class="text-xs text-slate-500 leading-relaxed">Şifreli dosya yok, istediğiniz gibi düzenleyebilirsiniz.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-headset"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Ücretsiz Destek</h4>
                                    <p class="text-xs text-slate-500 leading-relaxed">Kurulum ve olası hatalarda 6 ay boyunca yanınızdayız.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function changeImage(src) {
        const mainImage = document.getElementById('mainImage');
        mainImage.style.opacity = '0.5';
        setTimeout(() => {
            mainImage.src = src;
            mainImage.style.opacity = '1';
        }, 150);
    }
</script>

<?php require_once 'inc/footer.php'; ob_end_flush(); ?>