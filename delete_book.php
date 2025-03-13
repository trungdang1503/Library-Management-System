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

// Kiểm tra xem có yêu cầu xóa sách không
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Kết nối đến cơ sở dữ liệu
    include('connect.php');

    // Lấy ID của sách cần xóa
    $book_id = $_GET['id'];

    // Kiểm tra xem sách có đang được mượn hoặc có lịch hẹn đặt không
    $stmt = $pdo->prepare('SELECT book_id FROM borrow_history WHERE book_id = ? AND return_date IS NULL 
                            UNION 
                            SELECT book_id FROM booking WHERE book_id = ?');
    $stmt->execute([$book_id, $book_id]);
    $borrowed_books = $stmt->fetchAll();

    // Nếu sách không được mượn và không có lịch hẹn đặt, thực hiện xóa
    if (empty($borrowed_books)) {
        // Trước tiên, xóa các bản ghi trong borrow_history tham chiếu đến sách này
        $stmt = $pdo->prepare('DELETE FROM borrow_history WHERE book_id = ?');
        $stmt->execute([$book_id]);

        // Sau đó, xóa sách khỏi cơ sở dữ liệu
        $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
        $stmt->execute([$book_id]);

        // Chuyển hướng người dùng về trang quản lý sách
        header('Location: admin_book.php');
        exit();
    } else {
        // Nếu sách đang được mượn hoặc có lịch hẹn đặt, thông báo không thể xóa
		 header('Location: admin_book.php?error=delete');
        exit();
    }
} else {
    // Nếu không có yêu cầu xóa sách được gửi, chuyển hướng người dùng về trang quản lý sách
    header('Location: admin_book.php');
    exit();
}
?>
