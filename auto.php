<?php
// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Xóa tất cả các dòng có booking_date < NOW() 
$stmt = $pdo->prepare('DELETE FROM booking WHERE booking_date < NOW()');
$stmt->execute();

// Kiểm tra và thêm người dùng vào danh sách đen nếu cần
$stmt = $pdo->prepare('SELECT id, user_id, borrow_date, return_date FROM borrow_history');
$stmt->execute();
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($borrows as $borrow) {
    $borrow_date = new DateTime($borrow['borrow_date']);
    $return_date = new DateTime($borrow['return_date']);
    $interval = $borrow_date->diff($return_date);

    // Nếu số ngày mượn vượt quá 30 ngày, thêm người dùng vào danh sách đen
    if ($interval->days > 30) {
        $user_id = $borrow['user_id'];
        $stmt = $pdo->prepare('UPDATE users SET blacklisted = true WHERE id = ?');
        $stmt->execute([$user_id]);
    }
}
?>
