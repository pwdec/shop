<?php
require_once 'inc/db.php';
require_once 'inc/header.php';

// KATEGORİLERİ ÇEK
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// FİLTRELEME MANTIĞI
$whereClause = [];
$params = [];
$active_cat = isset($_GET['kategori']) ? $_GET['kategori'] : null;
$search_term = isset($_GET['q']) ? trim($_GET['q']) : null;

if ($active_cat) {
    $whereClause[] = "category_id = ?";
    $params[] = $active_cat;
}

if ($search_term) {
    $whereClause[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}

// SQL SORGUSU
$sql = "SELECT products.*, (SELECT AVG(rating) FROM comments WHERE product_id = products.id) as avg_rating FROM products";
if (count($whereClause) > 0) $sql .= " WHERE " . implode(" AND ", $whereClause);
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-slate-50 min-h-screen relative overflow-hidden">
    
    <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-indigo-50/50 to-transparent pointer-events-none"></div>
    
    <div class="container mx-auto px-4 py-10 relative z-10">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight mb-2">Mağaza</h1>
                <p class="text-slate-500 font-medium text-lg">En yeni scriptler, temalar ve dijital ürünler.</p>
            </div>
            <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200 text-sm font-bold text-slate-600 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <?php echo count($products); ?> Ürün Listeleniyor
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <div class="w-full lg:w-72 flex-shrink-0 lg:sticky lg:top-24 z-20">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    
                    <div class="mb-8">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Arama</h3>
                        <form action="market.php" method="GET" class="relative group">
                            <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Yazılım ara..." 
                                   class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl py-3 pl-10 pr-4 text-sm font-semibold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all placeholder:font-normal">
                            <i class="fa-solid fa-search absolute left-3.5 top-3.5 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                        </form>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Kategoriler</h3>
                        <div class="space-y-1">
                            <a href="market.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-bold transition-all <?php echo !$active_cat ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                                <span>Tümü</span>
                                <?php if(!$active_cat): ?><i class="fa-solid fa-check text-xs"></i><?php endif; ?>
                            </a>
                            
                            <?php foreach ($categories as $cat): 
                                $isActive = $active_cat == $cat['id'];
                            ?>
                                <a href="market.php?kategori=<?php echo $cat['id']; ?>" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-bold transition-all <?php echo $isActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'; ?>">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <?php if($isActive): ?><i class="fa-solid fa-check text-xs"></i><?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="flex-1 w-full">
                
                <?php if (count($products) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): 
                            $rating = $product['avg_rating'] ? round($product['avg_rating'], 1) : 0;
                        ?>
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full overflow-hidden relative">
                                
                                <a href="detay.php?id=<?php echo $product['id']; ?>" class="block relative aspect-video overflow-hidden bg-slate-100">
                                    <img src="<?php echo $product['image']; ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[2px]">
                                        <span class="bg-white text-slate-900 text-xs font-bold px-5 py-2.5 rounded-full transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 shadow-lg flex items-center gap-2">
                                            <i class="fa-solid fa-eye"></i> İncele
                                        </span>
                                    </div>

                                    <?php if($rating >= 4.5): ?>
                                        <div class="absolute top-3 left-3 bg-yellow-400 text-yellow-900 text-[10px] font-black px-2.5 py-1 rounded-lg shadow-sm uppercase tracking-wide flex items-center gap-1">
                                            <i class="fa-solid fa-fire"></i> Popüler
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <div class="p-5 flex flex-col flex-grow">
                                    <div class="mb-3">
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight group-hover:text-indigo-600 transition-colors line-clamp-1">
                                            <a href="detay.php?id=<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['title']); ?>
                                            </a>
                                        </h3>
                                        <p class="text-xs text-slate-400 mt-1 line-clamp-2 min-h-[2.5em]">
                                            <?php echo mb_strimwidth(strip_tags($product['description']), 0, 80, "..."); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center gap-1.5 mb-4 bg-slate-50 w-fit px-2 py-1 rounded-lg">
                                        <div class="flex text-yellow-400 text-xs">
                                            <?php for($i=1; $i<=5; $i++) echo ($i <= $rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star text-slate-300"></i>'; ?>
                                        </div>
                                        <span class="text-xs font-bold text-slate-500 mt-0.5 ml-1">(<?php echo $rating > 0 ? $rating : '0.0'; ?>)</span>
                                    </div>
                                    
                                    <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Fiyat</span>
                                            <span class="text-xl font-black text-slate-900"><?php echo number_format($product['price'], 2); ?> ₺</span>
                                        </div>
                                        
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="w-11 h-11 rounded-xl bg-slate-900 text-white hover:bg-indigo-600 transition-all shadow-md hover:shadow-indigo-500/30 flex items-center justify-center group/btn active:scale-95">
                                            <i class="fa-solid fa-cart-plus text-lg transform group-hover/btn:scale-110 transition-transform"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    
                    <div class="bg-white rounded-3xl p-16 text-center border border-dashed border-slate-300">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-4xl animate-pulse">
                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Aradığınız kriterlere uygun ürün yok.</h3>
                        <p class="text-slate-500 mb-6 max-w-md mx-auto">Farklı anahtar kelimeler deneyebilir veya filtreleri temizleyerek tüm ürünleri görüntüleyebilirsiniz.</p>
                        <a href="market.php" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                            <i class="fa-solid fa-rotate-left"></i> Filtreleri Temizle
                        </a>
                    </div>

                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>