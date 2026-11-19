<?php
ob_start();
session_start();
require_once 'inc/db.php';

if (isset($_SESSION['user_id'])) { header("Location: profil.php"); exit; }

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = '<div class="bg-rose-50 text-rose-700 p-3 rounded-xl mb-6 text-sm font-bold border border-rose-100 flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> Lütfen tüm alanları doldurun.</div>';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $message = '<div class="bg-amber-50 text-amber-700 p-3 rounded-xl mb-6 text-sm font-bold border border-amber-100 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> Bu e-posta zaten kayıtlı.</div>';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $message = '<div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl mb-6 text-sm font-bold border border-emerald-100 flex items-center gap-2"><i class="fa-solid fa-check-circle"></i> Kayıt başarılı! Yönlendiriliyorsunuz...</div>';
                echo "<script>setTimeout(function(){ window.location.href = 'giris.php'; }, 2000);</script>";
            } else {
                $message = '<div class="bg-rose-50 text-rose-700 p-3 rounded-xl mb-6 text-sm font-bold border border-rose-100">Bir hata oluştu.</div>';
            }
        }
    }
}

require_once 'inc/header.php';
?>

<div class="bg-slate-50 min-h-[calc(100vh-300px)] flex items-center justify-center py-16 px-4 relative overflow-hidden">
    
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#059669 1px, transparent 1px); background-size: 32px 32px;"></div>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 relative z-10 overflow-hidden">
        
        <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>

        <div class="p-8 md:p-10">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-extrabold text-slate-900">Aramıza Katıl 🚀</h1>
                <p class="text-slate-500 text-sm mt-2">Saniyeler içinde ücretsiz hesabını oluştur.</p>
            </div>

            <?php echo $message; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Kullanıcı Adı</label>
                    <div class="relative group">
                        <i class="fa-solid fa-user absolute left-4 top-3.5 text-slate-400 group-focus-within:text-emerald-600 transition-colors"></i>
                        <input type="text" name="username" required 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 block pl-11 p-3 transition-all font-medium placeholder:text-slate-400 outline-none" 
                               placeholder="Kullanıcı adınız">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">E-posta</label>
                    <div class="relative group">
                        <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-slate-400 group-focus-within:text-emerald-600 transition-colors"></i>
                        <input type="email" name="email" required 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 block pl-11 p-3 transition-all font-medium placeholder:text-slate-400 outline-none" 
                               placeholder="örnek@site.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Şifre</label>
                    <div class="relative group">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400 group-focus-within:text-emerald-600 transition-colors"></i>
                        <input type="password" name="password" required 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 block pl-11 p-3 transition-all font-medium placeholder:text-slate-400 outline-none" 
                               placeholder="En az 6 karakter">
                    </div>
                </div>

                <div class="flex items-start py-1">
                    <div class="flex items-center h-5">
                        <input id="terms" type="checkbox" required class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-emerald-300 accent-emerald-600 cursor-pointer">
                    </div>
                    <label for="terms" class="ml-2 text-xs font-medium text-slate-500 cursor-pointer select-none">
                        <a href="#" class="text-emerald-600 hover:text-emerald-700 font-bold hover:underline">Hizmet Şartlarını</a> kabul ediyorum.
                    </label>
                </div>

                <button type="submit" class="w-full bg-emerald-600 text-white hover:bg-emerald-700 font-bold rounded-xl text-sm px-5 py-3.5 transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center justify-center gap-2 group">
                    <span>Hesap Oluştur</span>
                    <i class="fa-solid fa-user-plus group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
        </div>

        <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center">
            <p class="text-sm text-slate-600">
                Zaten hesabın var mı? 
                <a href="giris.php" class="font-bold text-emerald-600 hover:text-emerald-800 hover:underline transition">Giriş Yap</a>
            </p>
        </div>
    </div>
</div>

<?php 
require_once 'inc/footer.php'; 
ob_end_flush(); 
?>