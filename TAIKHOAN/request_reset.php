<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../db.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_fullname = $user['fullname'];

        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", time() + 3600);

        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $token, $expires, $email);
        $update_stmt->execute();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tamhuyhoangnguyen@gmail.com'; // THAY bằng Gmail của bạn
            $mail->Password   = 'uamv izyz zacv jcxm';        // THAY bằng App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('tamhuyhoangnguyen@gmail.com', 'Galaxy Project');
            $mail->addAddress($email, $user_fullname);

            $mail->isHTML(true);
            $mail->Subject = 'Khôi phục mật khẩu - Galaxy';
            $reset_link = "http://localhost/galaxy/TAIKHOAN/reset_password.php?token=" . urlencode($token);

            $mail->Body = "
                Xin chào <b>$user_fullname</b>,<br><br>
                Vui lòng nhấn vào link dưới đây để đặt lại mật khẩu mới:<br>
                <a href='$reset_link' style='padding:10px 15px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>
                    Đặt lại mật khẩu
                </a><br><br>
                Link này sẽ hết hạn sau 1 giờ.
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("Lỗi gửi mail: {$mail->ErrorInfo}");
        }
    }

    echo '
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khôi phục mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: rgba(0, 0, 0, 0.75) url("/galaxy/images-icon/taikhoan.jpg") no-repeat center center; display:flex; justify-content:center; align-items:center; height:100vh;}
        .msg-box { max-width:500px; padding:30px; border-radius:15px; background:black; box-shadow:0px 5px 15px rgba(0,0,0,0.2); text-align:center; }
    </style>
</head>
<body>
    <div class="msg-box">
        <h3 class="text-success">✅ Yêu cầu đã được xử lý</h3>
        <p>Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn khôi phục mật khẩu.</p>
        <a href="login-register.html" class="btn btn-primary mt-3">Quay lại đăng nhập</a>
    </div>
</body>
</html>';
}
?>
