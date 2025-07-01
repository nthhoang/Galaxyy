<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/db.php';

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$desc = $_POST['description'];
$cover_path = null;

if (!empty($_FILES['cover_image']['name'])) {
    $filename = time() . '_' . basename($_FILES['cover_image']['name']);
    $upload_dir = '/galaxy/uploads/group_covers/';
    $target_path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . $filename;

    if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
        $cover_path = $upload_dir . $filename;
    }
}

$stmt = $conn->prepare("INSERT INTO groups (name, description, cover_image, created_by) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $name, $desc, $cover_path, $user_id);
$stmt->execute();

header("Location: nhom.php");
exit();
