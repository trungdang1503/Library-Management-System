<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Xử lý lỗi: chuyển hướng đến trang đăng nhập
    header('Location: login.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Lấy thông tin người dùng từ cơ sở dữ liệu
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý chỉnh sửa thông tin người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Cập nhật thông tin người dùng vào cơ sở dữ liệu
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    $stmt->execute([$name, $email, $_SESSION['user_id']]);

    // Chuyển hướng người dùng về trang chính sau khi cập nhật thành công
    header('Location: borrow_books.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin</title>
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
    </style>
</head>

<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h2 class="m-0">Trang mượn sách</h2>
            <div>
                <a href="borrow_books.php" class="btn btn-light btn-sm">Trang chủ</a>
                <a href="history.php" class="btn btn-light btn-sm">Xem lịch sử mượn sách</a>
                <a href="booking_history.php" class="btn btn-light btn-sm">Xem lịch hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Chỉnh sửa thông tin</h2>
        <div class="card p-4 mx-auto" style="max-width: 600px;">
            <form method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Họ và tên:</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
