<?php
session_start();
require_once '../inc/db.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../giris.php"); exit; }

// Silme İşlemi
if (isset($_GET['sil'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['sil']]);
    header("Location: kullanicilar.php"); exit;
}

// Kullanıcı Güncelleme (Modal'dan gelen)
if (isset($_POST['update_user'])) {
    $uid = $_POST['user_id'];
    $uname = trim($_POST['username']);
    $uemail = trim($_POST['email']);
    $urole = $_POST['role'];

    if(!empty($uname) && !empty($uemail)) {
        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
        $stmt->execute([$uname, $uemail, $urole, $uid]);
        header("Location: kullanicilar.php?msg=updated"); exit;
    }
}

// ARAMA VE LİSTELEME
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if($search) {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar</title>
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
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">Kullanıcı Yönetimi</h1>
                <p class="text-slate-500 font-medium mt-1">Üyeleri ara, düzenle veya yetkilendir.</p>
            </div>
            
            <form action="" method="GET" class="w-full md:w-auto relative">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Kullanıcı adı veya E-posta ara..." 
                       class="w-full md:w-80 pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition shadow-sm font-medium">
                <i class="fa-solid fa-search absolute left-3.5 top-3.5 text-slate-400"></i>
                <?php if($search): ?>
                    <a href="kullanicilar.php" class="absolute right-3 top-3 text-slate-400 hover:text-rose-500"><i class="fa-solid fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="p-5 font-bold">Kullanıcı</th>
                            <th class="p-5 font-bold">İletişim</th>
                            <th class="p-5 font-bold">Rol</th>
                            <th class="p-5 font-bold text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach($users as $user): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg border border-indigo-100">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-base"><?php echo htmlspecialchars($user['username']); ?></p>
                                        <p class="text-xs text-slate-400">ID: #<?php echo $user['id']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-5 font-medium text-slate-600"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="p-5">
                                <?php if($user['role'] == 'admin'): ?>
                                    <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-xs font-bold border border-rose-200 inline-flex items-center gap-1"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                                <?php else: ?>
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold border border-slate-200">Üye</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                            class="w-9 h-9 flex items-center justify-center rounded-lg text-indigo-500 hover:bg-indigo-50 transition-colors border border-transparent hover:border-indigo-100">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </button>
                                    
                                    <a href="?sil=<?php echo $user['id']; ?>" onclick="return confirm('Bu üyeyi silmek istediğine emin misin?')" 
                                       class="w-9 h-9 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 transition-colors border border-transparent hover:border-rose-100">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if(empty($users)): ?>
                <div class="p-10 text-center text-slate-400 font-medium">Kriterlere uygun kullanıcı bulunamadı.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="userModal" class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center backdrop-blur-sm px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 transform scale-95 transition-transform duration-300" id="userModalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-slate-900 flex items-center gap-2"><i class="fa-solid fa-user-gear text-indigo-600"></i> Kullanıcı Düzenle</h3>
            <button onclick="closeUserModal()" class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-xmark text-2xl"></i></button>
        </div>
        
        <form action="" method="POST" class="space-y-5">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Kullanıcı Adı</label>
                <input type="text" name="username" id="edit_username" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition font-bold">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">E-Posta Adresi</label>
                <input type="email" name="email" id="edit_email" required class="w-full p-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition font-bold">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Yetki Rolü</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="user" id="role_user" class="peer sr-only">
                        <div class="p-3 rounded-xl border-2 border-slate-200 text-slate-500 text-center font-bold peer-checked:border-indigo-500 peer-checked:text-indigo-600 peer-checked:bg-indigo-50 transition">
                            <i class="fa-solid fa-user mr-1"></i> Standart Üye
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="admin" id="role_admin" class="peer sr-only">
                        <div class="p-3 rounded-xl border-2 border-slate-200 text-slate-500 text-center font-bold peer-checked:border-rose-500 peer-checked:text-rose-600 peer-checked:bg-rose-50 transition">
                            <i class="fa-solid fa-shield-halved mr-1"></i> Yönetici
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeUserModal()" class="flex-1 py-3.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Vazgeç</button>
                <button type="submit" name="update_user" class="flex-1 py-3.5 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUserModal(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email;
        
        if(user.role === 'admin') document.getElementById('role_admin').checked = true;
        else document.getElementById('role_user').checked = true;

        document.getElementById('userModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('userModalContent').classList.replace('scale-95', 'scale-100'), 10);
    }

    function closeUserModal() {
        document.getElementById('userModalContent').classList.replace('scale-100', 'scale-95');
        setTimeout(() => document.getElementById('userModal').classList.add('hidden'), 200);
    }
</script>

</body>
</html>