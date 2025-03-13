<?php
session_start();

// Kiểm tra đăng nhập và quyền truy cập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Kiểm tra xem có phải là yêu cầu POST không
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Kiểm tra xem người dùng đã chọn người dùng nào để đưa vào danh sách đen chưa
    if (isset($_GET['user_id'])) {
        $userId = $_GET['user_id'];

        // Kết nối đến cơ sở dữ liệu
        include('connect.php');

        // Thực hiện truy vấn để cập nhật người dùng vào danh sách đen
        $stmt = $pdo->prepare('UPDATE users SET blacklisted = 1 WHERE id = ?');
        $stmt->execute([$userId]);

        // Chuyển hướng người dùng trở lại trang quản lý người dùng
        header('Location: admin_user.php');
        exit();
    } else {
        // Nếu không có user_id được truyền, chuyển hướng người dùng trở lại trang trước đó
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Nếu không phải là yêu cầu GET, chuyển hướng người dùng trở lại trang trước đó
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
