<?php
session_start();

if (isset($_GET['id']) && isset($_SESSION['cart'])) {
    $id = $_GET['id'];
    
    // Dizide bu ID'nin anahtarını (sırasını) bul
    $key = array_search($id, $_SESSION['cart']);
    
    // Eğer bulunduysa diziden çıkar
    if ($key !== false) {
        unset($_SESSION['cart'][$key]);
    }
}

// Sepete geri dön
header("Location: sepet.php");
exit;
?>