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

// Kiểm tra borrow_id
if (!isset($_POST['borrow_id']) && !isset($_GET['borrow_id'])) {
    header('Location: admin_borrow.php');
    exit();
}

$borrow_id = $_POST['borrow_id'] ?? $_GET['borrow_id'];

// Lấy thông tin mượn sách
$stmt = $pdo->prepare('SELECT borrow_history.id, users.id AS user_id, users.username, books.id AS book_id, books.title, borrow_history.borrow_date, borrow_history.return_date
                     FROM borrow_history
                     INNER JOIN users ON borrow_history.user_id = users.id
                     INNER JOIN books ON borrow_history.book_id = books.id
                     WHERE borrow_history.id = ?');
$stmt->execute([$borrow_id]);
$borrow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$borrow) {
    header('Location: admin_borrow.php');
    exit();
}

// Lấy danh sách người dùng
$stmt_users = $pdo->query('SELECT id, username FROM users WHERE id IN (SELECT user_id FROM user_roles WHERE role_id = (SELECT id FROM roles WHERE name = "user"))');
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sách
$stmt_books = $pdo->query('SELECT id, title FROM books');
$books = $stmt_books->fetchAll(PDO::FETCH_ASSOC);

// Xử lý cập nhật hồ sơ mượn sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['book_id'], $_POST['borrow_date'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $borrow_date = $_POST['borrow_date'];
    $return_date = $_POST['return_date'] ?? null;

    // Kiểm tra ngày trả phải lớn hơn hoặc bằng ngày mượn
    if ($return_date && $return_date < $borrow_date) {
        $error_message = "Ngày trả phải lớn hơn hoặc bằng ngày mượn.";
    } else {
        // Nếu thay đổi sách mượn, cập nhật số lượng sách
        if ($book_id !== $borrow['book_id']) {
            // Tăng lại số lượng sách cũ
            $stmt_update_old = $pdo->prepare('UPDATE books SET quantity = quantity + 1 WHERE id = ?');
            $stmt_update_old->execute([$borrow['book_id']]);

            // Giảm số lượng sách mới
            $stmt_update_new = $pdo->prepare('UPDATE books SET quantity = quantity - 1 WHERE id = ?');
            $stmt_update_new->execute([$book_id]);
        }

        // Cập nhật thông tin mượn sách
        $stmt_update = $pdo->prepare('UPDATE borrow_history SET user_id = ?, book_id = ?, borrow_date = ?, return_date = ? WHERE id = ?');
        $stmt_update->execute([$user_id, $book_id, $borrow_date, $return_date, $borrow_id]);

        header('Location: admin_borrow.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa mượn sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h2 class="m-0">Quản lý mượn sách</h2>
            <a href="admin_borrow.php" class="btn btn-light btn-sm">Quay lại</a>
        </div>
    </header>

    <div class="container my-4">
        <h2 class="text-center mb-4">Chỉnh sửa mượn sách</h2>

        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
        <?php } ?>

        <form action="" method="POST" class="card p-4 mx-auto" style="max-width: 600px;">
            <input type="hidden" name="borrow_id" value="<?php echo $borrow['id']; ?>">

            <div class="mb-3">
                <label for="user_id" class="form-label">Người mượn:</label>
                <select id="user_id" name="user_id" class="form-select" required>
                    <?php foreach ($users as $user) { ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $borrow['user_id']) ? 'selected' : ''; ?>>
                            <?php echo $user['username']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="book_id" class="form-label">Tên sách:</label>
                <select id="book_id" name="book_id" class="form-select" required>
                    <?php foreach ($books as $book) { ?>
                        <option value="<?php echo $book['id']; ?>" <?php echo ($book['id'] == $borrow['book_id']) ? 'selected' : ''; ?>>
                            <?php echo $book['title']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="borrow_date" class="form-label">Ngày mượn:</label>
                <input type="date" id="borrow_date" name="borrow_date" class="form-control" value="<?php echo $borrow['borrow_date']; ?>" required>
            </div>

            <?php if ($borrow['return_date']) { ?>
                <div class="mb-3">
                    <label for="return_date" class="form-label">Ngày trả:</label>
                    <input type="date" id="return_date" name="return_date" class="form-control" value="<?php echo $borrow['return_date']; ?>">
                </div>
            <?php } ?>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
