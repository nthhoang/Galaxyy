<?php
session_start();
$loggedIn = isset($_SESSION['username']);
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/lang.php'; 
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/load_noti.php';
?>
<?php
require_once 'db.php';

// 1. Xác định các cột theo ngôn ngữ
$title_col = ($current_lang == 'en') ? 'title_en' : 'title_vi';
$category_col = ($current_lang == 'en') ? 'category_en' : 'category_vi';
$excerpt_col = ($current_lang == 'en') ? 'excerpt_en' : 'excerpt_vi';


// lấy các thẻ category
$category_query = "SELECT DISTINCT $category_col AS category FROM news ORDER BY $category_col ASC";
$category_result = $conn->query($category_query);

$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// 2. Nhận tham số GET
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Số bài viết mỗi trang
$offset = ($page - 1) * $limit;

// 3. Truy vấn tổng số bài viết để tính phân trang
$count_sql = "SELECT COUNT(*) AS total FROM news WHERE 1=1";
$count_params = [];
$count_types = "";

if (!empty($cat)) {
    $count_sql .= " AND {$category_col} = ?";
    $count_params[] = $cat;
    $count_types .= "s";
}
if (!empty($search_term)) {
    $count_sql .= " AND (title_vi LIKE ? OR excerpt_vi LIKE ? OR title_en LIKE ? OR excerpt_en LIKE ?)";
    $search_like = "%" . $search_term . "%";
    array_push($count_params, $search_like, $search_like, $search_like, $search_like);
    $count_types .= "ssss";
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_row = $count_result->fetch_assoc();
$total_news = $total_row['total'];
$total_pages = ceil($total_news / $limit);
$count_stmt->close();

// 4. Truy vấn dữ liệu bài viết thực tế
$sql = "SELECT id, {$title_col} AS title, {$category_col} AS category, {$excerpt_col} AS excerpt, image_url, created_at FROM news WHERE 1=1";
$params = [];
$types = "";

if (!empty($cat)) {
    $sql .= " AND {$category_col} = ?";
    $params[] = $cat;
    $types .= "s";
}
if (!empty($search_term)) {
    $sql .= " AND (title_vi LIKE ? OR excerpt_vi LIKE ? OR title_en LIKE ? OR excerpt_en LIKE ?)";
    $search_like = "%" . $search_term . "%";
    array_push($params, $search_like, $search_like, $search_like, $search_like);
    $types .= "ssss";
}

$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// 5. Thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// 6. Lưu dữ liệu vào mảng
$all_news = [];
while ($row = $result->fetch_assoc()) {
    $all_news[] = $row;
}
$stmt->close();


// Tin nhiều lượt xem
$sql_views = "SELECT id, {$title_col} AS title, image_url, views FROM news ORDER BY views DESC LIMIT 3";
$result_views = $conn->query($sql_views);
$popular_news = $result_views->fetch_all(MYSQLI_ASSOC);

// tin tức mới nhất
$sql_latest = "SELECT id, {$title_col} AS title, {$excerpt_col} AS excerpt, image_url, created_at FROM news ORDER BY created_at DESC LIMIT 3";
$result_latest = $conn->query($sql_latest);
$latest_news = $result_latest->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Tức & Khám Phá Vũ Trụ</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/galaxy/css/header.css">
    <link rel="stylesheet" href="/galaxy/css/tintuc.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/galaxy/css/noti.css">
    <link rel="stylesheet" href="bot_cosmos/static/style.css">
</head>
<body>
    <header id="head"> 
        <div class="logo-container">
            <img src="/galaxy/images-icon/logo3.png" alt="logonhom" class="logo-overlay">
        </div>
        <div id="menuhead">
            <nav>
                <button id="menu-toggle" aria-label="Mở menu">☰</button>
                <ul id="main-menu">
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
                    </li>
                    <li><a href="congdong.php" class="active"><img src="/galaxy/images-icon/group (1).png" alt=""><?= t('4') ?></a></li>
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
                                <input type="checkbox" id="lang-toggle" class="lang-toggle-checkbox" <?php if(isset($current_lang)) echo ($current_lang == 'en') ? 'checked' : ''; ?>>
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
    <br><br>

    <main class="container my-5">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <form action="category.php" method="GET" class="search-form">
                    <input type="hidden" name="cat" value="<?= htmlspecialchars($_GET['cat'] ?? '') ?>">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-lg" placeholder="<?= t('tintuc-1') ?>" value="<?= htmlspecialchars($search_term) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-lg px-4" type="submit"><?= t('tintuc-2') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="other-news">
            <div class="row">
                  <?php
                    // Nhóm tin tức theo chuyên mục (category)
                    $grouped_news = [];
                    foreach ($all_news as $news) {
                        $cat = $news['category'];
                        if (!isset($grouped_news[$cat])) {
                            $grouped_news[$cat] = [];
                        }
                        $grouped_news[$cat][] = $news;
                    }
                    ?>
                <div class="col-md-8">
                    <?php
                    // Lấy category từ URL
                    $cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
                    ?>
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="tintuc.php"  class="text-decoration-none"><?= t('tintuc') ?></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="category.php?cat=<?= urlencode($cat) ?>"  class="text-decoration-none"><?= htmlspecialchars($cat) ?></a>
                            </li>
                        </ol>
                    </nav>

                    <?php if(!empty($search_term)): ?>
                        <h3 class="text-white mb-4"><?= t('tintuc-3') ?>"<?= htmlspecialchars($search_term) ?>"</h3>
                    <?php endif; ?>

                    <?php foreach ($grouped_news as $category => $news_list): ?>
                        <div class="mb-5"> <!-- Khoảng cách giữa các nhóm -->
                            <h3 class="section-title mb-3"><?= htmlspecialchars($category) ?></h3>
                            <div class="row">
                                <?php foreach (array_slice($news_list, 0, 6) as $row): ?>
                                    <div class="col-md-12 col-lg-6 mb-4" data-aos="fade-up" data-aos-duration="800">
                                        <div class="card news-card h-100">
                                            <img src="<?= htmlspecialchars($row['image_url'] ?: 'assets/images/default-news.jpg') ?>" class="card-img-top news-card-img" alt="<?= htmlspecialchars($row['title']) ?>">
                                                <div class="card-body d-flex flex-column">
                                                    <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                                    <p class="card-text small text-muted"><?= htmlspecialchars($row['category']) ?> - <?= date("d/m/Y", strtotime($row['created_at'])) ?></p>
                                                    <p class="card-text flex-grow-1"><?= htmlspecialchars($row['excerpt']) ?></p>
                                                    <a href="view_news.php?id=<?= $row['id'] ?>" class="btn btn-custom mt-auto align-self-start"><?= t('tintuc-4') ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php 
                                endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                   <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);

                                // Trang đầu
                                if ($start > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?cat=' . urlencode($cat) . '&search=' . urlencode($search_term) . '&page=1">1</a></li>';
                                }

                                // Dấu ...
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }

                                // Các trang ở giữa
                                for ($i = $start; $i <= $end; $i++) {
                                    $active = ($i == $page) ? 'active' : '';
                                    echo '<li class="page-item ' . $active . '">
                                        <a class="page-link" href="?cat=' . urlencode($cat) . '&search=' . urlencode($search_term) . '&page=' . $i . '">' . $i . '</a>
                                    </li>';
                                }

                                // Dấu ...
                                if ($end < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }

                                // Trang cuối
                                if ($end < $total_pages) {
                                    echo '<li class="page-item"><a class="page-link" href="?cat=' . urlencode($cat) . '&search=' . urlencode($search_term) . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    <?php endif; ?>


                </div>
                 <!-- Cột phải: Tin đặc biệt -->
                <div class="col-md-4">
                    <!-- Tin nhiều lượt xem -->
                    <h5 class="mt-5 pt-4 mb-3 section-title">🔥 Tin nhiều lượt xem</h5>
                    <?php foreach ($popular_news as $news): ?>
                        <div class="d-flex mb-3 border-bottom pb-3 align-items-center">
                            <img src="<?= htmlspecialchars($news['image_url'] ?: 'assets/images/default-news.jpg') ?>"
                                class="me-3 rounded news-thumbnail"
                                alt="<?= htmlspecialchars($news['title']) ?>">
                            <div>
                                <a href="view_news.php?id=<?= $news['id'] ?>" class="news-link fw-bold d-block text-decoration-none" ><?= htmlspecialchars($news['title']) ?></a>
                                <small class="luotxem"><?= $news['views'] ?> lượt xem</small>
                            </div>
                        </div>
                    <?php endforeach; ?>


                    <!-- Tin mới nhất -->
                    <h5 class="mt-4 mb-3 section-title">🕒 Tin mới nhất</h5>
                    <?php foreach ($latest_news as $news): ?>
                    <div class="d-flex mb-3 border-bottom pb-2 align-items-center">
                        <img src="<?= htmlspecialchars($news['image_url'] ?: 'assets/images/default-news.jpg') ?>"
                                class="me-3 rounded news-thumbnail"
                                alt="<?= htmlspecialchars($news['title']) ?>">
                        <div>
                        <a href="view_news.php?id=<?= $news['id'] ?>" class="news-link d-block text-decoration-none">
                        <?= htmlspecialchars($news['title']) ?>
                        </a>
                        <small class="ngaydang"><?= date("d/m/Y", strtotime($news['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Lọc theo ngày -->
                    <h5 class="mt-4 mb-3">📅 Lọc theo ngày</h5>
                    <form method="GET" action="news_by_date.php">
                        <input type="date" name="date" class="form-control mb-2" required>
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Xem tin</button>
                    </form>
                    
                      <!-- Hiện các thẻ category -->
                     <h5 class="mt-4 mb-3 section-title"><?= t('tag-categoty') ?></h5>
                    <div class="mb-4">
                        <?php foreach ($categories as $cat): ?>
                            <a href="category.php?cat=<?= urlencode($cat) ?>" class="btn btn-outline-secondary btn-sm mx-1 mb-2">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($cat) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script src="/galaxy/js/tintuc.js"></script>

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