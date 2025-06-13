<?php
// إعدادات اتصال قاعدة البيانات
$db_host = 'localhost';
$db_user = 'username';
$db_pass = 'password';
$db_name = 'visitor_tracking';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
