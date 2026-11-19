<?php
ob_start();
session_start();
require_once 'inc/db.php';

// 1. GÜVENLİK KONTROLÜ
if (!isset($_SESSION['user_id'])) { 
    header("Location: giris.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];

// Sidebar için kullanıcı bilgisini çek
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// 2. YENİ TALEP OLUŞTURMA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_ticket'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (!empty($subject) && !empty($message)) {
        // Bileti Oluştur
        $stmt = $pdo->prepare("INSERT INTO tickets (user_id, subject, status) VALUES (?, ?, 'acik')");
        $stmt->execute([$user_id, $subject]);
        $ticket_id = $pdo->lastInsertId();
        
        // İlk Mesajı Ekle
        $msgStmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $msgStmt->execute([$ticket_id, $user_id, $message]);
        
        header("Location: destek-detay.php?id=$ticket_id");
        exit;
    }
}

// 3. TALEPLERİ LİSTELE
$tickets = $pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
$tickets->execute([$user_id]);
$ticketList = $tickets->fetchAll(PDO::FETCH_ASSOC);

include 'inc/header.php';
?>

<div class="max-w-7xl mx-auto mt-10 mb-20 px-4">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                <div class="p-6 bg-slate-900 text-white text-center">
                    <div class="w-16 h-16 bg-white text-slate-900 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-3 border-4 border-indigo-500">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h2 class="text-lg font-bold"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-slate-400 text-xs">Üye Hesabı</p>
                </div>
                
                <nav class="p-3 space-y-1">
                    <a href="profil.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-box-open w-5"></i> Siparişlerim
                    </a>
                    <a href="hesap-ayarlari.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-user-gear w-5"></i> Hesap Ayarları
                    </a>
                    <a href="destek.php" class="block px-4 py-3 bg-indigo-50 text-indigo-700 font-bold rounded-xl flex items-center gap-3">
                        <i class="fa-solid fa-headset w-5"></i> Destek Talepleri
                    </a>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="admin/index.php" class="block px-4 py-3 text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition flex items-center gap-3">
                            <i class="fa-solid fa-gauge-high w-5"></i> Yönetici Paneli
                        </a>
                    <?php endif; ?>

                    <div class="border-t border-gray-100 my-2"></div>
                    <a href="cikis.php" class="block px-4 py-3 text-red-500 hover:bg-red-50 hover:text-red-700 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-right-from-bracket w-5"></i> Çıkış Yap
                    </a>
                </nav>
            </div>
        </div>

        <div class="w-full md:w-3/4">
            
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-headset text-indigo-600"></i> Destek Merkezi
            </h1>

            <div class="grid gap-8">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-indigo-500"></i> Yeni Talep Oluştur
                    </h2>
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-bold mb-2">Konu</label>
                            <input type="text" name="subject" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none focus:border-indigo-500 focus:bg-white transition" placeholder="Örn: Siparişim hakkında">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-bold mb-2">Mesajınız</label>
                            <textarea name="message" rows="3" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 outline-none focus:border-indigo-500 focus:bg-white transition resize-none" placeholder="Sorununuzu detaylı anlatın..."></textarea>
                        </div>
                        <button type="submit" name="new_ticket" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30">
                            Talebi Gönder
                        </button>
                    </form>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Talep Geçmişi</h2>
                    
                    <?php if (count($ticketList) > 0): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="divide-y divide-gray-100">
                                <?php foreach ($ticketList as $ticket): 
                                    $statusClass = 'bg-gray-100 text-gray-600';
                                    $statusText = 'Açık';
                                    if($ticket['status'] == 'cevaplandi') { $statusClass = 'bg-green-100 text-green-700'; $statusText = 'Yanıtlandı'; }
                                    if($ticket['status'] == 'kapali') { $statusClass = 'bg-red-100 text-red-700'; $statusText = 'Kapalı'; }
                                ?>
                                    <a href="destek-detay.php?id=<?php echo $ticket['id']; ?>" class="block p-5 hover:bg-gray-50 transition group">
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                                    #<?php echo $ticket['id']; ?>
                                                </div>
                                                <div>
                                                    <h3 class="font-bold text-gray-800 group-hover:text-indigo-600 transition"><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                                                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                                        <i class="fa-regular fa-clock"></i> <?php echo date("d.m.Y H:i", strtotime($ticket['created_at'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="text-xs font-bold px-3 py-1 rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                                <i class="fa-solid fa-chevron-right text-gray-300"></i>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-white p-10 rounded-2xl border border-gray-100 text-center shadow-sm">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-2xl">
                                <i class="fa-solid fa-life-ring"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-700">Henüz talep oluşturmadınız.</h3>
                            <p class="text-gray-500 text-sm mt-1">Bir sorununuz varsa yukarıdaki formu kullanabilirsiniz.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once 'inc/footer.php'; ?>