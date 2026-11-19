<?php
session_start();
require_once '../inc/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

// Ekleme
if (isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $rate = (int)$_POST['rate'];
    if (!empty($code) && $rate > 0 && $rate <= 100) {
        $check = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
        $check->execute([$code]);
        if ($check->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_rate) VALUES (?, ?)");
            $stmt->execute([$code, $rate]);
        }
    }
    header("Location: kuponlar.php"); exit;
}

// Silme
if (isset($_GET['sil'])) {
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$_GET['sil']]);
    header("Location: kuponlar.php"); exit;
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuponlar</title>
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
        <h1 class="text-3xl font-extrabold text-slate-900 mb-8">İndirim Kuponları</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-24">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-ticket text-emerald-500"></i> Yeni Kupon
                    </h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Kupon Kodu</label>
                            <input type="text" name="code" placeholder="Örn: YAZ2025" required 
                                   class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 uppercase font-bold text-center tracking-widest placeholder:font-normal placeholder:tracking-normal">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">İndirim Oranı (%)</label>
                            <input type="number" name="rate" min="1" max="100" placeholder="20" required 
                                   class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 font-bold text-center">
                        </div>
                        <button type="submit" name="add_coupon" class="w-full bg-emerald-600 text-white py-3 rounded-xl font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/30">
                            Kuponu Oluştur
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-5 font-bold">Kupon Kodu</th>
                                <th class="p-5 font-bold">İndirim</th>
                                <th class="p-5 font-bold text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($coupons as $c): ?>
                            <tr class="group hover:bg-slate-50 transition-colors">
                                <td class="p-5 font-mono font-bold text-indigo-600 text-lg tracking-wide">
                                    <?php echo htmlspecialchars($c['code']); ?>
                                </td>
                                <td class="p-5">
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-sm font-bold">
                                        %<?php echo $c['discount_rate']; ?>
                                    </span>
                                </td>
                                <td class="p-5 text-right">
                                    <a href="?sil=<?php echo $c['id']; ?>" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all ml-auto">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if(empty($coupons)): ?>
                        <div class="p-8 text-center text-slate-400 italic">Henüz kupon oluşturulmadı.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>