<?php
session_start();

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Khởi tạo biến lỗi
$error_message = '';

// Xử lý khi người dùng gửi biểu mẫu đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin đăng ký từ biểu mẫu
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Kiểm tra xem tên đăng nhập hoặc email đã tồn tại chưa
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    $existing_user = $stmt->fetch();

    if ($existing_user) {
        $error_message = "Tên đăng nhập hoặc email đã tồn tại.";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Thêm người dùng mới vào cơ sở dữ liệu
        $stmt = $pdo->prepare('INSERT INTO users (username, password, name, email) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $hashed_password, $name, $email]);

        // Lấy ID của người dùng mới được thêm vào
        $user_id = $pdo->lastInsertId();

        // Thêm vai trò "user" cho người dùng mới vào bảng user_roles
        $stmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
        $stmt->execute([$user_id, 1]); // Role_id của vai trò "user" là 1

        // Chuyển hướng người dùng đến trang đăng nhập
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <!-- Thêm Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Đăng ký</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)) { ?>
                            <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
                        <?php } ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Họ và Tên:</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng Ký</button>
                        </form>
                        <div class="text-center mt-3">
                            <p>Đã có tài khoản? <a href="index.php">Đăng nhập</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
