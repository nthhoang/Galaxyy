<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'message'=>"Bạn cần đăng nhập để quan tâm."]);
    exit;
}
if (!isset($_POST['id'])) {
    echo json_encode(['success'=>false, 'message'=>"Invalid request"]);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$event_id = intval($_POST['id']);

// Kiểm tra đã tồn tại chưa
$stmt = $conn->prepare("SELECT 1 FROM event_cares WHERE user_id=? AND event_id=?");
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    // Thêm vào bảng event_cares
    $stmt = $conn->prepare("INSERT INTO event_cares (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();

    // Tăng care trong events
    $stmt = $conn->prepare("UPDATE events SET care = care + 1 WHERE id=?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
}

// Lấy số lượng hiện tại
$stmt = $conn->prepare("SELECT care FROM events WHERE id=?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$count = (int)$result['care'];

echo json_encode(['success'=>true, 'count'=>$count]);
exit;
?>
