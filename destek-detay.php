<?php
// 1. OTURUMU VE TAMPONLAMAYI BAŞLAT (En üstte olmalı!)
ob_start();
session_start();

require_once 'inc/db.php';

// 2. GÜVENLİK VE ID KONTROLLERİ
if (!isset($_SESSION['user_id'])) { 
    header("Location: giris.php"); 
    exit; 
}
if (!isset($_GET['id'])) { 
    header("Location: destek.php"); 
    exit; 
}

$ticket_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// 3. TALEBİ ÇEK (Doğrulama için)
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) { die("Talep bulunamadı veya yetkiniz yok."); }

// 4. YENİ MESAJ GÖNDERME İŞLEMİ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message, is_admin) VALUES (?, ?, ?, 0)");
        $stmt->execute([$ticket_id, $user_id, $message]);
        
        // Talebi tekrar 'acik' yap
        $pdo->prepare("UPDATE tickets SET status = 'acik' WHERE id = ?")->execute([$ticket_id]);
        
        header("Location: destek-detay.php?id=$ticket_id");
        exit;
    }
}

// 5. MESAJLARI ÇEK
$msgs = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC");
$msgs->execute([$ticket_id]);
$messages = $msgs->fetchAll(PDO::FETCH_ASSOC);

// 6. HEADER ÇAĞIR
require_once 'inc/header.php';
?>

<div class="max-w-4xl mx-auto mt-10 mb-20 px-4">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="destek.php" class="text-slate-500 hover:text-indigo-600 text-sm mb-1 inline-flex items-center gap-1 font-bold transition">
                <i class="fa-solid fa-arrow-left"></i> Geri Dön
            </a>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="text-indigo-600">#<?php echo $ticket['id']; ?></span> <?php echo htmlspecialchars($ticket['subject']); ?>
            </h1>
        </div>
        <span class="px-4 py-2 rounded-lg text-sm font-bold bg-slate-200 text-slate-700">
            <?php 
                if($ticket['status'] == 'acik') echo '<span class="text-green-600">Açık</span>';
                elseif($ticket['status'] == 'cevaplandi') echo '<span class="text-indigo-600">Yanıtlandı</span>';
                else echo '<span class="text-red-600">Kapalı</span>';
            ?>
        </span>
    </div>

    <div class="bg-slate-50 rounded-2xl p-6 h-[500px] overflow-y-auto mb-6 border border-slate-200 flex flex-col gap-4 shadow-inner">
        <?php foreach ($messages as $msg): ?>
            <?php if ($msg['is_admin']): ?>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-md">ADM</div>
                    <div class="bg-white p-4 rounded-r-2xl rounded-bl-2xl shadow-sm border border-slate-100 max-w-[85%]">
                        <p class="text-slate-800 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                        <span class="text-[10px] text-slate-400 mt-2 block font-bold"><?php echo date("d.m.Y H:i", strtotime($msg['created_at'])); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-start gap-3 flex-row-reverse">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-md">SEN</div>
                    <div class="bg-indigo-600 text-white p-4 rounded-l-2xl rounded-br-2xl shadow-md max-w-[85%]">
                        <p class="text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                        <span class="text-[10px] text-indigo-200 mt-2 block text-right font-bold"><?php echo date("d.m.Y H:i", strtotime($msg['created_at'])); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php if(empty($messages)): ?>
            <div class="text-center text-slate-400 py-10">Henüz mesaj yok.</div>
        <?php endif; ?>
    </div>

    <?php if ($ticket['status'] != 'kapali'): ?>
        <form action="" method="POST" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-lg flex gap-4 items-end">
            <div class="flex-grow">
                <textarea name="message" rows="2" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 focus:bg-white transition resize-none text-slate-700" placeholder="Bir yanıt yazın..."></textarea>
            </div>
            <button type="submit" name="reply" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-xl font-bold transition flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fa-solid fa-paper-plane text-lg"></i>
            </button>
        </form>
    <?php else: ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-center border border-red-100 font-bold shadow-sm">
            <i class="fa-solid fa-lock mr-2"></i> Bu destek talebi kapatılmıştır. Yeni bir talep oluşturabilirsiniz.
        </div>
    <?php endif; ?>

</div>

<?php require_once 'inc/footer.php'; ob_end_flush(); ?>