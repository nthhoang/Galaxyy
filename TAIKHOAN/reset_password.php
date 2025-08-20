<?php
require_once '../db.php';

// Đặt múi giờ VN
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Nếu form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = ["error", "❌ Mật khẩu xác nhận không khớp."];
    } elseif (strlen($new_password) < 8) {
        $message = ["error", "⚠️ Mật khẩu mới phải có ít nhất 8 ký tự."];
    } else {
        // Kiểm tra token hợp lệ
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Token hợp lệ → cập nhật mật khẩu
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
            $update_stmt->bind_param("ss", $hashed_password, $token);

            if ($update_stmt->execute()) {
                $message = ["success", "✅ Cập nhật mật khẩu thành công! <br> <a href='login-register.html'>Đăng nhập ngay</a>"];
            } else {
                $message = ["error", "⚠️ Có lỗi xảy ra, vui lòng thử lại."];
            }
        } else {
            $message = ["error", "⏰ Token không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu lại quên mật khẩu."];
        }
    }
} else {
    // Nếu mở lần đầu bằng link reset
    if (!isset($_GET['token'])) {
        die("Yêu cầu không hợp lệ.");
    }
    $token = $_GET['token'];

    // Kiểm tra token
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Token không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.");
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại Mật khẩu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;700&display=swap');
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?q=80&w=2071') no-repeat center center;
            background-size: cover;
            font-family: 'Be Vietnam Pro', sans-serif;
        }
        .form-box {
            width: 400px;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            color: #fff;
            text-align: center;
        }
        h2 {
            margin-bottom: 1.5rem;
        }
        .input-group {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            width: 100%;
            margin: 15px 0;
            display: flex;
            align-items: center;
            padding: 0 15px;
        }
        .input-group i {
            color: #ccc;
            margin-right: 10px;
        }
        input {
            width: 100%;
            padding: 14px 10px;
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: 14px;
        }
        button {
            border-radius: 8px;
            border: 1px solid #00ffff;
            background: #00ffff;
            color: #000;
            font-weight: bold;
            padding: 12px 45px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 1rem;
            transition: 0.3s;
        }
        button:hover {
            background: transparent;
            color: #00ffff;
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .message.success {
            background: rgba(0,255,100,0.2);
            border: 1px solid #00ff88;
            color: #00ff88;
        }
        .message.error {
            background: rgba(255,50,50,0.2);
            border: 1px solid #ff5050;
            color: #ff5050;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>🔑 Đặt lại mật khẩu</h2>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $message[0]; ?>">
                <?php echo $message[1]; ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($message) || $message[0] === "error"): ?>
        <form action="" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required>
            </div>
            
            <button type="submit">Cập nhật mật khẩu</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
