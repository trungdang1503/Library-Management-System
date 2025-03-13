<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Lấy lịch sử mượn sách từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT booking.id, books.title, books.author, booking.booking_date 
                      FROM booking 
                      INNER JOIN books ON booking.book_id = books.id 
                      WHERE booking.user_id = ?');
$stmt->execute([$user_id]);
$booking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch hẹn</title>
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
                <a href="edit_profile.php" class="btn btn-light btn-sm">Chỉnh sửa thông tin</a>
                <a href="history.php" class="btn btn-light btn-sm">Lịch sử mượn sách</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Lịch hẹn</h2>
        <div class="booking-history">
            <?php if (count($booking_history) > 0) { ?>
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề sách</th>
                            <th>Tác giả</th>
                            <th>Ngày hẹn</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($booking_history as $booking) { ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo $booking['title']; ?></td>
                                <td><?php echo $booking['author']; ?></td>
                                <td><?php echo $booking['booking_date']; ?></td>
                                <td>
                                    <form method="post" action="delete_booking.php">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa lịch hẹn này không?')">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert alert-warning text-center">Không có lịch hẹn sách.</div>
            <?php } ?>
        </div>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
