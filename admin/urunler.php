<?php
session_start();
require_once '../inc/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

// Silme İşlemi
if(isset($_GET['sil'])){
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['sil']]);
    header("Location: urunler.php"); exit;
}

// Ürünleri Çek
$sql = "SELECT products.*, categories.name as category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.id 
        ORDER BY products.id DESC";
$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

<?php require_once 'sidebar.php'; ?>

<div class="md:ml-64 transition-all duration-300">
    <div class="h-16 md:h-0"></div> <div class="p-6 md:p-10 max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Ürünler</h1>
                <p class="text-slate-500 font-medium mt-1">Mağazadaki tüm yazılımları buradan yönet.</p>
            </div>
            <a href="urun-ekle.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-1 font-bold flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Yeni Ürün Ekle
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="p-5 font-bold">Görsel</th>
                            <th class="p-5 font-bold">Ürün Adı</th>
                            <th class="p-5 font-bold">Kategori</th>
                            <th class="p-5 font-bold">Fiyat</th>
                            <th class="p-5 font-bold text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($products as $prod): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-5">
                                <img src="../<?php echo $prod['image']; ?>" class="h-12 w-16 object-cover rounded-lg border border-slate-200 shadow-sm">
                            </td>
                            <td class="p-5 font-bold text-slate-700 text-base"><?php echo htmlspecialchars($prod['title']); ?></td>
                            <td class="p-5">
                                <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-100">
                                    <?php echo htmlspecialchars($prod['category_name'] ?? 'Kategorisiz'); ?>
                                </span>
                            </td>
                            <td class="p-5 font-mono font-bold text-emerald-600 text-lg"><?php echo number_format($prod['price'], 2); ?> ₺</td>
                            <td class="p-5 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="urun-duzenle.php?id=<?php echo $prod['id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors" title="Düzenle">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="?sil=<?php echo $prod['id']; ?>" onclick="return confirm('Bu ürünü silmek istediğine emin misin?')" class="w-9 h-9 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 transition-colors" title="Sil">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
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