<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'db.php';         // file kết nối database
require './vendor/autoload.php'; // PHPMailer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email không hợp lệ!");
    }

    // Chuẩn bị truy vấn insert
    try {
    $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        sendConfirmMail($email);
        header("Location: /galaxy/trangchu.php?success=1");
        exit;
    }
} catch (mysqli_sql_exception $e) {
    // Kiểm tra có phải lỗi duplicate email không
    if ($e->getCode() == 1062) {
        header("Location: /galaxy/trangchu.php?success=2"); // email đã tồn tại
        exit;
    } else {
        header("Location: /galaxy/trangchu.php?success=0"); // lỗi khác
        exit;
    }
} finally {
    $stmt->close();
    $conn->close();
}

}

// Hàm gửi email xác nhận
function sendConfirmMail($toEmail) {
    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP (bạn có thể dùng y hệt cấu hình khôi phục mật khẩu)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tamhuyhoangnguyen@gmail.com'; // THAY bằng Gmail của bạn
        $mail->Password   = 'uamv izyz zacv jcxm';        // THAY bằng App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';
        $mail->Port       = 587;

        // Người gửi
        $mail->setFrom('tamhuyhoangnguyen@gmail.com', 'Galaxy Newsletter');
        // Người nhận
        $mail->addAddress($toEmail);

        // Nội dung mail
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đăng ký nhận tin';
        $mail->Body    = "<h3>Chào bạn,</h3>
                          <p>Bạn đã đăng ký nhận tin thành công từ Galaxy.</p>
                          <p>Từ nay bạn sẽ nhận được thông báo khi có bài viết mới.</p>
                          <br>
                          <p><i>Trân trọng,<br>Đội ngũ Galaxy</i></p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
         return false;
    }
}
?>
