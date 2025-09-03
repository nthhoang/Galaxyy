<?php
require_once 'check_admin.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'], $_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Duyệt: Cập nhật status thành 'approved'
        $stmt = $conn->prepare("UPDATE cosmic_media SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['admin_message'] = "Đã duyệt thành công.";
        } else {
            $_SESSION['admin_message'] = "Lỗi khi duyệt: " . $stmt->error;
        }
        $stmt->close();

    } elseif ($action == 'delete') {
        // Xóa: Lấy tên tệp, xóa tệp, sau đó xóa bản ghi trong DB
        $stmt_select = $conn->prepare("SELECT file_name FROM cosmic_media WHERE id = ?");
        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $fileName = $row['file_name'];
            $filePath = '../uploads/' . $fileName;

            // Xóa tệp vật lý
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Xóa bản ghi trong database
            $stmt_delete = $conn->prepare("DELETE FROM cosmic_media WHERE id = ?");
            $stmt_delete->bind_param("i", $id);
            if ($stmt_delete->execute()) {
                $_SESSION['admin_message'] = "Đã xóa thành công.";
            } else {
                $_SESSION['admin_message'] = "Lỗi khi xóa khỏi database: " . $stmt_delete->error;
            }
            $stmt_delete->close();
        } else {
             $_SESSION['admin_message'] = "Lỗi: Không tìm thấy media để xóa.";
        }
        $stmt_select->close();
    }
    
    $conn->close();
    header('Location: galaxy_lib_admin.php');
    exit();
} else {
    $_SESSION['admin_message'] = "Yêu cầu không hợp lệ.";
    header('Location: galaxy_lib_admin.php');
    exit();
}
?>
