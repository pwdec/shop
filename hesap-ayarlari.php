<?php
require_once 'inc/db.php';
require_once 'inc/header.php';

// Güvenlik
if (!isset($_SESSION['user_id'])) { header("Location: giris.php"); exit; }

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = ""; // success veya error

// 1. PROFİL GÜNCELLEME İŞLEMİ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($email)) {
        $message = "Lütfen tüm alanları doldurun.";
        $message_type = "error";
    } else {
        // E-posta başkasında var mı kontrolü (Kendi e-postası hariç)
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $user_id]);
        
        if ($check->rowCount() > 0) {
            $message = "Bu e-posta adresi başka bir üye tarafından kullanılıyor.";
            $message_type = "error";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$username, $email, $user_id])) {
                $_SESSION['username'] = $username; // Session'ı da güncelle
                $message = "Profil bilgileriniz başarıyla güncellendi.";
                $message_type = "success";
            } else {
                $message = "Bir hata oluştu.";
                $message_type = "error";
            }
        }
    }
}

// 2. ŞİFRE GÜNCELLEME İŞLEMİ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Mevcut şifreyi veritabanından çekip kontrol edelim
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();

    if (!password_verify($current_password, $user_data['password'])) {
        $message = "Mevcut şifreniz hatalı.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "Yeni şifreler birbiriyle uyuşmuyor.";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "Yeni şifre en az 6 karakter olmalıdır.";
        $message_type = "error";
    } else {
        // Şifreyi güncelle
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($update->execute([$hashed_password, $user_id])) {
            $message = "Şifreniz başarıyla değiştirildi.";
            $message_type = "success";
        } else {
            $message = "Hata oluştu.";
            $message_type = "error";
        }
    }
}

// Güncel Kullanıcı Bilgilerini Çek
$user = $pdo->query("SELECT * FROM users WHERE id = $user_id")->fetch(PDO::FETCH_ASSOC);
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
                    <a href="hesap-ayarlari.php" class="block px-4 py-3 bg-indigo-50 text-indigo-700 font-bold rounded-xl flex items-center gap-3">
                        <i class="fa-solid fa-user-gear w-5"></i> Hesap Ayarları
                    </a>
                    <a href="destek.php" class="block px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-headset w-5"></i> Destek Talepleri
                    </a>
                    <div class="border-t border-gray-100 my-2"></div>
                    <a href="cikis.php" class="block px-4 py-3 text-red-500 hover:bg-red-50 hover:text-red-700 rounded-xl transition flex items-center gap-3">
                        <i class="fa-solid fa-right-from-bracket w-5"></i> Çıkış Yap
                    </a>
                </nav>
            </div>
        </div>

        <div class="w-full md:w-3/4">
            
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-indigo-600"></i> Hesap Ayarları
            </h1>

            <?php if (!empty($message)): ?>
                <div class="p-4 rounded-xl mb-6 flex items-center gap-3 <?php echo $message_type == 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'; ?>">
                    <i class="fa-solid <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> text-xl"></i>
                    <p class="font-medium"><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <div class="grid gap-8">
                
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">Profil Bilgileri</h2>
                    
                    <form action="" method="POST">
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Kullanıcı Adı</label>
                                <div class="relative">
                                    <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-400"></i>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-10 pr-4 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">E-Posta Adresi</label>
                                <div class="relative">
                                    <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400"></i>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-10 pr-4 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" name="update_profile" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30">
                                Bilgileri Güncelle
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">Şifre Değiştir</h2>
                    
                    <form action="" method="POST">
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Mevcut Şifre</label>
                            <input type="password" name="current_password" required class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Yeni Şifre</label>
                                <input type="password" name="new_password" required class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Yeni Şifre (Tekrar)</label>
                                <input type="password" name="confirm_password" required class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_password" class="bg-slate-800 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-slate-900 transition shadow-lg">
                                Şifreyi Değiştir
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once 'inc/footer.php'; ?>