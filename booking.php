<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kiểm tra nếu không có ID sách được chuyển đến
if (!isset($_GET['book_id'])) {
    header('Location: borrow_books.php');
    exit();
}

// Kiểm tra nếu người dùng thuộc danh sách đen
if (isset($_SESSION['blacklisted']) && $_SESSION['blacklisted'] === true) {
    $blacklist_message = "Bạn thuộc danh sách đen nên không được mượn sách.";
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Xử lý khi người dùng nhấn nút đặt lịch hẹn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['book_id']) && isset($_SESSION['user_id'])) {
        $book_id = $_POST['book_id'];
        $user_id = $_SESSION['user_id'];
        $booking_date = $_POST['booking_date'];

        // Kiểm tra nếu số lượng sách còn lại là 0
        $stmt = $pdo->prepare('SELECT quantity FROM books WHERE id = ?');
        $stmt->execute([$book_id]);
        $quantity = $stmt->fetchColumn();

        if ($quantity > 0 && $booking_date >= date('Y-m-d')) {
            // Thực hiện đặt lịch hẹn bằng cách thêm vào bảng booking
            $stmt = $pdo->prepare('INSERT INTO booking (user_id, book_id, booking_date) VALUES (?, ?, ?)');
            $stmt->execute([$user_id, $book_id, $booking_date]);

            // Cập nhật số lượng sách trong bảng books
            $stmt = $pdo->prepare('UPDATE books SET quantity = quantity - 1 WHERE id = ?');
            $stmt->execute([$book_id]);

            // Chuyển hướng người dùng đến trang lịch sử mượn sách
            header('Location: booking_history.php');
            exit();
        } else {
            // Hiển thị thông báo lỗi nếu số lượng sách là 0 hoặc ngày hẹn không hợp lệ
            $error_message = "Không thể đặt lịch hẹn. Vui lòng kiểm tra lại số lượng sách và ngày hẹn.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch hẹn</title>
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
                <a href="edit_profile.php" class="btn btn-light btn-sm">Chỉnh sửa thông tin</a>
                <a href="history.php" class="btn btn-light btn-sm">Xem lịch sử mượn sách</a>
                <a href="booking_history.php" class="btn btn-light btn-sm">Xem lịch hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Đặt lịch hẹn</h2>

        <?php if (isset($blacklist_message)) { ?>
            <div class="alert alert-danger text-center">
                <?php echo $blacklist_message; ?>
            </div>
            <div class="text-center">
                <a href="borrow_books.php" class="btn btn-secondary">Quay lại</a>
            </div>
        <?php } else { ?>
            <?php if (isset($error_message)) { ?>
                <div class="alert alert-warning text-center">
                    <?php echo $error_message; ?>
                </div>
            <?php } ?>

            <div class="card p-4 mx-auto" style="max-width: 500px;">
                <form method="post">
                    <input type="hidden" name="book_id" value="<?php echo $_GET['book_id']; ?>">
                    <div class="mb-3">
                        <label for="booking_date" class="form-label">Nhập ngày hẹn:</label>
                        <input type="date" id="booking_date" name="booking_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Xác nhận đặt lịch hẹn</button>
                    </div>
                </form>
            </div>
        <?php } ?>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
