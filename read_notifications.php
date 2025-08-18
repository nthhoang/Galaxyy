<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = intval($data['user_id']);

// =======================
// 1️⃣ Xử lý broadcast
// =======================

// Lấy tất cả thông báo broadcast mà user này chưa đọc
$sql_broadcast = "
    SELECT n.id
    FROM notifications n
    LEFT JOIN notification_user nu
        ON nu.notification_id = n.id AND nu.user_id = ?
    WHERE n.type = 'broadcast'
      AND (nu.is_read IS NULL OR nu.is_read = 0)
";

$stmt = $conn->prepare($sql_broadcast);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$broadcasts = $result->fetch_all(MYSQLI_ASSOC);

// Với mỗi broadcast chưa đọc, thêm vào notification_user để đánh dấu đã đọc
$sql_insert = "
    INSERT IGNORE INTO notification_user (notification_id, user_id, is_read, read_at)
    VALUES (?, ?, 1, NOW())
";

$stmt_insert = $conn->prepare($sql_insert);

foreach ($broadcasts as $b) {
    $notification_id = $b['id'];
    $stmt_insert->bind_param("ii", $notification_id, $user_id);
    $stmt_insert->execute();
}


// =======================
// 2️⃣ Xử lý individual
// =======================

// Đánh dấu tất cả thông báo individual gửi riêng cho user này là đã đọc
$sql_individual = "
    UPDATE notification_user
    SET is_read = 1, read_at = NOW()
    WHERE user_id = ? AND is_read = 0
";

$stmt_update = $conn->prepare($sql_individual);
$stmt_update->bind_param("i", $user_id);
$stmt_update->execute();


// =======================
// ✅ Phản hồi thành công
// =======================
echo json_encode(['success' => true]);
?>
