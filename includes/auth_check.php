<?php
session_start();

// هذا مثال بسيط، يجب استبداله بنظام تحقق آمن
$valid_users = [
    'admin' => password_hash('admin_password', PASSWORD_BCRYPT)
];

if (!isset($_SESSION['loggedin']) || !isset($valid_users[$_SESSION['username']]) || 
    !password_verify($_SESSION['password'], $valid_users[$_SESSION['username']])) {
    header('Location: login.php');
    exit;
}
