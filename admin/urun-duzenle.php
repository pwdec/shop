<?php
session_start();
require_once '../inc/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }
if (!isset($_GET['id'])) { header("Location: urunler.php"); exit; }

$id = $_GET['id'];

// ... (Resim silme kodu aynen kalacak) ...
if (isset($_GET['delete_image'])) {
    $imgId = $_GET['delete_image'];
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ?");
    $stmt->execute([$imgId]);
    $img = $stmt->fetch();
    if ($img && file_exists('../' . $img['image_path'])) { unlink('../' . $img['image_path']); }
    $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imgId]);
    header("Location: urun-duzenle.php?id=$id&msg=img_deleted"); exit;
}

// Ürünü Çek
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) { header("Location: urunler.php"); exit; }

// Özellikleri Çözümle
$features = !empty($product['features']) ? json_decode($product['features'], true) : [];

// Galeri ve Kategoriler
$gallery = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?")->execute([$id]); // Yanlış kullanım düzeltildi
$gallery = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
$gallery->execute([$id]);
$images = $gallery->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $site_demo = trim($_POST['site_demo']);
    $admin_demo = trim($_POST['admin_demo']);

    // Özellikleri JSON Yap
    $newFeatures = [];
    if(isset($_POST['feature_icon'])) {
        for($i=0; $i<count($_POST['feature_icon']); $i++) {
            if(!empty($_POST['feature_title'][$i])) {
                $newFeatures[] = [
                    'icon' => $_POST['feature_icon'][$i],
                    'title' => $_POST['feature_title'][$i],
                    'desc' => $_POST['feature_desc'][$i]
                ];
            }
        }
    }
    $featuresJson = !empty($newFeatures) ? json_encode($newFeatures, JSON_UNESCAPED_UNICODE) : null;

    // Resim/Dosya güncelleme kodları (Aynı)
    $imagePath = $product['image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $imagePath = 'uploads/images/' . uniqid('cover_') . '.' . $ext;
        move_uploaded_file($_FILES['cover_image']['tmp_name'], '../' . $imagePath);
    }
    $filePath = $product['download_link'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $filePath = 'uploads/files/' . uniqid('file_') . '.' . $ext;
        move_uploaded_file($_FILES['file']['tmp_name'], '../' . $filePath);
    }

    // Güncelleme Sorgusu (features eklendi)
    $sql = "UPDATE products SET title=?, category_id=?, description=?, price=?, image=?, download_link=?, site_demo=?, admin_demo=?, features=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$title, $category_id, $description, $price, $imagePath, $filePath, $site_demo, $admin_demo, $featuresJson, $id]);

    // Galeri yükleme (Aynı)
    if (isset($_FILES['gallery']) && count($_FILES['gallery']['name']) > 0) {
        $total_files = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gallery']['error'][$i] == 0) {
                $ext = strtolower(pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION));
                $galleryName = uniqid('gallery_') . '.' . $ext;
                if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], '../uploads/images/' . $galleryName)) {
                    $galleryPath = 'uploads/images/' . $galleryName;
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)")->execute([$id, $galleryPath]);
                }
            }
        }
    }

    if ($result) {
        header("Location: urun-duzenle.php?id=$id&msg=updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Düzenle</title>
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
            <h1 class="text-3xl font-extrabold text-slate-900">Ürün Düzenle</h1>
            <a href="urunler.php" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-300 transition font-bold text-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Geri Dön
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated') echo '<div class="bg-emerald-100 text-emerald-700 p-4 rounded mb-4 font-bold">Güncellendi!</div>'; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Ürün Başlığı</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 font-bold">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Açıklama</label>
                                <textarea name="description" id="editor1"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-indigo-500"></i> Ürün Özellikleri
                        </h3>
                        
                        <div id="features-container" class="space-y-3">
                            <?php if(!empty($features)): ?>
                                <?php foreach($features as $feat): ?>
                                <div class="feature-row flex flex-col md:flex-row gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                    <div class="w-full md:w-1/4">
                                        <select name="feature_icon[]" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                                            <option value="fa-bolt" <?= $feat['icon'] == 'fa-bolt' ? 'selected' : '' ?>>⚡ Hızlı</option>
                                            <option value="fa-code" <?= $feat['icon'] == 'fa-code' ? 'selected' : '' ?>>💻 Kod</option>
                                            <option value="fa-headset" <?= $feat['icon'] == 'fa-headset' ? 'selected' : '' ?>>🎧 Destek</option>
                                            <option value="fa-lock" <?= $feat['icon'] == 'fa-lock' ? 'selected' : '' ?>>🔒 Kilit</option>
                                            <option value="fa-infinity" <?= $feat['icon'] == 'fa-infinity' ? 'selected' : '' ?>>♾️ Sınırsız</option>
                                            <option value="fa-download" <?= $feat['icon'] == 'fa-download' ? 'selected' : '' ?>>📥 İndir</option>
                                            <option value="fa-check" <?= $feat['icon'] == 'fa-check' ? 'selected' : '' ?>>✅ Tik</option>
                                            <option value="fa-mobile-screen" <?= $feat['icon'] == 'fa-mobile-screen' ? 'selected' : '' ?>>📱 Mobil</option>
                                        </select>
                                    </div>
                                    <div class="w-full md:w-1/4">
                                        <input type="text" name="feature_title[]" value="<?= htmlspecialchars($feat['title']) ?>" placeholder="Başlık" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                                    </div>
                                    <div class="w-full md:w-2/4 flex gap-2">
                                        <input type="text" name="feature_desc[]" value="<?= htmlspecialchars($feat['desc']) ?>" placeholder="Açıklama" class="w-full p-2.5 rounded-lg border border-slate-300 text-sm">
                                        <button type="button" onclick="removeFeature(this)" class="bg-red-100 text-red-600 p-2.5 rounded-lg hover:bg-red-200"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php endif; ?>
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
                                <input type="url" name="site_demo" value="<?php echo htmlspecialchars($product['site_demo']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 transition">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Admin Demo</label>
                                <input type="url" name="admin_demo" value="<?php echo htmlspecialchars($product['admin_demo']); ?>" class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 transition">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Galeri Resimleri</h3>
                        <?php if(count($images) > 0): ?>
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-4 mb-4">
                                <?php foreach($images as $img): ?>
                                    <div class="relative group">
                                        <img src="../<?php echo $img['image_path']; ?>" class="w-full h-24 object-cover rounded-lg border border-slate-200">
                                        <a href="?id=<?php echo $id; ?>&delete_image=<?php echo $img['id']; ?>" onclick="return confirm('Silinsin mi?')" class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 flex items-center justify-center rounded-full text-xs opacity-0 group-hover:opacity-100 transition shadow-md"><i class="fa-solid fa-xmark"></i></a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Yeni Resim Ekle (Çoklu)</label>
                            <input type="file" name="gallery[]" multiple accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Kategori</label>
                                <select name="category_id" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 transition">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Fiyat (TL)</label>
                                <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-lg">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kapak Resmi</label>
                            <img src="../<?php echo $product['image']; ?>" class="w-full h-40 object-cover rounded-lg border border-slate-200 mb-2">
                            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm text-slate-500">
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Yazılım Dosyası</label>
                            <input type="file" name="file" accept=".zip,.rar,.7z" class="w-full text-sm text-slate-500">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-lg hover:bg-slate-800 transition shadow-lg">
                        Güncelle
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