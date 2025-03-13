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

// Kiểm tra xem có yêu cầu sửa sách không
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];

    // Kiểm tra xem người dùng đã chọn hình ảnh mới hay không
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $upload_dir = 'Book/';

        // Di chuyển hình ảnh tải lên vào thư mục Book
        move_uploaded_file($image_tmp_name, $upload_dir . $image_name);

        // Cập nhật thông tin sách kèm theo đường dẫn hình ảnh mới
        $stmt = $pdo->prepare('UPDATE books SET title = ?, author = ?, quantity = ?, image = ? WHERE id = ?');
        $stmt->execute([$title, $author, $quantity, $upload_dir . $image_name, $book_id]);
    } else {
        // Nếu không có hình ảnh mới, chỉ cập nhật thông tin sách
        $stmt = $pdo->prepare('UPDATE books SET title = ?, author = ?, quantity = ? WHERE id = ?');
        $stmt->execute([$title, $author, $quantity, $book_id]);
    }

    // Chuyển hướng người dùng về trang quản lý sách
    header('Location: admin_book.php');
    exit();
}

// Lấy thông tin sách từ cơ sở dữ liệu để hiển thị trong form sửa
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header('Location: admin_book.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sách</title>
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
                <a href="admin_borrow.php" class="btn btn-light btn-sm">Quản lý mượn sách</a>
                <a href="admin_booking.php" class="btn btn-light btn-sm">Quản lý đặt hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Chỉnh sửa sách</h2>
        <form action="" method="POST" enctype="multipart/form-data" class="card p-4 mx-auto" style="max-width: 500px;">
            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Tiêu đề sách:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo $book['title']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Tác giả:</label>
                <input type="text" name="author" id="author" class="form-control" value="<?php echo $book['author']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Số lượng:</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo $book['quantity']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Hình ảnh:</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
