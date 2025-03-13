<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');
// Khởi tạo biến lỗi
$error_message = '';

// Xử lý khi người dùng nhấn nút mượn sách
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['book_id'])) {
        // Xử lý mã sách được chọn
        $book_id = $_POST['book_id'];
          
        // Chuyển hướng người dùng đến trang đặt lịch hẹn và truyền ID của sách
        header('Location: booking.php?book_id=' . $book_id);
        exit();
    } else {
        // Hiển thị thông báo lỗi nếu không có mã sách được chọn
        $error_message = "Vui lòng chọn một cuốn sách để mượn.";
    }
}
// Lấy danh sách sách từ cơ sở dữ liệu
$stmt = $pdo->query('SELECT * FROM books');
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Chuyển danh sách sách sang dạng JSON để sử dụng trong JavaScript
$books_json = json_encode($books);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
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

        .card {
            min-height: 500px;  /* Đảm bảo chiều cao tối thiểu cho card */
            height: auto;
            display: flex;
            flex-direction: column;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            position: relative;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .card-body {
            padding: 15px;
            flex-grow: 1;
            overflow: hidden;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 10px;
            height: 3.2em;  /* Đảm bảo tên sách có thể chứa 2 dòng */
            line-height: 1.6em;
            overflow-y: auto;
            text-overflow: clip;
            white-space: normal;
        }

        .card-text {
            font-size: 1rem;
            color: #555;
            height: 3.2em;  /* Đảm bảo phần tên tác giả có đủ 2 dòng */
            line-height: 1.6em;  /* Giữ khoảng cách giữa các dòng */
            overflow-y: auto;  /* Khi tên tác giả bị dài, sẽ có thanh cuộn dọc */
            text-overflow: ellipsis;
            white-space: normal;  /* Cho phép văn bản xuống dòng nếu cần */
        }

        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #f8f9fa;
            padding: 10px;
            text-align: center;
        }

        .btn-primary {
            width: 100%;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        var books = <?php echo $books_json; ?>;

        // Hiển thị tất cả sách khi trang được tải
        displayBooks(books);

        // Tìm kiếm sách khi người dùng nhập từ khóa
        $('#searchInput').keyup(function(){
            searchBooks();
        });

        function searchBooks() {
            var keyword = $('#searchInput').val().trim().toLowerCase();

            // Xóa danh sách sách hiện tại
            $('#bookList').empty();

            // Hiển thị các sách phù hợp với từ khóa
            books.forEach(function(book) {
                if (book.title.toLowerCase().includes(keyword)) {
                    appendBookElement(book);
                }
            });
        }

        function displayBooks(books) {
            books.forEach(function(book) {
                appendBookElement(book);
            });
        }

        function appendBookElement(book) {
            $('#bookList').append(`
                <div class="col-md-2">
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-md-12">
                                <img src="${book.image}" class="img-fluid rounded-top" alt="${book.title}">
                            </div>
                            <div class="col-md-12">
                                <div class="card-body">
                                    <h5 class="card-title">${book.title}</h5>
                                    <p class="card-text">Tác giả: ${book.author}</p>
                                    <p class="card-text">Số lượng: ${book.quantity}</p>
                                </div>
                            </div>
                            <div class="col-md-12 card-footer">
                                <form method="post">
                                    <input type="hidden" name="book_id" value="${book.id}">
                                    <button type="submit" class="btn btn-primary">Mượn sách</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    });
    </script>
</head>

<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h2 class="m-0">Trang mượn sách</h2>
            <div>
                <a href="edit_profile.php" class="btn btn-light btn-sm">Chỉnh sửa thông tin</a>
                <a href="history.php" class="btn btn-light btn-sm">Lịch sử mượn sách</a>
                <a href="booking_history.php" class="btn btn-light btn-sm">Xem lịch hẹn</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Nội dung -->
    <div class="container my-4">
        <div class="search-bar mb-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm sách">
        </div>
        <div class="row g-3" id="bookList"></div>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
