<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kiểm tra quyền truy cập của người dùng
if ($_SESSION['role'] !== 'admin') {
    header('Location: access_denied.php');
    exit();
}

// Kiểm tra xem đã nhận được ID của người dùng từ yêu cầu không
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header('Location: admin_user.php');
    exit();
}

$user_id = $_GET['user_id'];

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Cập nhật trạng thái danh sách đen của người dùng
$stmt = $pdo->prepare('UPDATE users SET blacklisted = 0 WHERE id = ?');
$stmt->execute([$user_id]);

// Chuyển hướng trở lại trang quản lý người dùng
header('Location: admin_user.php');
exit();
?>
