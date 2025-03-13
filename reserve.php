<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');
// Lấy thông tin sách từ cơ sở dữ liệu
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$_GET['book_id']]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý đặt lịch hẹn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra số lượng sách có sẵn
    if ($book['quantity'] > 0) {
        // Code xử lý đặt lịch hẹn
    } else {
        $error_message = "Sách này hiện đã hết hàng.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="style2.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="vendor/bootstrap.css">
        <link rel="stylesheet" href="vendor/angular-material.min.css">
        <link rel="stylesheet" href="vendor/font-awesome.css">
        <link rel="stylesheet" href="2.css"> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch hẹn</title>
    <style>
        <?php include "styles.css" ?>
    </style>
</head>

<body>
    <h2>Đặt lịch hẹn</h2>
    <?php if (isset($error_message)) { echo "<p>$error_message</p>"; } ?>
    <form method="post">
        <h3>Thông tin sách:</h3>
        <p><strong>Tên sách:</strong> <?php echo $book['title']; ?></p>
        <p><strong>Tác giả:</strong> <?php echo $book['author']; ?></p>
        <p><strong>Số lượng còn lại:</strong> <?php echo $book['quantity']; ?></p>
        <!-- Thêm các trường và chức năng để chọn thời gian mượn -->
        <button type="submit">Đặt lịch hẹn</button>
    </form>
</body>
</html>
