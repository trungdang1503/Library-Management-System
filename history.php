<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Lấy lịch sử mượn sách của người dùng từ cơ sở dữ liệu
$stmt = $pdo->prepare('SELECT borrow_history.borrow_date, borrow_history.return_date, books.title, books.author 
                       FROM borrow_history 
                       INNER JOIN books ON borrow_history.book_id = books.id
                       WHERE borrow_history.user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mượn sách</title>
    <!-- Thêm Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h2 class="m-0">Trang mượn sách</h2>
            <div>
                <a href="borrow_books.php" class="btn btn-light btn-sm">Trang chủ</a>
                <a href="edit_profile.php" class="btn btn-light btn-sm">Chỉnh sửa thông tin</a>
                <a href="booking_history.php" class="btn btn-light btn-sm">Xem lịch hẹn sách</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <h2 class="text-center mb-4">Lịch sử mượn sách</h2>
        <div class="table-responsive">
            <?php if (count($history) > 0) { ?>
                <table class="table table-bordered table-striped">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Tên sách</th>
                            <th>Tác giả</th>
                            <th>Ngày mượn</th>
                            <th>Ngày trả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $record) { ?>
                            <tr>
                                <td class="text-center"><?php echo $record['title']; ?></td>
                                <td class="text-center"><?php echo $record['author']; ?></td>
                                <td class="text-center"><?php echo $record['borrow_date']; ?></td>
                                <td class="text-center"><?php echo $record['return_date']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert alert-warning text-center">
                    Không có lịch sử mượn sách.
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
