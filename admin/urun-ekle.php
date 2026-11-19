<?php
session_start();
require_once '../inc/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

$message = "";
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $site_demo = trim($_POST['site_demo']);
    $admin_demo = trim($_POST['admin_demo']);
    
    // Özellikleri JSON Olarak Hazırla
    $features = [];
    if(isset($_POST['feature_icon'])) {
        for($i=0; $i<count($_POST['feature_icon']); $i++) {
            if(!empty($_POST['feature_title'][$i])) {
                $features[] = [
                    'icon' => $_POST['feature_icon'][$i],
                    'title' => $_POST['feature_title'][$i],
                    'desc' => $_POST['feature_desc'][$i]
                ];
            }
        }
    }
    $featuresJson = !empty($features) ? json_encode($features, JSON_UNESCAPED_UNICODE) : null;

    if (empty($title) || empty($price) || empty($category_id)) {
        $message = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Lütfen zorunlu alanları doldurun.</div>';
    } else {
        // Resim ve Dosya Yükleme (Aynı kodlar)
        $coverPath = "";
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
            $coverName = uniqid('cover_') . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], '../uploads/images/' . $coverName)) {
                $coverPath = 'uploads/images/' . $coverName;
            }
        }

        $filePath = "";
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $fileName = uniqid('file_') . '.' . $ext;
            if (move_uploaded_file($_FILES['file']['tmp_name'], '../uploads/files/' . $fileName)) {
                $filePath = 'uploads/files/' . $fileName;
            }
        }

        // features sütununu da ekledik
        $sql = "INSERT INTO products (title, category_id, description, price, image, download_link, site_demo, admin_demo, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$title, $category_id, $description, $price, $coverPath, $filePath, $site_demo, $admin_demo, $featuresJson]);
        
        if ($result) {
            $product_id = $pdo->lastInsertId();
            // Galeri işlemleri (Aynı kodlar)
            if (isset($_FILES['gallery']) && count($_FILES['gallery']['name']) > 0) {
                $total_files = count($_FILES['gallery']['name']);
                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['gallery']['error'][$i] == 0) {
                        $ext = strtolower(pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION));
                        $galleryName = uniqid('gallery_') . '.' . $ext;
                        if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], '../uploads/images/' . $galleryName)) {
                            $galleryPath = 'uploads/images/' . $galleryName;
                            $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)")->execute([$product_id, $galleryPath]);
                        }
                    }
                }
            }
            $message = '<div class="bg-emerald-100 text-emerald-700 p-4 rounded mb-4">Ürün başarıyla eklendi!</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ekle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800">

<?php require_once 'sidebar.php'; ?>

<div class="md:ml-64 transition-all duration-300">
    <div class="h-16 md:h-0"></div>

    <div class="p-6 md:p-10 max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Yeni Ürün Ekle</h1>
            <a href="urunler.php" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-300 transition font-bold text-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Geri Dön
            </a>
        </div>
        
        <?php echo $message; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Ürün Başlığı <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 transition">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Ürün Açıklaması</label>
                                <textarea name="description" id="editor1"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-indigo-500"></i> Ürün Özellikleri (Güven Alanı)
                        </h3>
                        
                        <div id="features-container" class="space-y-3">
                            <div class="feature-row flex flex-col md:flex-row gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <div class="w-full md:w-1/4">
                                    <select name="feature_icon[]" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                                        <option value="fa-bolt">⚡ Hızlı (Bolt)</option>
                                        <option value="fa-code">💻 Kod (Code)</option>
                                        <option value="fa-headset">🎧 Destek (Headset)</option>
                                        <option value="fa-lock">🔒 Kilit (Lock)</option>
                                        <option value="fa-infinity">♾️ Sınırsız (Infinity)</option>
                                        <option value="fa-download">📥 İndir (Download)</option>
                                        <option value="fa-check">✅ Tik (Check)</option>
                                        <option value="fa-mobile-screen">📱 Mobil (Phone)</option>
                                    </select>
                                </div>
                                <div class="w-full md:w-1/4">
                                    <input type="text" name="feature_title[]" placeholder="Başlık (Örn: Açık Kaynak)" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div class="w-full md:w-2/4 flex gap-2">
                                    <input type="text" name="feature_desc[]" placeholder="Açıklama (Örn: Şifresiz teslimat)" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                                    <button type="button" onclick="removeFeature(this)" class="bg-red-100 text-red-600 p-2.5 rounded-lg hover:bg-red-200"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="addFeature()" class="mt-4 text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <i class="fa-solid fa-plus-circle"></i> Yeni Özellik Ekle
                        </button>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Demo Bağlantıları</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Site Demo</label>
                                <input type="url" name="site_demo" placeholder="https://..." class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 transition">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Admin Demo</label>
                                <input type="url" name="admin_demo" placeholder="https://..." class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                                <select name="category_id" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 transition">
                                    <option value="">Seçiniz</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Fiyat (TL) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="price" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 transition font-bold text-lg">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Medya & Dosya</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Kapak Resmi</label>
                            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Galeri (Çoklu)</label>
                            <input type="file" name="gallery[]" multiple accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                        </div>

                        <div class="border-t border-slate-100 pt-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">İndirilebilir Dosya</label>
                            <input type="file" name="file" accept=".zip,.rar,.7z" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-lg hover:bg-slate-800 transition shadow-lg shadow-slate-900/20">
                        Kaydet
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    CKEDITOR.replace('editor1');

    function addFeature() {
        const container = document.getElementById('features-container');
        const row = document.createElement('div');
        row.className = 'feature-row flex flex-col md:flex-row gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200';
        row.innerHTML = `
            <div class="w-full md:w-1/4">
                <select name="feature_icon[]" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                    <option value="fa-bolt">⚡ Hızlı (Bolt)</option>
                    <option value="fa-code">💻 Kod (Code)</option>
                    <option value="fa-headset">🎧 Destek (Headset)</option>
                    <option value="fa-lock">🔒 Kilit (Lock)</option>
                    <option value="fa-infinity">♾️ Sınırsız (Infinity)</option>
                    <option value="fa-download">📥 İndir (Download)</option>
                    <option value="fa-check">✅ Tik (Check)</option>
                    <option value="fa-mobile-screen">📱 Mobil (Phone)</option>
                </select>
            </div>
            <div class="w-full md:w-1/4">
                <input type="text" name="feature_title[]" placeholder="Başlık" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-full md:w-2/4 flex gap-2">
                <input type="text" name="feature_desc[]" placeholder="Açıklama" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                <button type="button" onclick="removeFeature(this)" class="bg-red-100 text-red-600 p-2.5 rounded-lg hover:bg-red-200"><i class="fa-solid fa-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
    }

    function removeFeature(btn) {
        btn.closest('.feature-row').remove();
    }
</script>

</body>
</html>