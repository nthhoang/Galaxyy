<?php
require_once 'db.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["mediaFile"])) {
    
    // Yêu cầu người dùng phải đăng nhập để upload
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        $_SESSION['message'] = "Lỗi: Bạn phải đăng nhập để thực hiện hành động này.";
        header("Location: galaxy_lib.php#upload-section");
        exit();
    }

    $targetDir = "uploads/";
    $fileName = basename($_FILES["mediaFile"]["name"]);
    $originalFileName = $fileName;
    // Tạo tên tệp duy nhất để tránh trùng lặp
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetFilePath = $targetDir . $uniqueFileName;
    $fileType = $_FILES["mediaFile"]["type"];
    
    // Lấy tên người đăng từ session thay vì form
    $uploaderName = $_SESSION['username'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    // Các loại tệp cho phép
    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $allowedVideoTypes = ['mp4', 'avi', 'mov', 'mpeg', 'webm'];

    $fileExtension = strtolower($fileExtension);
    $mediaType = '';
    
    if (in_array($fileExtension, $allowedImageTypes)) {
        $mediaType = 'image';
    } elseif (in_array($fileExtension, $allowedVideoTypes)) {
        $mediaType = 'video';
    } else {
        $_SESSION['message'] = "Lỗi: Định dạng tệp không được hỗ trợ. Chỉ chấp nhận ảnh (JPG, PNG, GIF) và video (MP4, AVI, MOV).";
        header("Location: galaxy_lib.php#upload-section");
        exit();
    }
    
    // Kiểm tra kích thước tệp (ví dụ: 50MB)
    if ($_FILES["mediaFile"]["size"] > 50 * 1024 * 1024) {
        $_SESSION['message'] = "Lỗi: Kích thước tệp quá lớn. Vui lòng chọn tệp nhỏ hơn 50MB.";
        header("Location: galaxy_lib.php#upload-section");
        exit();
    }
    
    // Di chuyển tệp đã upload
    if (move_uploaded_file($_FILES["mediaFile"]["tmp_name"], $targetFilePath)) {
        // Chuẩn bị câu lệnh SQL để tránh SQL Injection
        $stmt = $conn->prepare("INSERT INTO cosmic_media (file_name, file_type, uploader_name, description, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssss", $uniqueFileName, $mediaType, $uploaderName, $description);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Tải lên thành công! Nội dung của bạn đang chờ duyệt.";
        } else {
            $_SESSION['message'] = "Tải lên tệp thành công nhưng có lỗi khi lưu vào cơ sở dữ liệu: " . $stmt->error;
            // Xóa tệp nếu không lưu được vào DB
            unlink($targetFilePath);
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Đã xảy ra lỗi khi tải tệp lên.";
    }
    
    $conn->close();
    header("Location: galaxy_lib.php#upload-section");
    exit();
} else {
    // Nếu truy cập trực tiếp vào tệp này
    $_SESSION['message'] = "Yêu cầu không hợp lệ.";
    header("Location: galaxy_lib.php");
    exit();
}
?>

