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

// Xử lý khi người dùng trả sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_book_id'])) {
    $return_book_id = $_POST['return_book_id'];
    $return_date = date('Y-m-d');

    // Lấy thông tin sách được trả
    $stmt = $pdo->prepare('SELECT book_id FROM borrow_history WHERE id = ?');
    $stmt->execute([$return_book_id]);
    $book_id = $stmt->fetchColumn();

    // Cập nhật ngày trả sách
    $stmt = $pdo->prepare('UPDATE borrow_history SET return_date = ? WHERE id = ?');
    $stmt->execute([$return_date, $return_book_id]);

    // Tăng lại số lượng sách trong bảng books
    $stmt = $pdo->prepare('UPDATE books SET quantity = quantity + 1 WHERE id = ?');
    $stmt->execute([$book_id]);
}

// Xử lý khi người dùng tạo hồ sơ mượn sách mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['book_id'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $borrow_date = date('Y-m-d');

    // Thêm hồ sơ mượn sách
    $stmt = $pdo->prepare('INSERT INTO borrow_history (user_id, book_id, borrow_date) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $book_id, $borrow_date]);

    // Giảm số lượng sách
    $stmt = $pdo->prepare('UPDATE books SET quantity = quantity - 1 WHERE id = ?');
    $stmt->execute([$book_id]);
}

// Lọc mượn sách theo tìm kiếm
$search_query = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = " WHERE users.username LIKE :search OR books.title LIKE :search";
}

$stmt = $pdo->prepare('SELECT borrow_history.id, users.username, books.title, borrow_history.borrow_date, borrow_history.return_date
                     FROM borrow_history
                     INNER JOIN users ON borrow_history.user_id = users.id
                     INNER JOIN books ON borrow_history.book_id = books.id' . $search_query);
if ($search_query) {
    $stmt->execute(['search' => '%' . $search . '%']);
} else {
    $stmt->execute();
}
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý mượn sách</title>
    <!-- Thêm Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .popup {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
        }

        /* Thay đổi giao diện thanh tìm kiếm */
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 50%;
            margin-right: 10px;
        }
        .search-bar button {
            width: auto;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h2 class="m-0">Trang admin</h2>
            <div>
                <a href="admin.php" class="btn btn-light btn-sm">Trang chủ</a>
                <a href="admin_user.php" class="btn btn-light btn-sm">Quản lý người dùng</a>
                <a href="admin_book.php" class="btn btn-light btn-sm">Quản lý sách</a>
                <a href="admin_booking.php" class="btn btn-light btn-sm">Quản lý đặt hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Quản lý mượn sách</h2>

        <!-- Thanh tìm kiếm -->
        <form class="search-bar" action="" method="GET">
            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Tìm kiếm theo người mượn hoặc tên sách" value="<?php echo isset($search) ? $search : ''; ?>" />
            <button type="submit" id="searchButton" class="btn btn-primary">Tìm kiếm</button>
        </form>

        <!-- Nút thêm hồ sơ -->
        <div class="text-end mb-3">
            <button class="btn btn-success" onclick="openPopup()">Thêm hồ sơ mượn sách mới</button>
        </div>

        <!-- Danh sách mượn sách -->
        <?php if (count($borrows) > 0) { ?>
            <table class="table table-bordered table-striped">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Người mượn</th>
                        <th>Tên sách</th>
                        <th>Ngày mượn</th>
                        <th>Ngày trả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrows as $borrow) { ?>
                        <tr class="text-center">
                            <td><?php echo $borrow['username']; ?></td>
                            <td><?php echo $borrow['title']; ?></td>
                            <td><?php echo $borrow['borrow_date']; ?></td>
                            <td><?php echo $borrow['return_date'] ? $borrow['return_date'] : 'Chưa trả'; ?></td>
                            <td>
                                <form action="edit_borrow.php" method="POST" class="d-inline">
                                    <input type="hidden" name="borrow_id" value="<?php echo $borrow['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Chỉnh sửa</button>
                                </form>
                                <?php if (!$borrow['return_date']) { ?>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="return_book_id" value="<?php echo $borrow['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Trả sách</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-warning text-center">Không có lịch sử mượn sách.</div>
        <?php } ?>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay">
        <div class="popup" id="popup">
            <h2 class="text-center">Thêm hồ sơ mượn sách mới</h2>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="user_id" class="form-label">Người mượn:</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <?php
                        $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id IN (SELECT user_id FROM user_roles WHERE role_id = (SELECT id FROM roles WHERE name = "user"))');
                        $stmt->execute();
                        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($users as $user) {
                            echo '<option value="' . $user['id'] . '">' . $user['username'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="book_id" class="form-label">Tên sách:</label>
                    <select name="book_id" id="book_id" class="form-select">
                        <?php
                        $stmt = $pdo->query('SELECT id, title FROM books WHERE quantity > 0');
                        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($books as $book) {
                            echo '<option value="' . $book['id'] . '">' . $book['title'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                    <button type="button" class="btn btn-secondary" onclick="closePopup()">Đóng</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function openPopup() {
            document.getElementById("popup").style.display = "block";
            document.getElementById("overlay").style.display = "flex";
        }
        function closePopup() {
            document.getElementById("popup").style.display = "none";
            document.getElementById("overlay").style.display = "none";
        }
    </script>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
