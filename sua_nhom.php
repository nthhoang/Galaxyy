<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/db.php';

if (!isset($_SESSION['user_id'])) {
    // Người dùng chưa đăng nhập
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = $_POST['group_id'] ?? null;
$name = $_POST['name'] ?? '';
$desc = $_POST['description'] ?? '';
$cover_path = null;

if (!$group_id || empty($name)) {
    // Dữ liệu không hợp lệ
    header("Location: nhom.php?error=invalid_data");
    exit();
}

// Kiểm tra quyền sửa: chỉ người tạo mới được sửa nhóm
$stmt_check = $conn->prepare("SELECT * FROM groups WHERE id = ? AND created_by = ?");
$stmt_check->bind_param("ii", $group_id, $user_id);
$stmt_check->execute();
$result = $stmt_check->get_result();
if ($result->num_rows === 0) {
    // Không tìm thấy nhóm hoặc không phải người tạo
    header("Location: nhom.php?error=unauthorized");
    exit();
}

// Nếu có ảnh mới thì xử lý upload ảnh
if (!empty($_FILES['cover_image']['name'])) {
    $filename = time() . '_' . basename($_FILES['cover_image']['name']);
    $upload_dir = '/galaxy/uploads/group_covers/';
    $target_path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . $filename;

    if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
        $cover_path = $upload_dir . $filename;

        // Cập nhật cả ảnh
        $stmt = $conn->prepare("UPDATE groups SET name = ?, description = ?, cover_image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $desc, $cover_path, $group_id);
    } else {
        // Nếu upload thất bại
        header("Location: nhom.php?error=upload_fail");
        exit();
    }
} else {
    // Cập nhật không có ảnh
    $stmt = $conn->prepare("UPDATE groups SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $desc, $group_id);
}

$stmt->execute();

// Chuyển hướng lại
header("Location: nhom.php?success=updated");
exit();
