<?php
session_start();
require_once '../inc/db.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

// Ekleme
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: kategoriler.php?msg=added"); exit;
    }
}

// Güncelleme (YENİ)
if (isset($_POST['update_category'])) {
    $id = $_POST['cat_id'];
    $name = trim($_POST['cat_name']);
    if (!empty($name) && !empty($id)) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header("Location: kategoriler.php?msg=updated"); exit;
    }
}

// Silme
if (isset($_GET['sil'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['sil']]);
    header("Location: kategoriler.php?msg=deleted"); exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategoriler</title>
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
        <h1 class="text-3xl font-extrabold text-slate-900 mb-8">Kategori Yönetimi</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-24">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle text-indigo-500"></i> Yeni Ekle
                    </h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Kategori Adı</label>
                            <input type="text" name="name" placeholder="Örn: PHP Scriptler" required 
                                   class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition font-medium">
                        </div>
                        <button type="submit" name="add_category" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 transition shadow-lg">
                            Kategoriyi Kaydet
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-700">Mevcut Kategoriler</h3>
                    </div>
                    <table class="w-full text-left">
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($categories as $cat): ?>
                            <tr class="group hover:bg-indigo-50/50 transition-colors">
                                <td class="p-5 font-bold text-slate-700">
                                    <span class="text-slate-400 mr-3 text-xs font-mono">#<?php echo $cat['id']; ?></span>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </td>
                                <td class="p-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="openEditModal(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>')" 
                                                class="w-9 h-9 flex items-center justify-center rounded-lg text-indigo-500 hover:bg-indigo-100 transition-colors">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="?sil=<?php echo $cat['id']; ?>" onclick="return confirm('Silmek istediğine emin misin?')" 
                                           class="w-9 h-9 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-100 transition-colors">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center backdrop-blur-sm px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300" id="modalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-slate-900">Kategori Düzenle</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="cat_id" id="edit_cat_id">
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Kategori Adı</label>
                <input type="text" name="cat_name" id="edit_cat_name" required 
                       class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 transition font-bold">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50">İptal</button>
                <button type="submit" name="update_category" class="flex-1 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        document.getElementById('edit_cat_id').value = id;
        document.getElementById('edit_cat_name').value = name;
        document.getElementById('editModal').classList.remove('hidden');
        // Animasyon için timeout
        setTimeout(() => {
            document.getElementById('modalContent').classList.replace('scale-95', 'scale-100');
        }, 10);
    }

    function closeEditModal() {
        document.getElementById('modalContent').classList.replace('scale-100', 'scale-95');
        setTimeout(() => {
            document.getElementById('editModal').classList.add('hidden');
        }, 200);
    }
</script>

</body>
</html>