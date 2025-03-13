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

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Xử lý khi người dùng gửi form thêm sách
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin sách từ form
    $title = $_POST['title'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    
    // Upload ảnh sách và lưu đường dẫn vào thư mục Book
    $image_path = 'Book/' . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

    // Thêm sách mới vào cơ sở dữ liệu
    $stmt = $pdo->prepare('INSERT INTO books (title, author, quantity, image) VALUES (?, ?, ?, ?)');
    $stmt->execute([$title, $author, $quantity, $image_path]);

    // Chuyển hướng người dùng về trang quản lý sách
    header('Location: admin_book.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sách Mới</title>
    <!-- Thêm Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h2 class="m-0">Thêm Sách Mới</h2>
            <div>
                <a href="admin.php" class="btn btn-light btn-sm">Trang Admin</a>
                <a href="admin_user.php" class="btn btn-light btn-sm">Quản lý người dùng</a>
                <a href="admin_borrow.php" class="btn btn-light btn-sm">Quản lý mượn sách</a>
                <a href="admin_booking.php" class="btn btn-light btn-sm">Quản lý đặt hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Thông Tin Sách Mới</h2>
        <form action="" method="POST" enctype="multipart/form-data" class="card p-4 mx-auto" style="max-width: 500px;">
            <div class="mb-3">
                <label for="title" class="form-label">Tiêu đề sách:</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Nhập tiêu đề sách" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Tác giả:</label>
                <input type="text" name="author" id="author" class="form-control" placeholder="Nhập tên tác giả" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Số lượng:</label>
                <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Nhập số lượng sách" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Ảnh sách:</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Thêm sách</button>
            </div>
        </form>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
