<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/db.php'; 
$user_id = $_SESSION['user_id'];

// Lấy tất cả broadcast (và trạng thái đọc), kết hợp với individual gửi riêng cho user này
$sql = "
    (
        SELECT n.id, n.message, n.created_at, n.id_news, n.id_post, 
               COALESCE(nu.is_read, 0) AS is_read
        FROM notifications n
        LEFT JOIN notification_user nu
               ON nu.notification_id = n.id AND nu.user_id = ?
        WHERE n.type = 'broadcast'
    )
    UNION ALL
    (
        SELECT n.id, n.message, n.created_at, n.id_news, n.id_post,
               nu.is_read
        FROM notification_user nu
        JOIN notifications n ON nu.notification_id = n.id
        WHERE nu.user_id = ? AND n.type = 'individual'
    )
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Đếm số broadcast chưa đọc
$sql = "
    SELECT COUNT(*) AS unread_broadcast
    FROM notifications n
    LEFT JOIN notification_user nu
           ON nu.notification_id = n.id AND nu.user_id = ?
    WHERE n.type = 'broadcast'
      AND (nu.is_read IS NULL OR nu.is_read = 0)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unread_broadcast = intval($row['unread_broadcast']);


// Đếm số individual chưa đọc
$sql = "
    SELECT COUNT(*) AS unread_individual
    FROM notification_user nu
    JOIN notifications n ON nu.notification_id = n.id
    WHERE nu.user_id = ? AND nu.is_read = 0 AND n.type = 'individual'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unread_individual = intval($row['unread_individual']);


// Tổng số chưa đọc
$unreadCount = $unread_broadcast + $unread_individual;

?>