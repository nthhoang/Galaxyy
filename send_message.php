<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    // Không cho phép gửi nếu chưa đăng nhập
    http_response_code(403);
    exit('Bạn cần đăng nhập để thực hiện hành động này.');
}

$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
$sender_id = $_SESSION['user_id'];
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$image_path = null;

// Kiểm tra xem người dùng có quyền trong cuộc trò chuyện này không
// (Thêm bước này để tăng bảo mật)
$stmt_check = $conn->prepare("SELECT id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
$stmt_check->bind_param("iii", $conversation_id, $sender_id, $sender_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    http_response_code(403);
    exit('Không có quyền truy cập cuộc trò chuyện này.');
}
$stmt_check->close();

// Xử lý upload ảnh
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $image = $_FILES['image'];
    
    // --- BẢO MẬT: KIỂM TRA QUAN TRỌNG ---
    // 1. Kiểm tra kích thước file (ví dụ: tối đa 5MB)
    if ($image['size'] > 5 * 1024 * 1024) {
        // Có thể redirect về với một thông báo lỗi
        header("Location: message.php?conversation_id=$conversation_id&error=image_too_large");
        exit();
    }

    // 2. Kiểm tra loại file (chỉ cho phép các định dạng ảnh phổ biến)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $image['tmp_name']);
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime_type, $allowed_mime_types)) {
        finfo_close($finfo);
        header("Location: message.php?conversation_id=$conversation_id&error=invalid_file_type");
        exit();
    }
    finfo_close($finfo);

    // Tạo tên tệp duy nhất để tránh ghi đè
    $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
    $filename = $upload_dir . 'chat_' . uniqid() . '_' . time() . '.' . $extension;
    
    if (move_uploaded_file($image['tmp_name'], $filename)) {
        $image_path = $filename;
    }
}

// Chỉ lưu vào DB nếu có tin nhắn hoặc có ảnh
if (!empty($message) || !empty($image_path)) {
    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, message, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $conversation_id, $sender_id, $message, $image_path);
    $stmt->execute();
    $stmt->close();

    // Cập nhật trường `updated_at` của bảng `conversations` để đẩy cuộc trò chuyện lên đầu
    $stmt_update = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $stmt_update->bind_param("i", $conversation_id);
    $stmt_update->execute();
    $stmt_update->close();
}


header("Location: message.php?conversation_id=$conversation_id");
exit();