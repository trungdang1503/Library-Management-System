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

// Lấy danh sách sách từ cơ sở dữ liệu dựa trên từ khóa tìm kiếm
$stmt = $pdo->prepare('SELECT * FROM books WHERE title LIKE ? OR author LIKE ?');
$stmt->execute(['%' . $search_keyword . '%', '%' . $search_keyword . '%']);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sách</title>
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
        .thumbnail {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
        }
        table tbody td, table thead th {
            vertical-align: middle; /* Căn giữa nội dung theo chiều dọc */
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
                <a href="admin_borrow.php" class="btn btn-light btn-sm">Quản lý mượn sách</a>
                <a href="admin_booking.php" class="btn btn-light btn-sm">Quản lý đặt hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Quản lý sách</h2>

        <!-- Thông báo lỗi -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'delete') { ?>
            <div class="alert alert-danger text-center">Không thể xóa sách này vì đang được mượn hoặc có lịch hẹn đặt.</div>
        <?php } ?>

        <!-- Thanh tìm kiếm -->
        <form action="" method="GET" class="mb-4 d-flex justify-content-center">
            <input type="text" name="search" id="searchInput" class="form-control w-50 me-2" placeholder="Tìm kiếm sách theo tiêu đề hoặc tác giả" value="<?php echo htmlspecialchars($search_keyword); ?>">
            <button type="submit" id="searchButton" class="btn btn-primary">Tìm kiếm</button>
        </form>

        <!-- Nút thêm sách -->
        <div class="text-end mb-3">
            <a href="add_book.php" class="btn btn-success">Thêm sách</a>
        </div>

        <!-- Danh sách sách -->
        <?php if (count($books) > 0) { ?>
            <table class="table table-bordered table-striped">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tiêu đề sách</th>
                        <th>Tác giả</th>
                        <th>Số lượng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book) { ?>
                        <tr class="text-center">
                            <td>
                                <?php if (!empty($book['image'])) { ?>
                                    <img src="<?php echo htmlspecialchars($book['image']); ?>" alt="Hình ảnh sách" class="thumbnail">
                                <?php } else { ?>
                                    <span>Không có ảnh</span>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['quantity']); ?></td>
                            <td>
                                <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-warning btn-sm">Chỉnh sửa</a>
                                <a href="delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xoá sách này?')">Xoá</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-warning text-center">Không có sách nào phù hợp với từ khóa tìm kiếm.</div>
        <?php } ?>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
