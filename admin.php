<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <!-- Thêm Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        header {
            margin: 0;
        }
        .admin-options a {
            width: 200px;
            display: block;
            padding: 15px;
            margin: 10px auto;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .admin-options a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h2 class="m-0">Trang quản trị</h2>
            <div>
                <a href="admin_user.php" class="btn btn-light btn-sm">Quản lý người dùng</a>
                <a href="admin_book.php" class="btn btn-light btn-sm">Quản lý sách</a>
                <a href="admin_borrow.php" class="btn btn-light btn-sm">Quản lý mượn sách</a>
                <a href="admin_booking.php" class="btn btn-light btn-sm">Quản lý đặt hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Chức năng quản trị</h2>
        <div class="admin-options">
            <a href="admin_user.php">Quản lý người dùng</a>
            <a href="admin_book.php">Quản lý sách</a>
            <a href="admin_borrow.php">Quản lý mượn sách</a>
            <a href="admin_booking.php">Quản lý đặt hẹn</a>
        </div>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
