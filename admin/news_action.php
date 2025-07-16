<?php
require_once 'check_admin.php';

if (isset($_POST['save_news'])) {
    // Lấy dữ liệu từ các trường song ngữ
    $title_vi = $_POST['title_vi'];
    $category_vi = $_POST['category_vi'];
    $excerpt_vi = $_POST['excerpt_vi'];
    $full_content_vi = $_POST['full_content_vi'];
    $title_en = $_POST['title_en'];
    $category_en = $_POST['category_en'];
    $excerpt_en = $_POST['excerpt_en'];
    $full_content_en = $_POST['full_content_en'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;


    // Trường chung
    $image_url = $_POST['current_image'] ?? ''; 

    // Xử lý upload ảnh (giữ nguyên)
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir_physical = '../uploads/news/'; 
        if (!is_dir($upload_dir_physical)) mkdir($upload_dir_physical, 0775, true);
        if (!empty($image_url) && file_exists('..' . $image_url)) unlink('..' . $image_url);
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file_physical = $upload_dir_physical . $file_name;
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file_physical)) {
            $image_url = '/galaxy/uploads/news/' . $file_name; 
        }
    }

    if (isset($_POST['id'])) { // SỬA
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE news SET title_vi=?, category_vi=?, excerpt_vi=?, full_content_vi=?, title_en=?, category_en=?, excerpt_en=?, full_content_en=?, image_url=?, is_featured=? WHERE id=?");
        $stmt->bind_param("ssssssssssi", $title_vi, $category_vi, $excerpt_vi, $full_content_vi, $title_en, $category_en, $excerpt_en, $full_content_en, $image_url, $is_featured, $id);
        $stmt->execute();
    } else { // THÊM
       $stmt = $conn->prepare("INSERT INTO news (title_vi, category_vi, excerpt_vi, full_content_vi, title_en, category_en, excerpt_en, full_content_en, image_url, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssi", $title_vi, $category_vi, $excerpt_vi, $full_content_vi, $title_en, $category_en, $excerpt_en, $full_content_en, $image_url, $is_featured);
    
           $success = $stmt->execute();

    if ($success) {
    $inserted_id = $conn->insert_id;
    // Sau khi lưu bài thành công -> gửi thông báo
    $message = "📰 Admin vừa đăng tin tức mới: \"$title_vi $title_en\"";
    $sql = "INSERT INTO notifications (user_id, message, id_news) VALUES (NULL, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $message, $inserted_id);  // "s" = string, "i" = integer
    $stmt->execute();
    
    $notification_id = $conn->insert_id; 
    // Lấy created_at
    $stmt2 = $conn->prepare("SELECT created_at FROM notifications WHERE id = ?");
    $stmt2->bind_param("i", $notification_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $row = $result->fetch_assoc();
    $created_at = $row['created_at'];

    $data = ['message' => $message, 'id' => $inserted_id, 'created_at' => $created_at];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => json_encode($data),
        ]
    ];
    $context = stream_context_create($options);
    file_get_contents("http://localhost:4000/notify", false, $context);

    } else {
    echo "Lỗi khi lưu bài viết!";
    }

    }

    header("Location: news_management.php");
    exit();
}
?>