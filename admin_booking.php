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

// Lấy danh sách đặt hẹn từ cơ sở dữ liệu dựa trên từ khóa tìm kiếm
$stmt = $pdo->prepare('SELECT booking.id, users.username, books.title, booking.booking_date
                       FROM booking
                       INNER JOIN users ON booking.user_id = users.id
                       INNER JOIN books ON booking.book_id = books.id
                       WHERE users.username LIKE ? OR books.title LIKE ?');
$stmt->execute(['%' . $search_keyword . '%', '%' . $search_keyword . '%']);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xác nhận mượn sách khi người dùng nhấn nút
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking_id'])) {
    $confirm_booking_id = $_POST['confirm_booking_id'];
    $borrow_date = date('Y-m-d');

    // Lấy thông tin đặt hẹn từ bảng booking
    $stmt = $pdo->prepare('SELECT user_id, book_id FROM booking WHERE id = ?');
    $stmt->execute([$confirm_booking_id]);
    $booking_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Chèn thông tin vào bảng borrow_history
    $stmt = $pdo->prepare('INSERT INTO borrow_history (user_id, book_id, borrow_date) VALUES (?, ?, ?)');
    $stmt->execute([$booking_info['user_id'], $booking_info['book_id'], $borrow_date]);

    // Xoá dòng đặt hẹn khỏi bảng booking
    $stmt = $pdo->prepare('DELETE FROM booking WHERE id = ?');
    $stmt->execute([$confirm_booking_id]);

    header('Location: admin_booking.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đặt hẹn</title>
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
            <h2 class="m-0">Trang quản trị</h2>
            <div>
                <a href="admin.php" class="btn btn-light btn-sm">Trang chủ</a>
                <a href="admin_user.php" class="btn btn-light btn-sm">Quản lý người dùng</a>
                <a href="admin_book.php" class="btn btn-light btn-sm">Quản lý sách</a>
                <a href="admin_borrow.php" class="btn btn-light btn-sm">Quản lý mượn sách</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Quản lý đặt hẹn</h2>

        <!-- Thanh tìm kiếm -->
        <form action="" method="GET" class="mb-4 d-flex justify-content-center">
            <input type="text" name="search" id="searchInput" class="form-control w-50 me-2" placeholder="Tìm kiếm theo tên người đặt hoặc tên sách" value="<?php echo htmlspecialchars($search_keyword); ?>">
            <button type="submit" id="searchButton" class="btn btn-primary">Tìm kiếm</button>
        </form>

        <!-- Danh sách đặt hẹn -->
        <?php if (count($bookings) > 0) { ?>
            <table class="table table-bordered table-striped">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Người đặt</th>
                        <th>Tên sách</th>
                        <th>Ngày đặt</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking) { ?>
                        <tr class="text-center">
                            <td><?php echo $booking['username']; ?></td>
                            <td><?php echo $booking['title']; ?></td>
                            <td><?php echo $booking['booking_date']; ?></td>
                            <td>
                                <form action="" method="POST" class="d-inline">
                                    <input type="hidden" name="confirm_booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Xác nhận mượn</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-warning text-center">Không có đặt hẹn nào phù hợp với từ khóa tìm kiếm.</div>
        <?php } ?>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
