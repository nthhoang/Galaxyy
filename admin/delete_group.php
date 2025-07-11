<?php
require_once 'check_admin.php';

$group_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($group_id <= 0) {
    die("ID nhóm không hợp lệ.");
}

// Xóa yêu cầu tham gia nhóm
$conn->query("DELETE FROM group_join_requests WHERE group_id = $group_id");

// Xóa thành viên trong nhóm
$conn->query("DELETE FROM group_members WHERE group_id = $group_id");

// Xóa bài viết trong nhóm
$conn->query("DELETE FROM group_posts WHERE group_id = $group_id");

// Xóa nhóm chính
$conn->query("DELETE FROM groups WHERE id = $group_id");

header("Location: group_management.php");
exit();
?>
