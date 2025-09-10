<?php
session_start();
$loggedIn = isset($_SESSION['username']);
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/lang.php'; 
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/load_noti.php';

// 1. Xác định các cột ngôn ngữ
$title_col = ($current_lang == 'en') ? 'title_en' : 'title_vi';
$category_col = ($current_lang == 'en') ? 'category_en' : 'category_vi';
$excerpt_col = ($current_lang == 'en') ? 'excerpt_en' : 'excerpt_vi';

// tin nổi bật
$sql_featured = "SELECT id, {$title_col} AS title, image_url, created_at FROM news WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 5";
$result_featured = $conn->query($sql_featured);
$featured_news = $result_featured->fetch_all(MYSQLI_ASSOC);
?>

<!-- thông báo trạng thái đăng kí email -->
<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == 1): ?>
        <div class="alert alert-success mt-4">
            Bạn đã đăng ký email thành công!
        </div>
    <?php elseif ($_GET['success'] == 2): ?>
        <div class="alert alert-warning mt-4">
            Email này đã được đăng ký trước đó!
        </div>
    <?php else: ?>
        <div class="alert alert-danger mt-4">
            Có lỗi xảy ra, vui lòng thử lại!
        </div>
    <?php endif; ?>
<?php endif; ?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Exo+2:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/galaxy/css/header.css">
    <link rel="stylesheet" href="/galaxy/css/trangchu.css">
    <link rel="stylesheet" href="/galaxy/css/noti.css">
    <link rel="stylesheet" href="/galaxy/css/footer.css">
 
     <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600&display=swap">
    <link rel="stylesheet" href="bot_cosmos/static/style.css">
<body>
   
  <header id="head"> 
    <div class="logo-container">
      <img src="/galaxy/images-icon/logo3.png" alt="logonhom" class="logo-overlay">
    </div>
       <div id="menuhead">
        
        <nav>
           <button id="menu-toggle" aria-label="Mở menu">☰</button>

        <ul id="main-menu" >
    <li><a href="trangchu.php" class="active"><img src="/galaxy/images-icon/home.png" alt=""><?= t('1') ?></a></li>

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
        <a href="#"><img src="/galaxy/images-icon/more.png" alt=""><?= t('6') ?></a>
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

     <main class="sun-content-section">
        <?php
            // 1. Xác định đường dẫn đến tệp nội dung dựa trên ngôn ngữ hiện tại
            $content_file = $_SERVER['DOCUMENT_ROOT'] . "/galaxy/content/trangchu_{$current_lang}.php";

            // 2. Kiểm tra tệp có tồn tại không và đọc toàn bộ nội dung của nó
            if (file_exists($content_file)) {
                include  $content_file;
            } else {
                echo "Nội dung không có sẵn cho ngôn ngữ này.";
            }
        ?>
    </main>
        <?php
            // 1. Xác định đường dẫn đến tệp nội dung dựa trên ngôn ngữ hiện tại
            $content_file = $_SERVER['DOCUMENT_ROOT'] . "/galaxy/footer.html";

            // 2. Kiểm tra tệp có tồn tại không và đọc toàn bộ nội dung của nó
            if (file_exists($content_file)) {
                echo  file_get_contents($content_file);
            } else {
                echo "Nội dung không có sẵn cho ngôn ngữ này.";
            }
        ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="/galaxy/js/trangchu.js"></script>
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