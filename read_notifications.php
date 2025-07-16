<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = intval($data['user_id']);

// Lấy tất cả thông báo chung hoặc thông báo riêng gửi cho người này
$sql = "SELECT id 
        FROM notifications
        WHERE user_id IS NULL OR user_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Chuẩn bị câu SQL thêm vào bảng notification_user
$sql_insert = "INSERT IGNORE INTO notification_user (notification_id, user_id, is_read, read_at)
               VALUES (?, ?, 1, NOW())";

$stmt_insert = $conn->prepare($sql_insert);

// Với mỗi thông báo, nếu user chưa có record, thêm vào
foreach ($notifications as $n) {
    $notification_id = $n['id'];
    $stmt_insert->bind_param("ii", $notification_id, $user_id);
    $stmt_insert->execute();
}

echo json_encode(['success' => true]);
?>
