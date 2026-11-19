<?php
session_start();
require_once '../inc/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

// --- İSTATİSTİKLER ---
// 1. Toplam Ciro
$total_revenue = $pdo->query("SELECT SUM(price) FROM orders")->fetchColumn() ?: 0;
// 2. Bu Ayın Cirosu
$month_revenue = $pdo->query("SELECT SUM(price) FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn() ?: 0;
// 3. Toplam Sipariş
$total_orders = $pdo->query("SELECT count(*) FROM orders")->fetchColumn();
// 4. Toplam Üye
$total_users = $pdo->query("SELECT count(*) FROM users")->fetchColumn();
// 5. Son 5 Sipariş
$recent_orders = $pdo->query("SELECT orders.*, users.username, products.title 
                              FROM orders 
                              JOIN users ON orders.user_id = users.id 
                              JOIN products ON orders.product_id = products.id 
                              ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

<?php require_once 'sidebar.php'; ?>

<div class="md:ml-64 transition-all duration-300">
    
    <div class="h-16 md:h-0"></div>

    <div class="p-6 md:p-10 max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Genel Bakış</h1>
                <p class="text-slate-500 font-medium mt-1">Mağazanızın anlık durum raporu ve istatistikleri.</p>
            </div>
            <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-xl shadow-sm border border-slate-200">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-sm font-bold text-slate-600"><?php echo date("d.m.Y"); ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            
            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="fa-solid fa-wallet text-6xl text-emerald-600"></i>
                </div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Toplam Ciro</p>
                <p class="text-3xl font-black text-slate-800 tracking-tight"><?php echo number_format($total_revenue, 2); ?><span class="text-lg text-slate-400 ml-1">₺</span></p>
                <div class="mt-4 flex items-center text-emerald-600 text-xs font-bold bg-emerald-50 w-fit px-2 py-1 rounded-lg">
                    <i class="fa-solid fa-arrow-trend-up mr-1"></i> Süper Gidiyor
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="fa-solid fa-calendar-check text-6xl text-blue-600"></i>
                </div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Bu Ayın Cirosu</p>
                <p class="text-3xl font-black text-slate-800 tracking-tight"><?php echo number_format($month_revenue, 2); ?><span class="text-lg text-slate-400 ml-1">₺</span></p>
                <div class="mt-4 flex items-center text-blue-600 text-xs font-bold bg-blue-50 w-fit px-2 py-1 rounded-lg">
                    <i class="fa-regular fa-calendar mr-1"></i> Bu Ay
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="fa-solid fa-bag-shopping text-6xl text-indigo-600"></i>
                </div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Toplam Sipariş</p>
                <p class="text-3xl font-black text-slate-800 tracking-tight"><?php echo $total_orders; ?></p>
                <div class="mt-4 flex items-center text-indigo-600 text-xs font-bold bg-indigo-50 w-fit px-2 py-1 rounded-lg">
                    <i class="fa-solid fa-check mr-1"></i> Başarılı Satış
                </div>
            </div>

             <div class="bg-white p-6 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="fa-solid fa-users text-6xl text-orange-600"></i>
                </div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Toplam Müşteri</p>
                <p class="text-3xl font-black text-slate-800 tracking-tight"><?php echo $total_users; ?></p>
                <div class="mt-4 flex items-center text-orange-600 text-xs font-bold bg-orange-50 w-fit px-2 py-1 rounded-lg">
                    <i class="fa-solid fa-user-plus mr-1"></i> Kayıtlı Üye
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="font-bold text-xl text-slate-800">Son Siparişler</h3>
                <a href="siparisler.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg transition-colors">
                    Tümünü Gör <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="p-5 font-bold">Sipariş No</th>
                            <th class="p-5 font-bold">Müşteri</th>
                            <th class="p-5 font-bold">Ürün</th>
                            <th class="p-5 font-bold">Tutar</th>
                            <th class="p-5 font-bold">Tarih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($recent_orders as $order): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-5 font-mono text-slate-500 text-xs">
                                <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                    #<?php echo htmlspecialchars($order['order_no'] ?? $order['id']); ?>
                                </span>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs">
                                        <?php echo strtoupper(substr($order['username'], 0, 1)); ?>
                                    </div>
                                    <span class="font-bold text-slate-700"><?php echo htmlspecialchars($order['username']); ?></span>
                                </div>
                            </td>
                            <td class="p-5 text-slate-600 font-medium"><?php echo htmlspecialchars($order['title']); ?></td>
                            <td class="p-5 font-bold text-emerald-600 text-base">+<?php echo number_format($order['price'], 2); ?> ₺</td>
                            <td class="p-5 text-slate-400 text-xs font-medium">
                                <i class="fa-regular fa-clock mr-1"></i>
                                <?php echo date("d.m.Y H:i", strtotime($order['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</body>
</html>