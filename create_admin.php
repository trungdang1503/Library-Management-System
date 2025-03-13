<?php
// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Tạo tài khoản admin mới
$username = 'admin'; // Đổi thành tên đăng nhập mong muốn
$password = 'admin'; // Đổi thành mật khẩu mong muốn
$email = 'admin@gmail.com'; // Đổi thành email mong muốn
$name = 'Admin'; // Tên đầy đủ của tài khoản

// Kiểm tra xem tài khoản đã tồn tại chưa
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
$existing_user = $stmt->fetch();

if ($existing_user) {
    echo "Tài khoản đã tồn tại.";
    exit();
}

// Mã hóa mật khẩu
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Thêm tài khoản vào bảng `users`
$stmt = $pdo->prepare('INSERT INTO users (username, password, name, email) VALUES (?, ?, ?, ?)');
$stmt->execute([$username, $hashed_password, $name, $email]);

// Gán vai trò admin cho tài khoản mới
$user_id = $pdo->lastInsertId();
$stmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
$stmt->execute([$user_id, 2]); // 2 là role_id của vai trò admin (đảm bảo vai trò admin trong bảng roles)

echo "Tài khoản admin mới đã được tạo!";
?>
