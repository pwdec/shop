<?php
require_once 'inc/db.php';
require_once 'inc/header.php';

// Sadece ürünleri çekiyoruz (Kategoriler anasayfada kalktığı için sorgusuna gerek yok)
$products = $pdo->query("SELECT products.*, (SELECT AVG(rating) FROM comments WHERE product_id = products.id) as avg_rating FROM products ORDER BY created_at DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="relative bg-slate-900 overflow-hidden py-24 lg:py-32 border-b border-slate-800">
    <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-purple-600/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-sm font-medium mb-6">
            <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
            %100 Güvenli ve Lisanslı
        </div>
        
        <h1 class="text-5xl md:text-7xl font-extrabold text-white tracking-tight mb-6">
            Premium Script <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">Pazar Yeri</span>
        </h1>
        
        <p class="text-xl text-slate-400 max-w-2xl mx-auto font-light leading-relaxed">
            Projeniz için ihtiyacınız olan profesyonel yazılımları keşfedin. 
            Güvenli ödeme, anında teslimat ve Türkçe destek.
        </p>
    </div>
</div>

<div class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        
        <div class="flex justify-between items-end mb-10 border-b border-gray-200 pb-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900">Vitrin Ürünleri</h2>
                <p class="text-slate-500 mt-1 text-sm">En son eklenen seçkin yazılımlar.</p>
            </div>
            <a href="market.php" class="text-indigo-600 font-bold hover:text-indigo-800 transition flex items-center gap-1">
                Tümünü Gör <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($products as $product): 
                $rating = $product['avg_rating'] ? round($product['avg_rating'], 1) : 0;
            ?>
                <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col group overflow-hidden">
                    
                    <a href="detay.php?id=<?php echo $product['id']; ?>" class="block relative h-64 overflow-hidden bg-gray-100">
                        <img src="<?php echo $product['image']; ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        
                        <?php if($rating >= 4.5): ?>
                            <span class="absolute top-3 left-3 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-2 py-1 rounded shadow-sm uppercase tracking-wide">Popüler</span>
                        <?php endif; ?>
                    </a>

                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="font-bold text-slate-800 text-lg mb-2 leading-tight">
                            <a href="detay.php?id=<?php echo $product['id']; ?>" class="hover:text-indigo-600 transition">
                                <?php echo htmlspecialchars($product['title']); ?>
                            </a>
                        </h3>

                        <div class="flex items-center gap-1 mb-4">
                            <div class="flex text-yellow-400 text-xs">
                                <?php for($i=0; $i<5; $i++) echo ($i < $rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star text-gray-300"></i>'; ?>
                            </div>
                            <span class="text-xs text-gray-400 font-medium">(<?php echo $rating > 0 ? $rating : '0'; ?>)</span>
                        </div>
                        
                        <div class="mt-auto pt-4 border-t border-gray-50">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs text-gray-400 font-semibold uppercase">Fiyat</span>
                                <span class="text-xl font-extrabold text-slate-900"><?php echo number_format($product['price'], 2); ?> ₺</span>
                            </div>
                            
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 active:scale-95 transition shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cart-plus"></i> Sepete Ekle
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>