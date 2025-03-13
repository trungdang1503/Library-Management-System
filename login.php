<?php
session_start();

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Hiển thị lỗi nếu có
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Lấy thông tin người dùng
    $stmt = $pdo->prepare('SELECT users.id, users.username, users.password, users.blacklisted, roles.name AS role 
                           FROM users 
                           INNER JOIN user_roles ON users.id = user_roles.user_id 
                           INNER JOIN roles ON user_roles.role_id = roles.id 
                           WHERE users.username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra thông tin
    if ($user && password_verify($password, $user['password'])) {
        if ($user['blacklisted']) {
            header('Location: index.php?error=blacklisted');
            exit();
        } else {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['blacklisted'] = false;

            header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'borrow_books.php'));
            exit();
        }
    } else {
        header('Location: index.php?error=incorrect_credentials');
        exit();
    }
}
?>
