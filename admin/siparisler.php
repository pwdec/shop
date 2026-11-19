<?php
session_start();
require_once '../inc/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

// ARAMA MANTIĞI
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$sql = "SELECT orders.*, users.username, users.email, products.title as product_name, products.image as product_image 
        FROM orders 
        JOIN users ON orders.user_id = users.id 
        JOIN products ON orders.product_id = products.id 
        WHERE 1=1";

$params = [];
if($search) {
    $sql .= " AND (orders.order_no LIKE ? OR users.username LIKE ? OR products.title LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY orders.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişler</title>
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
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">Siparişler</h1>
                <p class="text-slate-500 font-medium mt-1">Tüm satış işlemlerini takip et.</p>
            </div>

            <form action="" method="GET" class="w-full md:w-auto relative">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Sipariş No, Müşteri veya Ürün ara..." 
                       class="w-full md:w-96 pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition shadow-sm font-medium">
                <i class="fa-solid fa-search absolute left-3.5 top-3.5 text-slate-400"></i>
                <?php if($search): ?>
                    <a href="siparisler.php" class="absolute right-3 top-3 text-slate-400 hover:text-rose-500"><i class="fa-solid fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="p-5 font-bold">Sipariş No</th>
                            <th class="p-5 font-bold">Müşteri</th>
                            <th class="p-5 font-bold">Ürün</th>
                            <th class="p-5 font-bold">Fiyat</th>
                            <th class="p-5 font-bold">Tarih</th>
                            <th class="p-5 font-bold">Durum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($orders as $order): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-5 font-mono text-slate-500 text-xs">
                                <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                    #<?php echo $order['order_no'] ? htmlspecialchars($order['order_no']) : $order['id']; ?>
                                </span>
                            </td>
                            
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="bg-indigo-100 text-indigo-600 w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs border border-indigo-200">
                                        <?php echo strtoupper(substr($order['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($order['username']); ?></p>
                                        <p class="text-xs text-slate-400"><?php echo htmlspecialchars($order['email']); ?></p>
                                    </div>
                                </div>
                            </td>

                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <img src="../<?php echo $order['product_image']; ?>" class="w-10 h-10 object-cover rounded-lg border border-slate-200 bg-slate-50">
                                    <span class="text-slate-700 font-medium text-sm truncate max-w-[200px]"><?php echo htmlspecialchars($order['product_name']); ?></span>
                                </div>
                            </td>

                            <td class="p-5 font-bold text-emerald-600 text-base"><?php echo number_format($order['price'], 2); ?> ₺</td>
                            
                            <td class="p-5 text-slate-500 text-xs">
                                <div class="flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo date("d.m.Y H:i", strtotime($order['created_at'])); ?>
                                </div>
                            </td>
                            
                            <td class="p-5">
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200 flex items-center w-fit gap-1">
                                    <i class="fa-solid fa-check"></i> Tamamlandı
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if(empty($orders)): ?>
                <div class="p-10 text-center text-slate-400 font-medium">
                    <?php echo $search ? 'Aramanıza uygun sipariş bulunamadı.' : 'Henüz sipariş yok.'; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>