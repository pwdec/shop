<?php
session_start();
require_once '../inc/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Popup Resmi Yükleme (Mevcut kodlar korundu)
    $popup_image = $_POST['current_popup_image'];
    if (isset($_FILES['popup_image']) && $_FILES['popup_image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['popup_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $uploadDir = '../uploads/images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = 'popup_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['popup_image']['tmp_name'], $uploadDir . $fileName)) {
                $popup_image = 'uploads/images/' . $fileName;
            }
        }
    }

    $sql = "UPDATE settings SET 
            site_title=?, site_desc=?, contact_email=?, iban_number=?, bank_name=?,
            topbar_status=?, topbar_text=?, topbar_color=?,
            popup_status=?, popup_title=?, popup_text=?, popup_link=?, popup_image=?,
            paytr_status=?, paytr_merchant_id=?, paytr_merchant_key=?, paytr_merchant_salt=?, paytr_test_mode=?
            WHERE id=1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['site_title'], $_POST['site_desc'], $_POST['contact_email'], $_POST['iban_number'], $_POST['bank_name'],
        $_POST['topbar_status'], $_POST['topbar_text'], $_POST['topbar_color'],
        $_POST['popup_status'], $_POST['popup_title'], $_POST['popup_text'], $_POST['popup_link'], $popup_image,
        $_POST['paytr_status'], $_POST['paytr_merchant_id'], $_POST['paytr_merchant_key'], $_POST['paytr_merchant_salt'], $_POST['paytr_test_mode']
    ]);
    
    $message = '<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-2 shadow-sm"><i class="fa-solid fa-circle-check"></i> Ayarlar başarıyla güncellendi!</div>';
}

