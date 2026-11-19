<?php
session_start();
session_destroy(); // Tüm oturum bilgilerini sil
header("Location: giris.php"); // Giriş sayfasına at
exit;
?>