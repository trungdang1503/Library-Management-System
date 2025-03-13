<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Xử lý khi nhận dữ liệu từ form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    // Lấy booking_id từ dữ liệu gửi từ form
    $booking_id = $_POST['booking_id'];

    // Truy vấn để lấy thông tin chi tiết của lịch hẹn mượn sách
    $stmt = $pdo->prepare('SELECT * FROM booking WHERE id = ?');
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        // Cập nhật số lượng sách đã được đặt hẹn
        $stmt = $pdo->prepare('UPDATE books SET quantity = quantity + 1 WHERE id = ?');
        $stmt->execute([$booking['book_id']]);

        // Xóa dữ liệu lịch hẹn mượn sách từ bảng booking
        $stmt = $pdo->prepare('DELETE FROM booking WHERE id = ?');
        $stmt->execute([$booking_id]);

        // Chuyển hướng người dùng về trang booking_history.php
        header('Location: booking_history.php');
        exit();
    } else {
        // Xử lý khi không tìm thấy thông tin lịch hẹn mượn sách
        echo "Không tìm thấy thông tin lịch hẹn mượn sách.";
    }
} else {
    // Xử lý khi không có dữ liệu gửi từ form
    echo "Dữ liệu không hợp lệ.";
}
?>