$settings = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Ayarları</title>
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
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Site Ayarları</h1>
        </div>

        <?php if(isset($message)) echo $message; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full -mr-10 -mt-10"></div>
                <h3 class="text-lg font-bold mb-6 text-slate-800 flex items-center gap-3 pb-4 border-b border-slate-100 relative z-10">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center"><i class="fa-solid fa-credit-card"></i></div>
                    PayTR Sanal POS Entegrasyonu
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Durum</label>
                        <select name="paytr_status" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-medium cursor-pointer">
                            <option value="1" <?php echo $settings['paytr_status'] == 1 ? 'selected' : ''; ?>>Aktif (Kredi Kartı ile Ödeme Al)</option>
                            <option value="0" <?php echo $settings['paytr_status'] == 0 ? 'selected' : ''; ?>>Pasif (Sadece Havale/EFT)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Mod</label>
                        <select name="paytr_test_mode" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-medium cursor-pointer">
                            <option value="0" <?php echo $settings['paytr_test_mode'] == 0 ? 'selected' : ''; ?>>Canlı Mod (Gerçek Ödeme)</option>
                            <option value="1" <?php echo $settings['paytr_test_mode'] == 1 ? 'selected' : ''; ?>>Test Modu (Sahte Ödeme)</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2 p-4 bg-indigo-50 rounded-xl border border-indigo-100 text-sm text-indigo-800">
                        <i class="fa-solid fa-circle-info mr-1"></i> Bu bilgileri PayTR Mağaza Paneli > Bilgi sayfasından alabilirsiniz.
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Merchant ID (Mağaza No)</label>
                        <input type="text" name="paytr_merchant_id" value="<?php echo htmlspecialchars($settings['paytr_merchant_id']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-mono text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Merchant Key</label>
                        <input type="text" name="paytr_merchant_key" value="<?php echo htmlspecialchars($settings['paytr_merchant_key']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-mono text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Merchant Salt</label>
                        <input type="text" name="paytr_merchant_salt" value="<?php echo htmlspecialchars($settings['paytr_merchant_salt']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-mono text-sm">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold mb-6 text-slate-800 flex items-center gap-3 pb-4 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center"><i class="fa-solid fa-globe"></i></div>
                        Genel Bilgiler
                    </h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Site Başlığı</label>
                            <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-medium">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Site Açıklaması</label>
                            <textarea name="site_desc" rows="3" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-medium resize-none"><?php echo htmlspecialchars($settings['site_desc']); ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">İletişim E-posta</label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-medium">
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold mb-6 text-slate-800 flex items-center gap-3 pb-4 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-money-bill-transfer"></i></div>
                        Banka Havale/EFT Ayarları
                    </h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Banka Adı</label>
                            <input type="text" name="bank_name" value="<?php echo htmlspecialchars($settings['bank_name']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-emerald-500 transition font-medium">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">IBAN Numarası</label>
                            <input type="text" name="iban_number" value="<?php echo htmlspecialchars($settings['iban_number']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-emerald-500 transition font-medium font-mono text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-bl-full -mr-10 -mt-10 transition-all group-hover:scale-110"></div>
                    <h3 class="text-lg font-bold mb-6 text-slate-800 flex items-center gap-3 pb-4 border-b border-slate-100 relative z-10">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center"><i class="fa-solid fa-bullhorn"></i></div>
                        Üst Duyuru Çubuğu
                    </h3>
                    
                    <div class="space-y-5 relative z-10">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Durum</label>
                            <select name="topbar_status" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-orange-500 transition font-medium cursor-pointer">
                                <option value="1" <?php echo $settings['topbar_status'] == 1 ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo $settings['topbar_status'] == 0 ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Duyuru Metni</label>
                            <input type="text" name="topbar_text" value="<?php echo htmlspecialchars($settings['topbar_text']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-orange-500 transition font-medium" placeholder="%20 İndirim Kodu: YAZ20">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Arkaplan Rengi</label>
                            <div class="flex items-center gap-3 p-2 border border-slate-200 rounded-xl w-fit">
                                <input type="color" name="topbar_color" value="<?php echo htmlspecialchars($settings['topbar_color']); ?>" class="h-10 w-16 rounded cursor-pointer border-0 bg-transparent p-0">
                                <span class="text-xs text-slate-500 font-mono"><?php echo htmlspecialchars($settings['topbar_color']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-10 -mt-10 transition-all group-hover:scale-110"></div>
                    <h3 class="text-lg font-bold mb-6 text-slate-800 flex items-center gap-3 pb-4 border-b border-slate-100 relative z-10">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center"><i class="fa-solid fa-window-maximize"></i></div>
                        Popup (Açılır Pencere)
                    </h3>
                    
                    <div class="space-y-5 relative z-10">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Durum</label>
                            <select name="popup_status" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-purple-500 transition font-medium cursor-pointer">
                                <option value="1" <?php echo $settings['popup_status'] == 1 ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo $settings['popup_status'] == 0 ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Başlık</label>
                                <input type="text" name="popup_title" value="<?php echo htmlspecialchars($settings['popup_title']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-purple-500 transition font-medium">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Buton Linki</label>
                                <input type="text" name="popup_link" value="<?php echo htmlspecialchars($settings['popup_link']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-purple-500 transition font-medium">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Açıklama</label>
                            <textarea name="popup_text" rows="2" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-purple-500 transition font-medium resize-none"><?php echo htmlspecialchars($settings['popup_text']); ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Popup Görseli</label>
                            <div class="flex items-center gap-4">
                                <?php if($settings['popup_image']): ?>
                                    <img src="../<?php echo $settings['popup_image']; ?>" class="h-16 w-16 object-cover rounded-lg border border-slate-200">
                                <?php endif; ?>
                                <label class="flex-1 cursor-pointer">
                                    <input type="hidden" name="current_popup_image" value="<?php echo $settings['popup_image']; ?>">
                                    <input type="file" name="popup_image" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="pt-4">
                <button type="submit" class="bg-slate-900 w-full md:w-auto px-8 py-4 rounded-xl text-white font-bold hover:bg-slate-800 transition shadow-lg shadow-slate-900/20 text-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save"></i> Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>