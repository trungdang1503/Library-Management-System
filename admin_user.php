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

// Xử lý tìm kiếm
$search_keyword = '';
if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
}

// Lấy danh sách người dùng từ cơ sở dữ liệu
$stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u 
    INNER JOIN user_roles ur ON u.id = ur.user_id 
    INNER JOIN roles r ON ur.role_id = r.id 
    WHERE u.name LIKE ? AND r.name != "admin" 
    ORDER BY u.name');
$stmt->execute(["%$search_keyword%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
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
            <h2 class="m-0">Trang quản trị</h2>
            <div>
                <a href="admin.php" class="btn btn-light btn-sm">Trang chủ</a>
                <a href="admin_book.php" class="btn btn-light btn-sm">Quản lý sách</a>
                <a href="admin_borrow.php" class="btn btn-light btn-sm">Quản lý mượn sách</a>
                <a href="admin_booking.php" class="btn btn-light btn-sm">Quản lý đặt hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Quản lý người dùng</h2>

        <!-- Thanh tìm kiếm -->
        <form action="" method="GET" class="mb-4 d-flex justify-content-center">
            <input type="text" name="search" id="searchInput" class="form-control w-50 me-2" placeholder="Tìm kiếm theo tên" value="<?php echo htmlspecialchars($search_keyword); ?>">
            <button type="submit" id="searchButton" class="btn btn-primary">Tìm kiếm</button>
        </form>

        <!-- Danh sách người dùng -->
        <?php if (count($users) > 0) { ?>
            <table class="table table-bordered table-striped">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Tài khoản</th>
                        <th>Mật khẩu</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Quyền</th>
                        <th>Danh sách đen</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr class="text-center">
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['password']; ?></td>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['role_name']; ?></td>
                            <td><?php echo $user['blacklisted'] ? 'Có' : 'Không'; ?></td>
                            <td>
                                <?php if ($user['blacklisted']) { ?>
                                    <button class="btn btn-success btn-sm" onclick="confirmUnblock(<?php echo $user['id']; ?>)">Loại khỏi danh sách đen</button>
                                <?php } else { ?>
                                    <button class="btn btn-danger btn-sm" onclick="confirmBlock(<?php echo $user['id']; ?>)">Đưa vào danh sách đen</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-warning text-center">Không có người dùng nào phù hợp.</div>
        <?php } ?>
    </div>

    <!-- JavaScript -->
    <script>
        function confirmBlock(userId) {
            if (confirm("Bạn có chắc chắn muốn đưa người dùng này vào danh sách đen không?")) {
                window.location.href = "block_user.php?user_id=" + userId;
            }
        }

        function confirmUnblock(userId) {
            if (confirm("Bạn có chắc chắn muốn loại người dùng này khỏi danh sách đen không?")) {
                window.location.href = "unblock_user.php?user_id=" + userId;
            }
        }
    </script>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
