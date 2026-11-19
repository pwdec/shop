<?php
$host = 'localhost';
$dbname = 'yazilim_market_db';
$username = 'root';
$password = 'mysql'; // Ampps varsayılan şifresi

try {
    // BURASI ARTIK $pdo OLDU
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı Bağlantı Hatası: " . $e->getMessage();
    exit;
}
?>