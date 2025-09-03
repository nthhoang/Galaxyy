<?php
session_start();
$loggedIn = isset($_SESSION['username']);
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/lang.php'; 
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/load_noti.php';
// Lấy tất cả media đã được duyệt
$result = $conn->query("SELECT * FROM cosmic_media WHERE status = 'approved' ORDER BY upload_date DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện Vũ trụ - Khám phá và Chia sẻ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Exo+2:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/galaxy/css/header.css">
    <link rel="stylesheet" href="/galaxy/css/noti.css">
 
     <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600&display=swap">
    <link rel="stylesheet" href="bot_cosmos/static/style.css">
    <style>
        body {
            background-color: #0c1427; /* Màu nền vũ trụ tối */
            color: #e0e0e0;
        }
        .card {
            background-color: #1a2035;
            border: 1px solid #303652;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }
        .upload-form {
            background-color: #1a2035;
            border: 1px solid #303652;
        }
    </style>
</head>
<body>
    <header id="head"> 
    <div class="logo-container">
      <img src="/galaxy/images-icon/logo3.png" alt="logonhom" class="logo-overlay">
    </div>
       <div id="menuhead">
        
        <nav>
           <button id="menu-toggle" aria-label="Mở menu">☰</button>

        <ul id="main-menu" >
    <li><a href="trangchu.php"><img src="/galaxy/images-icon/home.png" alt=""><?= t('1') ?></a></li>

    <li class="dropdown">
        <a href="#"><img src="/galaxy/images-icon/hemattroi.png" alt=""><?= t('2') ?></a>
        <div class="dropdown-content">
            <a class="item" href="/galaxy/hemattroi/mattroi.php"><img src="/galaxy/images-icon/sun.png" alt=""><?= t('2,1') ?></a>
            <a class="item" href="/galaxy/hemattroi/saothuy.php"><img src="/galaxy/images-icon/mercury.png" alt=""><?= t('2,2') ?></a>
            <a class="item" href="/galaxy/hemattroi/saokim.php"><img src="/galaxy/images-icon/venus.png" alt=""><?= t('2,3') ?></a>
            <a class="item" href="/galaxy/hemattroi/traidat.php"><img src="/galaxy/images-icon/earth.png" alt=""><?= t('2,4') ?></a>
            <a class="item" href="/galaxy/hemattroi/mattrang.php"><img src="/galaxy/images-icon/full-moon.png" alt=""><?= t('2,5') ?> </a>
            <a class="item" href="/galaxy/hemattroi/saohoa.php"><img src="/galaxy/images-icon/mars.png" alt=""><?= t('2,6') ?></a>
            <a class="item" href="/galaxy/hemattroi/saomoc.php"><img src="/galaxy/images-icon/jupiter.png" alt=""><?= t('2,7') ?></a>
            <a class="item" href="/galaxy/hemattroi/saotho.php"><img src="/galaxy/images-icon/saturn.png" alt=""><?= t('2,8') ?></a>
            <a class="item" href="/galaxy/hemattroi/saothienvuong.php"><img src="/galaxy/images-icon/uranus.png" alt=""><?= t('2,9') ?></a>
            <a class="item" href="/galaxy/hemattroi/saohaivuong.php"><img src="/galaxy/images-icon/neptune.png" alt=""><?= t('2,10') ?></a>
        </div>
    </li>

    <li class="dropdown">
        <a href="#"><img src="/galaxy/images-icon/black-hole.png" alt=""><?= t('3') ?></a>
        <div class="dropdown-content">
            <a class="item" href="vutru.php"><img src="/galaxy/images-icon/vutru.png" alt=""><?= t('3,3') ?> </a>
            <a class="item" href="sukien.php"><img src="/galaxy/images-icon/sukien.png" alt=""><?= t('3,1') ?> </a>
            <a class="item" href="tintuc.php"><img src="/galaxy/images-icon/news.png" alt=""><?= t('3,2') ?> </a>
        </div>
    </li> <li><a href="congdong.php"><img src="/galaxy/images-icon/group (1).png" alt=""><?= t('4') ?></a></li>

    <li>
        <a href="<?php echo $loggedIn ? 'taikhoan.php' : './TAIKHOAN/login-register.html'; ?>">
            <img src="/galaxy/images-icon/dangnhap.png" alt=""><?= t('5') ?>
        </a>
    </li>

    <li class="dropdown">
        <a href="#"  class="active"><img src="/galaxy/images-icon/more.png" alt=""><?= t('6') ?></a>
        <div class="dropdown-content" style="left: -170%">
            <a class="item" href="galaxy_lib.php"><img src="/galaxy/images-icon/group.png" alt=""><?= t('6,2') ?></a>
            <a class="item" href="vechungtoi.php"><img src="/galaxy/images-icon/group.png" alt=""><?= t('6,1') ?></a>
            <a class="language-switcher-container">
        <input type="checkbox" id="lang-toggle" class="lang-toggle-checkbox"
               <?php if(isset($current_lang)) echo ($current_lang == 'en') ? 'checked' : ''; ?>
        >
        <label for="lang-toggle" class="lang-toggle-label">
            <span class="lang-toggle-inner"></span>
            <span class="lang-toggle-switch"></span>
        </label>
             </a>
        </div>
    </li> 
</ul>
 </nav>  
        <div id="notification-wrapper"  style="position: relative;">
        <i id="notification-bell" class="fa fa-bell"></i>
        <span id="notification-count">0</span>
        <div id="notification-list">
            <ul id="notification-items" style="display: block;"></ul>
        </div>
        </div>
</div>
     
</header>
    <div class="container mx-auto p-4 md:p-8 mt-5 pt-5">
        <header class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-2">Thư viện Vũ trụ</h1>
            <p class="text-lg text-gray-300">Nơi cộng đồng chia sẻ những hình ảnh và video kỳ vĩ về không gian.</p>
        </header>

        <!-- Form Upload -->
        <section id="upload-section" class="mb-12">
            <div class="max-w-2xl mx-auto p-6 rounded-lg shadow-lg upload-form">
                <h2 class="text-2xl font-semibold mb-4 text-center text-white">Chia sẻ khoảnh khắc của bạn</h2>
                
                <!-- Hiển thị thông báo -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="p-4 mb-4 text-sm rounded-lg <?php echo strpos($_SESSION['message'], 'lỗi') !== false || strpos($_SESSION['message'], 'thất bại') !== false ? 'bg-red-800 text-red-200' : 'bg-green-800 text-green-200'; ?>" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <?php 
                // Kiểm tra xem người dùng đã đăng nhập chưa (thông qua session 'username')
                if (isset($_SESSION['username']) && !empty($_SESSION['username'])): 
                ?>
                    <p class="text-center text-gray-300 mb-4">Đăng với tư cách: <strong class="text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                    <form action="upload_lib.php" method="post" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="mediaFile" class="block mb-2 text-sm font-medium text-gray-300">Chọn Ảnh hoặc Video</label>
                            <input type="file" name="mediaFile" id="mediaFile" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-700 file:text-white hover:file:bg-violet-800" required>
                            <p class="mt-1 text-xs text-gray-500">Hỗ trợ PNG, JPG, GIF, MP4, MOV, AVI. Tối đa 50MB.</p>
                        </div>
                        <div class="mb-6">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-300">Mô tả</label>
                            <textarea name="description" id="description" rows="3" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ví dụ: Tinh vân Orion chụp từ..."></textarea>
                        </div>
                        <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Gửi lên Vũ trụ</button>
                    </form>
                <?php else: ?>
                    <div class="text-center p-4 border border-dashed border-gray-600 rounded-lg">
                        <p class="text-gray-300">Vui lòng <a href="/login.php" class="font-medium text-blue-400 hover:underline">đăng nhập</a> để chia sẻ hình ảnh và video của bạn.</p>
                        <p class="text-xs text-gray-500 mt-2">Chức năng này dành cho các thành viên của cộng đồng.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Thư viện Media -->
        <main id="gallery" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card rounded-lg overflow-hidden shadow-lg">
                        <?php
                            $filePath = 'uploads/' . htmlspecialchars($row['file_name']);
                            if ($row['file_type'] == 'image'):
                        ?>
                            <img src="<?php echo $filePath; ?>" alt="<?php echo htmlspecialchars($row['description']); ?>" class="w-full h-56 object-cover" loading="lazy">
                        <?php else: ?>
                            <video controls class="w-full h-56 object-cover" preload="metadata">
                                <source src="<?php echo $filePath; ?>">
                                Trình duyệt của bạn không hỗ trợ video tag.
                            </video>
                        <?php endif; ?>
                        <div class="p-4">
                            <p class="text-gray-300 text-sm mb-2"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <p class="text-xs text-gray-500">Đăng bởi: <?php echo htmlspecialchars($row['uploader_name']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="col-span-full text-center text-gray-400">Chưa có hình ảnh hoặc video nào. Hãy là người đầu tiên đóng góp!</p>
            <?php endif; ?>
        </main>
    </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.socket.io/4.7.1/socket.io.min.js"></script>

  <script>
    const storedNotifications = <?php echo json_encode($notifications); ?>;
    let notificationCount = <?php echo $unreadCount; ?>;
    let notifications = storedNotifications;
    const user_id = "<?php echo $_SESSION['user_id']; ?>";
  </script>
  <script src="/galaxy/js/noti.js"></script>

  <?php include 'bot_cosmos/templates/chat_window.html'; ?>
</body>
</html>
<?php
$conn->close();
?>

