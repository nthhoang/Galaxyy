<?php
include 'db.php';
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'];

$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? OR user_id IS NULL";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

echo json_encode(['success' => true]);
