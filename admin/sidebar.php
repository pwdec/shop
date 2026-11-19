<?php 
$cp = basename($_SERVER['PHP_SELF']); 
// Mevcut sayfa kontrolü için basit fonksiyon
function isActive($page, $current) {
    return $page == $current ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white';
}
?>

<div class="md:hidden fixed top-0 left-0 w-full bg-slate-900 z-40 px-4 py-3 flex items-center justify-between shadow-md border-b border-slate-800">
    <div class="flex items-center gap-2 text-white font-bold text-lg">
        <div class="bg-indigo-600 p-1.5 rounded-lg"><i class="fa-solid fa-layer-group"></i></div>
        <span>Yönetim</span>
    </div>
    <button onclick="toggleSidebar()" class="text-white p-2 rounded-lg hover:bg-slate-800 focus:outline-none">
        <i class="fa-solid fa-bars text-xl"></i>
    </button>
</div>

<div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-40 hidden backdrop-blur-sm transition-opacity duration-300"></div>

<aside id="sidebar" class="fixed top-0 left-0 z-50 h-screen w-64 bg-slate-900 text-slate-300 transition-transform duration-300 -translate-x-full md:translate-x-0 border-r border-slate-800 flex flex-col font-sans">
    
    <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800">
        <div class="bg-indigo-600 p-2 rounded-xl text-white shadow-lg shadow-indigo-500/20">
            <i class="fa-solid fa-layer-group text-xl"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-white tracking-wide">Yazılım<span class="text-indigo-500">Market</span></h2>
            <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider">Admin v2.0</p>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden ml-auto text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
    
    <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1 custom-scrollbar">
        
        <p class="px-2 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Genel</p>
        
        <a href="index.php" class="<?php echo isActive('index.php', $cp); ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group">
            <i class="fa-solid fa-chart-pie w-6 text-center group-hover:scale-110 transition-transform"></i> 
            <span class="font-medium text-sm">Dashboard</span>
        </a>

        <p class="px-2 text-xs font-bold text-slate-500 uppercase tracking-wider mt-6 mb-2">Mağaza Yönetimi</p>
        
        <a href="urunler.php" class="<?php echo (in_array($cp, ['urunler.php', 'urun-ekle.php', 'urun-duzenle.php'])) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group">
            <i class="fa-solid fa-box-open w-6 text-center group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-sm">Ürünler</span>
        </a>
        
        <a href="kategoriler.php" class="<?php echo isActive('kategoriler.php', $cp); ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group">
            <i class="fa-solid fa-tags w-6 text-center group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-sm">Kategoriler</span>
        </a>
        
        <a href="siparisler.php" class="<?php echo isActive('siparisler.php', $cp); ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group">
            <i class="fa-solid fa-basket-shopping w-6 text-center group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-sm">Siparişler</span>
        </a>
        
        <a href="kuponlar.php" class="<?php echo isActive('kuponlar.php', $cp); ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group">
            <i class="fa-solid fa-ticket w-6 text-center group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-sm">Kuponlar</span>
        </a>

        <p class="px-2 text-xs font-bold text-slate-500 uppercase tracking-wider mt-6 mb-2">Kullanıcı & Destek</p>
        
        <a href="kullanicilar.php" class="<?php echo isActive('kullanicilar.php', $cp); ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group">
            <i class="fa-solid fa-users w-6 text-center group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-sm">Kullanıcılar</span>
        </a>
        
        <a href="destek.php" class="<?php echo (in_array($cp, ['destek.php', 'destek-detay.php'])) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-life-ring w-6 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium text-sm">Destek</span>
            </div>
            <?php 
            $unread = $pdo->query("SELECT count(*) FROM tickets WHERE status = 'acik'")->fetchColumn();
            if($unread > 0): 
            ?>
                <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm animate-pulse"><?php echo $unread; ?></span>
            <?php endif; ?>
        </a>

        <p class="px-2 text-xs font-bold text-slate-500 uppercase tracking-wider mt-6 mb-2">Ayarlar</p>

        <a href="ayarlar.php" class="<?php echo isActive('ayarlar.php', $cp); ?> flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-200 group">
            <i class="fa-solid fa-sliders w-6 text-center group-hover:scale-110 transition-transform"></i>
            <span class="font-medium text-sm">Site Ayarları</span>
        </a>
    </div>

    <div class="p-4 border-t border-slate-800 bg-slate-900/50">
        <a href="../index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-emerald-400 transition-all mb-1 text-sm font-medium">
            <i class="fa-solid fa-arrow-up-right-from-square w-5"></i> Siteye Git
        </a>
        <a href="../cikis.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all text-sm font-medium">
            <i class="fa-solid fa-power-off w-5"></i> Güvenli Çıkış
        </a>
    </div>
</aside>

<script>
    // Mobil Menü Aç/Kapa Fonksiyonu
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            // Aç
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        } else {
            // Kapat
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }
    }
</script>

<style>
    /* İnce Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>