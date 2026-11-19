<?php
ob_start();
session_start();
require_once 'inc/db.php'; 

// Zaten giriş yapmışsa
if (isset($_SESSION['user_id'])) {
    header("Location: profil.php");
    exit;
}

$error = '';
if (isset($_POST['giris_yap'])) {
    $email_or_username = trim($_POST['email_username']);
    $password = $_POST['password'];

    if (empty($email_or_username) || empty($password)) {
        $error = "Lütfen tüm alanları doldurun.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :input OR username = :input");
        $stmt->execute(['input' => $email_or_username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: profil.php");
            exit;
        } else {
            $error = "Girdiğiniz bilgiler hatalı.";
        }
    }
}

require_once 'inc/header.php'; 
?>

<div class="bg-slate-50 min-h-[calc(100vh-300px)] flex items-center justify-center py-16 px-4 relative overflow-hidden">
    
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#4f46e5 1px, transparent 1px); background-size: 32px 32px;"></div>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 relative z-10 overflow-hidden">
        
        <div class="h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

        <div class="p-8 md:p-10">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-extrabold text-slate-900">Tekrar Hoşgeldin!</h1>
                <p class="text-slate-500 text-sm mt-2">Hesabına giriş yap ve projelerine devam et.</p>
            </div>

            <?php if($error): ?>
                <div class="bg-rose-50 border border-rose-100 text-rose-600 p-3 rounded-xl mb-6 text-sm font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Kullanıcı Adı veya E-posta</label>
                    <div class="relative group">
                        <i class="fa-solid fa-user absolute left-4 top-3.5 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                        <input type="text" name="email_username" required 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 block pl-11 p-3 transition-all font-medium placeholder:text-slate-400 outline-none" 
                               placeholder="örnek@site.com">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5 ml-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase">Şifre</label>
                        <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">Şifremi Unuttum?</a>
                    </div>
                    <div class="relative group">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                        <input type="password" name="password" required 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 block pl-11 p-3 transition-all font-medium placeholder:text-slate-400 outline-none" 
                               placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" name="giris_yap" class="w-full bg-slate-900 text-white hover:bg-indigo-600 font-bold rounded-xl text-sm px-5 py-3.5 transition-all shadow-lg hover:shadow-indigo-500/20 active:scale-95 flex items-center justify-center gap-2 group">
                    <span>Giriş Yap</span>
                    <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
        </div>

        <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center">
            <p class="text-sm text-slate-600">
                Hesabın yok mu? 
                <a href="kayit.php" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline transition">Kayıt Ol</a>
            </p>
        </div>
    </div>
</div>

<?php 
require_once 'inc/footer.php'; 
ob_end_flush(); 
?>