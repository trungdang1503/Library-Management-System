<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <!-- Thêm Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Đăng nhập</h4>
                    </div>
                    <div class="card-body">
                        <!-- Hiển thị thông báo lỗi -->
                        <?php if (isset($_GET['error'])) { ?>
                            <?php if ($_GET['error'] === 'incorrect_credentials') { ?>
                                <div class="alert alert-danger text-center">Tên đăng nhập hoặc mật khẩu không đúng, vui lòng đăng nhập lại.</div>
                            <?php } elseif ($_GET['error'] === 'blacklisted') { ?>
                                <div class="alert alert-danger text-center">Tài khoản của bạn đã bị chặn. Vui lòng liên hệ quản trị viên.</div>
                            <?php } ?>
                        <?php } ?>

                        <!-- Form Đăng Nhập -->
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                        </form>
                        <div class="text-center mt-3">
                            <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm JavaScript của Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
