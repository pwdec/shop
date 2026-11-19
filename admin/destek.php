<?php
session_start();
require_once '../inc/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

// Talepleri Çek
$sql = "SELECT tickets.*, users.username FROM tickets 
        JOIN users ON tickets.user_id = users.id 
        ORDER BY FIELD(status, 'acik', 'cevaplandi', 'kapali'), created_at DESC";
$tickets = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destek Talepleri</title>
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
        <h1 class="text-3xl font-extrabold text-slate-900 mb-8">Destek Merkezi</h1>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="p-5 font-bold">ID</th>
                            <th class="p-5 font-bold">Kullanıcı</th>
                            <th class="p-5 font-bold">Konu</th>
                            <th class="p-5 font-bold">Durum</th>
                            <th class="p-5 font-bold">Tarih</th>
                            <th class="p-5 font-bold text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($tickets as $ticket): 
                            $statusClass = 'bg-slate-100 text-slate-500';
                            $statusText = 'Kapalı';
                            
                            if($ticket['status'] == 'acik') { $statusClass = 'bg-emerald-100 text-emerald-700 animate-pulse border border-emerald-200'; $statusText = 'Yeni Talep'; }
                            elseif($ticket['status'] == 'cevaplandi') { $statusClass = 'bg-blue-100 text-blue-700 border border-blue-200'; $statusText = 'Yanıtlandı'; }
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-5 font-mono text-slate-500 text-xs">
                                #<?php echo $ticket['id']; ?>
                            </td>
                            <td class="p-5 font-bold text-slate-700"><?php echo htmlspecialchars($ticket['username']); ?></td>
                            <td class="p-5 text-slate-700 font-medium max-w-xs truncate"><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td class="p-5">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td class="p-5 text-slate-500 text-xs">
                                <div class="flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo date("d.m.Y H:i", strtotime($ticket['created_at'])); ?>
                                </div>
                            </td>
                            <td class="p-5 text-right">
                                <a href="destek-detay.php?id=<?php echo $ticket['id']; ?>" class="bg-slate-900 text-white px-4 py-2 rounded-lg font-bold hover:bg-indigo-600 transition text-xs shadow-md">
                                    İncele & Yanıtla
                                </a>
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