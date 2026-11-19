<?php
session_start();
require_once '../inc/db.php';

// Güvenlik & Yönlendirme
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }
if (!isset($_GET['id'])) { header("Location: destek.php"); exit; }

$ticket_id = (int)$_GET['id'];
$admin_id = $_SESSION['user_id'];

// Cevap Yazma
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_admin) VALUES (?, ?, ?, 1)");
        $stmt->execute([$ticket_id, $admin_id, $message]);
        
        $pdo->prepare("UPDATE tickets SET status = 'cevaplandi' WHERE id = ?")->execute([$ticket_id]);
        
        header("Location: destek-detay.php?id=$ticket_id");
        exit;
    }
}

// Talebi Kapatma
if (isset($_POST['close_ticket'])) {
    $pdo->prepare("UPDATE tickets SET status = 'kapali' WHERE id = ?")->execute([$ticket_id]);
    header("Location: destek-detay.php?id=$ticket_id");
    exit;
}

// Verileri Çek
$ticket = $pdo->query("SELECT tickets.*, users.username FROM tickets JOIN users ON tickets.user_id = users.id WHERE tickets.id = $ticket_id")->fetch(PDO::FETCH_ASSOC);
$messages = $pdo->query("SELECT * FROM ticket_messages WHERE ticket_id = $ticket_id ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talep Detay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

<?php require_once 'sidebar.php'; ?>

<div class="md:ml-64 transition-all duration-300">
    <div class="h-16 md:h-0"></div>

    <div class="p-6 md:p-10 max-w-5xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <a href="destek.php" class="text-slate-500 hover:text-slate-800 text-sm mb-2 inline-flex items-center gap-1 font-bold transition">
                    <i class="fa-solid fa-arrow-left"></i> Listeye Dön
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 leading-tight">
                    <span class="text-indigo-600">#<?php echo $ticket['id']; ?></span> <?php echo htmlspecialchars($ticket['subject']); ?>
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm font-medium text-slate-500">Müşteri:</span>
                    <span class="bg-slate-200 text-slate-700 px-2 py-0.5 rounded text-xs font-bold"><?php echo htmlspecialchars($ticket['username']); ?></span>
                </div>
            </div>
            
            <?php if($ticket['status'] != 'kapali'): ?>
            <form action="" method="POST">
                <button type="submit" name="close_ticket" class="bg-rose-50 text-rose-600 border border-rose-200 px-5 py-2.5 rounded-xl font-bold hover:bg-rose-100 transition text-sm flex items-center gap-2">
                    <i class="fa-solid fa-lock"></i> Talebi Kapat
                </button>
            </form>
            <?php else: ?>
                <span class="bg-slate-200 text-slate-600 border border-slate-300 px-5 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-lock"></i> Talep Kapalı
                </span>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[600px]">
            
            <div class="flex-grow overflow-y-auto p-6 space-y-6 bg-slate-50/50 custom-scrollbar">
                <?php foreach ($messages as $msg): ?>
                    <?php if ($msg['is_admin']): ?>
                        <div class="flex items-start gap-3 flex-row-reverse">
                            <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center text-white font-bold text-xs shadow-md border-2 border-white">
                                ADM
                            </div>
                            <div class="bg-slate-900 text-white p-4 rounded-2xl rounded-tr-none shadow-md max-w-[85%]">
                                <p class="text-sm leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($msg['message']); ?></p>
                                <span class="text-[10px] text-slate-400 mt-2 block text-right font-medium opacity-80">
                                    <?php echo date("d.m.Y H:i", strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-white border-2 border-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shadow-sm">
                                ÜYE
                            </div>
                            <div class="bg-white p-4 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 max-w-[85%]">
                                <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line"><?php echo htmlspecialchars($msg['message']); ?></p>
                                <span class="text-[10px] text-slate-400 mt-2 block font-medium">
                                    <?php echo date("d.m.Y H:i", strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="p-5 border-t border-slate-200 bg-white rounded-b-2xl">
                <?php if ($ticket['status'] != 'kapali'): ?>
                    <form action="" method="POST" class="flex gap-3">
                        <textarea name="message" rows="1" required class="flex-grow bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition resize-none text-sm font-medium" placeholder="Yanıtınızı buraya yazın..."></textarea>
                        <button type="submit" name="reply" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 rounded-xl font-bold transition flex flex-col items-center justify-center shadow-lg shadow-indigo-500/30">
                            <i class="fa-solid fa-paper-plane text-lg"></i>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center text-slate-500 py-3 font-medium bg-slate-50 rounded-xl border border-slate-200 text-sm">
                        <i class="fa-solid fa-circle-info mr-2"></i> Bu destek talebi kapatılmıştır. Yanıt verilemez.
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>
</body>
</html>