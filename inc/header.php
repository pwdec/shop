<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($pdo)) { require_once __DIR__ . '/db.php'; }

$settings = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$site_title = $settings['site_title'] ?? 'Yazılım Market';

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$cart_total = 0;
if ($cart_count > 0) {
    $ids = implode(',', $_SESSION['cart']);
    $stmt = $pdo->query("SELECT sum(price) FROM products WHERE id IN ($ids)");
    $cart_total = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="flex flex-col min-h-screen" x-data="{ mobileMenuOpen: false }">

<?php if (isset($settings['topbar_status']) && $settings['topbar_status'] == 1): ?>
    <div style="background-color: <?php echo htmlspecialchars($settings['topbar_color']); ?>;" class="text-white text-xs font-bold py-2 text-center relative z-[60]">
        <div class="container mx-auto px-4 flex justify-center items-center gap-2">
            <span><?php echo htmlspecialchars($settings['topbar_text']); ?></span>
            <i class="fa-solid fa-arrow-right animate-bounce"></i>
        </div>
    </div>
<?php endif; ?>

<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex h-20 items-center justify-between gap-6">
            
            <div class="flex items-center gap-4 lg:gap-8 shrink-0">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-slate-600 text-2xl p-1 rounded hover:bg-slate-100 transition">
                    <i class="fa-solid fa-bars"></i>
                </button>
                
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition duration-300">
                        <i class="fa-solid fa-code text-lg"></i>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-lg font-bold text-slate-900 leading-none tracking-tight"><?php echo htmlspecialchars($site_title); ?></h1>
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Marketplace</span>
                    </div>
                </a>

                <a href="market.php" class="hidden lg:flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-50 border border-slate-200 hover:border-indigo-200 hover:text-indigo-600 hover:bg-indigo-50 transition ml-2">
                    <i class="fa-solid fa-layer-group text-slate-400 group-hover:text-indigo-500"></i>
                    Tüm Yazılımlar
                </a>
            </div>

            <div class="hidden lg:flex flex-1 max-w-lg mx-auto">
                <form action="market.php" method="GET" class="w-full relative group">
                    <input type="text" name="q" placeholder="Script, tema veya eklenti ara..." 
                           class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-full py-2.5 pl-11 pr-4 text-sm focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all placeholder:text-slate-400 font-medium">
                    <i class="fa-solid fa-search absolute left-4 top-3 text-slate-400 group-focus-within:text-indigo-500 transition"></i>
                </form>
            </div>

            <div class="flex items-center gap-3 sm:gap-6 shrink-0">
                
                <a href="sepet.php" class="relative group flex items-center gap-2 px-2 sm:px-3 py-2 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-200">
                    <div class="relative">
                        <i class="fa-solid fa-cart-shopping text-xl text-slate-600 group-hover:text-indigo-600 transition"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center ring-2 ring-white animate-pulse">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($cart_total > 0): ?>
                        <span class="hidden sm:block text-sm font-bold text-slate-700 group-hover:text-indigo-600">
                            <?php echo number_format($cart_total, 0); ?>₺
                        </span>
                    <?php endif; ?>
                </a>

                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="relative group h-full flex items-center cursor-pointer" x-data="{ open: false }" @click.outside="open = false">
                        <div @click="open = !open" class="flex items-center gap-3 pl-2 py-4"> 
                            <div class="text-right hidden xl:block">
                                <p class="text-sm font-bold text-slate-700 leading-none"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                <p class="text-[10px] font-medium text-slate-400 mt-1 uppercase tracking-wide">Hesabım</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-indigo-50 border-2 border-white shadow-sm flex items-center justify-center text-indigo-600 font-bold text-sm overflow-hidden ring-2 ring-transparent group-hover:ring-indigo-100 transition">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-indigo-600 transition"></i>
                        </div>

                        <div x-show="open" x-transition class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden ring-1 ring-black/5 z-50">
                            <div class="px-5 py-4 bg-slate-50 border-b border-slate-100">
                                <p class="text-xs text-slate-500 font-medium">Hoşgeldin,</p>
                                <p class="font-bold text-slate-800 truncate text-base"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            </div>
                            <div class="p-2 space-y-1">
                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                    <a href="admin/index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                        <i class="fa-solid fa-gauge-high w-5 text-center text-indigo-500"></i> Yönetici Paneli
                                    </a>
                                <?php endif; ?>
                                <a href="profil.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                    <i class="fa-solid fa-box-open w-5 text-center text-blue-500"></i> Siparişlerim
                                </a>
                                <a href="hesap-ayarlari.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                    <i class="fa-solid fa-user-gear w-5 text-center text-orange-500"></i> Hesap Ayarları
                                </a>
                                <a href="destek.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                    <i class="fa-solid fa-headset w-5 text-center text-purple-500"></i> Destek Talebi
                                </a>
                            </div>
                            <div class="p-2 border-t border-slate-100 bg-slate-50/50">
                                <a href="cikis.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 transition">
                                    <i class="fa-solid fa-power-off w-5 text-center"></i> Güvenli Çıkış
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden sm:flex items-center gap-3">
                        <a href="giris.php" class="text-sm font-bold text-slate-600 hover:text-indigo-600 px-4 py-2 transition">Giriş</a>
                        <a href="kayit.php" class="bg-slate-900 text-white text-sm font-bold py-2.5 px-6 rounded-xl hover:bg-indigo-600 transition shadow-lg shadow-slate-900/20 transform hover:-translate-y-0.5">
                            Kayıt Ol
                        </a>
                    </div>
                    <a href="giris.php" class="sm:hidden text-slate-600 hover:text-indigo-600 text-xl">
                        <i class="fa-solid fa-user"></i>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div x-show="mobileMenuOpen" x-transition class="lg:hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm" @click="mobileMenuOpen = false">
        <div class="bg-white w-3/4 h-full shadow-2xl p-6 relative" @click.stop>
            <div class="flex justify-between items-center mb-8">
                <span class="text-xl font-bold text-slate-900">Menü</span>
                <button @click="mobileMenuOpen = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form action="market.php" method="GET" class="mb-6">
                <div class="relative">
                    <input type="text" name="q" placeholder="Yazılım ara..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 pl-10 pr-4 text-sm font-medium outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <i class="fa-solid fa-search absolute left-3.5 top-3.5 text-slate-400"></i>
                </div>
            </form>

            <nav class="space-y-2">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 font-bold hover:bg-slate-50 hover:text-indigo-600 rounded-xl transition">
                    <i class="fa-solid fa-house w-5 text-center text-slate-400"></i> Anasayfa
                </a>
                <a href="market.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 font-bold hover:bg-slate-50 hover:text-indigo-600 rounded-xl transition">
                    <i class="fa-solid fa-layer-group w-5 text-center text-slate-400"></i> Tüm Yazılımlar
                </a>
                <a href="destek.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 font-bold hover:bg-slate-50 hover:text-indigo-600 rounded-xl transition">
                    <i class="fa-solid fa-headset w-5 text-center text-slate-400"></i> Destek
                </a>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="border-t border-slate-100 my-4 pt-4 space-y-3">
                        <a href="giris.php" class="flex items-center justify-center gap-2 w-full py-3 border border-slate-200 rounded-xl font-bold text-slate-700 hover:bg-slate-50 transition">
                            <i class="fa-solid fa-right-to-bracket"></i> Giriş Yap
                        </a>
                        <a href="kayit.php" class="flex items-center justify-center gap-2 w-full py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                            <i class="fa-solid fa-user-plus"></i> Kayıt Ol
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<div class="flex-grow">