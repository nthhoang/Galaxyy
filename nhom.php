<!-- nhom.php -->
<?php
// kiểm tra đăng nhập
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /galaxy/TAIKHOAN/login-register.html");
    exit();
}
    require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/lang.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/load_noti.php';
    $loggedIn = true;
    $current_user_id = $_SESSION['user_id'];

        // Lấy avatar của người dùng đang đăng nhập để hiển thị trong form
    $stmt_user_avatar = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt_user_avatar->bind_param("i", $current_user_id);
    $stmt_user_avatar->execute();
    $user_result = $stmt_user_avatar->get_result()->fetch_assoc();
    $currentUserAvatar = !empty($user_result['avatar']) ? htmlspecialchars($user_result['avatar']) : '/galaxy/images-icon/default_avatar.png';
    $stmt_user_avatar->close();

// 1. Nhóm của bạn
$stmt = $conn->prepare("
    SELECT 
        g.*, 
        COUNT(gm.user_id) AS member_count 
    FROM groups g
    LEFT JOIN group_members gm ON g.id = gm.group_id
    WHERE g.created_by = ?
    GROUP BY g.id
    ORDER BY g.created_at DESC
");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$my_groups = $stmt->get_result();

// 2. Nhóm đã tham gia (trừ nhóm do mình tạo)
$stmt = $conn->prepare("
    SELECT 
        g.*, 
        COUNT(gm.user_id) AS member_count
    FROM groups g
    LEFT JOIN group_members gm ON g.id = gm.group_id
    WHERE g.id IN (
        SELECT group_id FROM group_members WHERE user_id = ?
    )
    AND g.created_by != ?
    GROUP BY g.id
    ORDER BY g.created_at DESC
");
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$joined_groups = $stmt->get_result();

// 3. Gợi ý nhóm (không tạo và chưa tham gia)
$stmt = $conn->prepare("
    SELECT 
        g.*, 
        COUNT(gm.user_id) AS member_count
    FROM groups g
    LEFT JOIN group_members gm ON g.id = gm.group_id
    WHERE g.id NOT IN (
        SELECT group_id FROM group_members WHERE user_id = ?
    )
    AND g.created_by != ?
    GROUP BY g.id
    ORDER BY RAND() -- Sắp xếp ngẫu nhiên để gợi ý đa dạng hơn
    LIMIT 10 -- Giới hạn số lượng gợi ý
");
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$suggested_groups = $stmt->get_result();

// Hàm để render một mục trong danh sách nhóm
function render_group_item($group, $type = 'suggested') {
    // Giả sử bạn có cột member_count trong CSDL, nếu không có thể bỏ qua
    $member_count = isset($group['member_count']) ? $group['member_count'] . ' thành viên' : '';

    // Tạo placeholder nếu không có ảnh bìa
    $cover_image_html = '<div class="me-3 rounded-circle group-avatar-placeholder"></div>';
    if (!empty($group['cover_image'])) {
        $cover_image_html = '<img src="' . htmlspecialchars($group['cover_image']) . '" alt="cover" class="me-3 rounded-circle group-avatar">';
    }
    ?>

    <li class="list-group-item list-group-item-action d-flex align-items-center p-2">
        <a href="?group_id=<?= $group['id'] ?>" class="d-flex align-items-center text-dark text-decoration-none flex-grow-1 group-info">
            <?= $cover_image_html ?>
            <div class="d-flex flex-column">
                <span class="fw-bold"><?= htmlspecialchars($group['name']) ?></span>
                <?php if ($member_count): ?>
                    <small class="text-muted"><?= $member_count ?></small>
                <?php endif; ?>
            </div>
        </a>
    </li>
    <?php
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Nhóm cộng đồng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/galaxy/css/header.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/galaxy/css/congdong.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/galaxy/css/noti.css">
    <style>
    .card-body .list-group-item {
        border-left: 0;
        border-right: 0;
        border-radius: 0; /* Bỏ bo tròn góc của item */
    }
    /* Xóa viền trên của item đầu tiên */
    .card-body .list-group-item:first-child {
        border-top: 0;
    }
    /* Xóa viền dưới của item cuối cùng */
     .card-body .list-group-item:last-child {
        border-bottom: 0;
    }
    .group-list .list-group-item {
        /* Thêm một đường viền dưới nhẹ nhàng hơn viền mặc định */
        border: none;
        border-bottom: 1px solid #eee;
    }

    .group-list .list-group-item:last-child {
        border-bottom: none;
    }
    
    .group-avatar {
        width: 45px;
        height: 45px;
        object-fit: cover;
    }

    .group-avatar-placeholder {
        width: 45px;
        height: 45px;
        background-color: #e9ecef; /* Màu xám nhạt */
    }

    .group-info {
        /* Đảm bảo tên nhóm không bị tràn nếu quá dài */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .carousel-container {
    position: relative;
    width: 100%;
    max-width: 2000px; /* Điều chỉnh kích thước tối đa của carousel */
    margin: 20px auto;
    overflow: hidden;
    border-radius: 15px;
}

.carousel-slides {
    display: flex;
    animation: slide-animation 15s infinite; /* Tốc độ di chuyển: 15 giây */
}

.carousel-img {
    width: 100%;
    flex-shrink: 0; /* Ngăn hình ảnh bị co lại */
    object-fit: cover;
}

.carousel-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff; /* Chữ màu trắng */
    text-shadow: 
        1px 1px 2px #000, 
        0 0 1em #2196F3, /* Tạo bóng xanh nhạt */
        0 0 0.2em #2196F3; /* Làm bóng rõ hơn */
    font-size: 2.5em; /* Kích thước chữ to hơn */
    font-weight: bold; /* Chữ in đậm */
    font-family: 'Arial', sans-serif; /* Chọn font chữ dễ đọc */
}

@keyframes slide-animation {
    0% { transform: translateX(0%); }
    25% { transform: translateX(-100%); } /* Hình 1 -> 2 */
    50% { transform: translateX(-200%); } /* Hình 2 -> 3 */
    75% { transform: translateX(-300%); } /* Hình 3 -> 4 */
    100% { transform: translateX(-400%); } /* Quay lại hình đầu tiên */
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

<div class="container mt-5 pt-5">
    <div class="row">
        <!-- Danh sách nhóm bên trái -->
        <div class="col-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tất cả nhóm</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTaoNhom">+ Tạo nhóm</button>
    </div>

    <?php if ($my_groups->num_rows > 0): ?>
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h6 class="text-success mb-0">Nhóm của bạn</h6>
            </div>
            <ul class="list-group list-group-flush">
                <?php while ($group = $my_groups->fetch_assoc()): ?>
                    <?php render_group_item($group, 'my'); ?>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($joined_groups->num_rows > 0): ?>
        <div class="card mb-3">
            <div class="card-header bg-white">
                 <h6 class="text-primary mb-0">Nhóm đã tham gia</h6>
            </div>
            <ul class="list-group list-group-flush">
                <?php while ($group = $joined_groups->fetch_assoc()): ?>
                    <?php render_group_item($group, 'joined'); ?>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($suggested_groups->num_rows > 0): ?>
         <div class="card mb-3">
            <div class="card-header bg-white">
                <h6 class="text-muted mb-0">Gợi ý nhóm</h6>
            </div>
            <ul class="list-group list-group-flush">
                <?php while ($group = $suggested_groups->fetch_assoc()): ?>
                    <?php render_group_item($group, 'suggested'); ?>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
        
        <!-- Chi tiết nhóm bên phải -->
        <div class="col-md-8">
             <?php if (isset($_GET['group_id'])):
    $gid = (int)$_GET['group_id'];
    $stmt = $conn->prepare("SELECT * FROM groups WHERE id = ?");
    $stmt->bind_param("i", $gid);
    $stmt->execute();
    $group = $stmt->get_result()->fetch_assoc();

    // Giả sử bạn có $group['id'] và $current_user_id
    $stmt = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group['id'], $current_user_id);
    $stmt->execute();
    $membership_result = $stmt->get_result();
    $is_member = $membership_result->num_rows > 0;

    // Kiểm tra đã gửi yêu cầu tham gia chưa
    $stmt = $conn->prepare("SELECT 1 FROM group_join_requests WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group['id'], $current_user_id);
    $stmt->execute();
    $has_pending_request = $stmt->get_result()->num_rows > 0;
    // Đếm số lượng thành viên trong nhóm
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM group_members WHERE group_id = ?");
    $stmt->bind_param("i", $gid);
    $stmt->execute();
    $result = $stmt->get_result();
    $memberCount = 0;
    if ($row = $result->fetch_assoc()) {
        $memberCount = $row['total'];
    }
?>

            <div class="card">
                <?php if (!empty($group['cover_image'])): ?>
                        <img src="<?= htmlspecialchars($group['cover_image']) ?>" 
                            class="card-img-top" 
                            alt="Cover"
                            style="height: 350px; object-fit: cover; width: 100%;">
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded shadow-sm mb-3">
                            <div class="me-3">
                                <h4 class="mb-1 text-primary"><?= htmlspecialchars($group['name']) ?></h4>
                                
                                <!-- Hiển thị icon công khai hoặc riêng tư + số thành viên -->
                                <p class="text-muted mb-1">
                                    <?php if ($group['privacy'] == 'public'): ?>
                                        <i class="bi bi-globe"></i> Nhóm công khai
                                    <?php else: ?>
                                        <i class="bi bi-lock"></i> Nhóm riêng tư
                                    <?php endif; ?>
                                    •  <a href="#" id="goto-members-tab" style="text-decoration: none;"><?= $memberCount ?> thành viên</a>
                                </p>

                                <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($group['description'])) ?></p>
                            </div>


                           <?php if ($group['created_by'] == $current_user_id): ?>
                            <?php
                            // Chỉ người tạo nhóm mới cần phần này
                            $pending_stmt = $conn->prepare("SELECT u.id, u.username, u.avatar FROM group_join_requests jr 
                                JOIN users u ON jr.user_id = u.id 
                                WHERE jr.group_id = ?");
                            $pending_stmt->bind_param("i", $group['id']);
                            $pending_stmt->execute();
                            $pending_requests = $pending_stmt->get_result();

                             ?>
                             <div>
                                 <?php if ($pending_requests->num_rows > 0): ?>
                                    <!-- Nút duyệt -->
                                    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalDuyetThanhVien">
                                        <i class="bi bi-person-check"></i> Duyệt thành viên (<?= $pending_requests->num_rows ?>)
                                    </button>
                                <?php endif; ?> 
                                <!-- Người tạo nhóm -->
                                <button class="btn btn-warning btn-sm btn-edit-group"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditGroup"
                                    data-id="<?= $group['id'] ?>"
                                    data-name="<?= htmlspecialchars($group['name']) ?>"
                                    data-description="<?= htmlspecialchars($group['description']) ?>"
                                    data-privacy="<?= $group['privacy'] ?>"
                                    data-cover="<?= htmlspecialchars($group['cover_image']) ?>">

                                    <i class="bi bi-pencil me-1"></i>Chỉnh sửa nhóm
                                </button>

                            </div>
                            <?php else: ?>
                                <?php if ($is_member): ?>
                                    <!-- Đã tham gia nhóm -->
                                    <form method="POST" action="roi_nhom.php" style="display: inline;">
                                        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-box-arrow-left me-1"></i>Rời nhóm
                                        </button>
                                    </form>
                                <?php elseif ($has_pending_request): ?> 
                                <button class="btn btn-secondary btn-sm" disabled>Đang chờ duyệt</button>
                                <?php else: ?>
                                    <!-- Chưa tham gia nhóm -->
                                    <form method="POST" action="thamgia_nhom.php" style="display: inline;">
                                        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-person-plus me-1"></i>Tham gia nhóm
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
            </div>

            <!-- Tabs điều hướng -->
            <ul class="nav nav-tabs mb-3 mt-2" id="groupTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link " id="intro-tab" data-bs-toggle="tab" data-bs-target="#intro" type="button" role="tab">Giới thiệu</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="discussion-tab" data-bs-toggle="tab" data-bs-target="#discussion" type="button" role="tab">Thảo luận</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">Thành viên</button>
            </li>
            </ul>

            <!-- Nội dung các tab -->
            <div class="tab-content" id="groupTabContent">
            <!-- Giới thiệu -->
            <div class="tab-pane fade" id="intro" role="tabpanel">
                <?php
                $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $stmt->bind_param("i", $group['created_by']);
                $stmt->execute();
                $userName = $stmt->get_result()->fetch_column();

                $stmt = $conn->prepare("SELECT COUNT(*) FROM group_members WHERE group_id = ?");
                $stmt->bind_param("i", $gid);
                $stmt->execute();
                $member = $stmt->get_result()->fetch_column();
                ?>

                <h5><?= htmlspecialchars($group['name']) ?></h5>
                <p><?= nl2br(htmlspecialchars($group['description'])) ?></p>
                <p><strong>Loại nhóm:</strong> <?= $group['privacy'] === 'public' ? 'Công khai' : 'Riêng tư' ?></p>
                <p><strong>Người tạo:</strong> <?= $userName ?></p>
                <p><strong>Số thành viên:</strong> <?= $member ?></p>
            </div>

            
            <!-- Thành viên -->
            <div class="tab-pane fade" id="members" role="tabpanel">
                <?php
               $allMembers = [];
                $stmt = $conn->prepare("SELECT u.id, u.username, u.avatar, gm.role FROM group_members gm 
                                        JOIN users u ON gm.user_id = u.id 
                                        WHERE gm.group_id = ?");
                $stmt->bind_param("i", $gid);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $allMembers[$row['role']][] = $row;
                }
                ?>
                <h5>Quản trị viên</h5>
                <ul class="list-group">
                <?php foreach ($allMembers['admin'] ?? [] as $admin): ?>
                       <li class="list-group-item d-flex align-items-center">
                            <a href="trangcanhan.php?user_id=<?= $admin['id'] ?>" style="text-decoration: none;">
                                <img src="<?= htmlspecialchars($admin['avatar'] ?? '/default-avatar.png') ?>" alt="avatar" class="me-2 rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                <strong><?= htmlspecialchars($admin['username']) ?></strong> <span class="badge bg-primary ms-2">Admin</span>
                            </a>
                        </li>
                <?php endforeach; ?>
                </ul>

                <h5>Thành viên</h5>
                <ul class="list-group">
                <?php foreach ($allMembers['member'] ?? [] as $member): ?>
                        <li class="list-group-item d-flex align-items-center">
                            <a href="trangcanhan.php?user_id=<?= $member['id'] ?>" style="text-decoration: none;">
                                <img src="<?= htmlspecialchars($member['avatar'] ?? '/default-avatar.png') ?>" alt="avatar" class="me-2 rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                <?= htmlspecialchars($member['username']) ?>
                            </a>
                        </li>
                <?php endforeach; ?>
                </ul>
            </div>

            <!-- Thảo luận -->
            <div class="tab-pane fade show active" id="discussion" role="tabpanel">
                    <div class="card-body">
                        <!-- Đăng bài -->
                         <?php if($group['created_by'] == $current_user_id || $is_member):?>
                        <div class="post-form-container">
                            <form action="submit_group_post.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="group_id" value="<?= $gid ?>">
                                <div class="post-form-body">
                                    <a href="trangcanhan.php?user_id=<?= $current_user_id ?>" style="cursor: url('/galaxy/cursor.cur'), auto;"><img src="<?php echo $currentUserAvatar; ?>" alt="Avatar" class="form-avatar"></a>
                                    <textarea name="content" class="form-control stylish-textarea" rows="3" placeholder="<?= t('congdong-1') ?> <?php echo htmlspecialchars($_SESSION['username']); ?>?"></textarea>
                                </div>
                                <div class="form-actions">
                                    <div class="d-flex justify-content-between">
                                        <label for="media-upload" class="file-upload-btn"><i class="fas fa-photo-video text-success"></i> <?= t('congdong-2') ?></label>
                                        <button type="submit" class="btn btn-primary w-50" style=" cursor:  url('/galaxy/cursor.cur'),  auto !important;"><?= t('congdong-3') ?></button>
                                    </div>
                                    <input type="file" id="media-upload" name="media[]" multiple accept="image/*,video/*" style="display: none;">
                                    <div id="preview-container" class="mt-3"></div>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                        <!-- Hiển thị bài -->
                        <?php
                        $isGroupPublic = ($group['privacy'] === 'public');
                        // Lấy bài viết
                        if($group['created_by'] == $current_user_id || $is_member || $isGroupPublic) {
                         $stmt_posts = $conn->prepare("SELECT p.*, u.username, u.avatar, u.is_verified
                                    FROM group_posts p JOIN users u ON p.user_id = u.id 
                                    WHERE p.group_id = ?
                                    ORDER BY p.created_at DESC");
                            $stmt_posts->bind_param("i", $gid);
                            $stmt_posts->execute();
                            $result_posts = $stmt_posts->get_result();

                            if ($result_posts && $result_posts->num_rows > 0) {
                                while($post = $result_posts->fetch_assoc()) {
                                    $post_id = $post['id'];
                                    echo '<div class="post">';
                                        echo '<div class="post-header">';
                                        echo '<a href="trangcanhan.php?user_id=' . $post['user_id'] . '" class="link-trangcanhan"><img src="' . 
                                            (!empty($post['avatar']) ? htmlspecialchars($post['avatar']) : '/galaxy/images-icon/default_avatar.png') . 
                                            '" alt="Avatar" class="avatar"></a>';
                                            echo '<div class="author-info">';
                                            echo '<div class="post-author mb-0">';
                                            echo '  <a href="trangcanhan.php?user_id='. $post['user_id'] .'" class="link-trangcanhan">';
                                            echo      htmlspecialchars($post['username']);
                                            if ($post['is_verified']) {
                                                echo ' <i class="fas fa-check-circle text-primary" title="Tài khoản đã xác minh"></i>';
                                            }
                                            echo '  </a>';
                                            echo '</div>';
                                            echo '<p class="post-time mb-0">'.date("H:i, d/m/Y", strtotime($post['created_at'])).'</p>';

                                            echo '</div>';
                                            if ($current_user_id == $post['user_id']) {
                                                echo '<div class="post-options-menu">';
                                                    echo '<a href="#" class="options-btn"><i class="fas fa-ellipsis-h"></i></a>';
                                                    echo '<div class="options-dropdown">';
                                                        echo "<a href='edit_post.php?id=" . $post['id'] . "&group=true'>" . htmlspecialchars(t('congdong-4')) . "</a>";
                                                        echo "<a href='delete_post.php?id=" . $post['id'] . "&group=true' onclick=\"return confirm('" . addslashes(t('congdong-6')) . "');\">" . htmlspecialchars(t('congdong-5')) . "</a>";
                                                    echo '</div>';
                                                echo '</div>';
                                            }
                                        echo '</div>';
                                        
                                        if (!empty($post['content'])) { echo '<div class="post-content">' . nl2br(htmlspecialchars($post['content'])) . '</div>'; }
                                        
                                        $stmt_media = $conn->prepare("SELECT file_path, media_type FROM post_group_media WHERE post_id = ?");
                                        $stmt_media->bind_param("i", $post_id);
                                        $stmt_media->execute();
                                        $result_media = $stmt_media->get_result();
                                        if ($result_media->num_rows > 0) {
                                            echo '<div class="post-media-grid">';
                                            while($media = $result_media->fetch_assoc()){
                                                if ($media['media_type'] == 'image') echo '<img src="' . htmlspecialchars($media['file_path']) . '" alt="Ảnh bài đăng">';
                                                elseif ($media['media_type'] == 'video') echo '<video src="' . htmlspecialchars($media['file_path']) . '" controls></video>';
                                            }
                                            echo '</div>';
                                        }
                                        $stmt_media->close();
                                        
                                        $stmt_reactions = $conn->prepare("SELECT user_id, reaction_type FROM group_reaction WHERE post_id = ?");
                                        $stmt_reactions->bind_param("i", $post_id);
                                        $stmt_reactions->execute();
                                        $result_reactions = $stmt_reactions->get_result();
                                        $reaction_counts = [];
                                        $total_reactions = 0;
                                        $user_reaction_type = null;
                                        while($reaction = $result_reactions->fetch_assoc()){
                                            @$reaction_counts[$reaction['reaction_type']]++;
                                            $total_reactions++;
                                            if ($reaction['user_id'] == $current_user_id) $user_reaction_type = $reaction['reaction_type'];
                                        }
                                        $stmt_reactions->close();

                                        echo '<div class="reaction-summary" id="reactions-count-'.$post_id.'">';
                                        if ($total_reactions > 0) {
                                            $icons_str = '';
                                            $reaction_map = ['love' => '❤️', 'like' => '👍', 'haha' => '😂', 'angry' => '😡'];
                                            foreach($reaction_map as $type => $icon) { if (isset($reaction_counts[$type])) $icons_str .= $icon; }
                                            echo trim($icons_str) . ' ' . $total_reactions;
                                        }
                                        echo '</div>';

                                        echo '<div class="post-actions">';
                                            echo '<div class="reaction-bar" id="reaction-bar-'.$post_id.'">';
                                                $reactions_config = ['like' => t('congdong-7'), 'love' =>t('congdong-8'), 'haha' => t('congdong-9'), 'angry' =>t('congdong-10')];
                                                foreach ($reactions_config as $type => $text) {
                                                    $active_class = ($user_reaction_type == $type) ? "active-reaction {$type}" : "";
                                                    echo '<a href="#" class="reaction-btn '.$active_class.'" data-post-id="'.$post_id.'" data-reaction="'.$type.'">'.$text.'</a>';
                                                }
                                            echo '</div>';

                                            $stmt_comment_count = $conn->prepare("SELECT COUNT(id) as comment_count FROM group_comments WHERE post_id = ?");
                                            $stmt_comment_count->bind_param("i", $post_id);
                                            $stmt_comment_count->execute();
                                            $comment_count = $stmt_comment_count->get_result()->fetch_assoc()['comment_count'];
                                            $stmt_comment_count->close();
                                            
                                            echo '<div class="text-center mt-2 border-top border-secondary pt-2">';
                                            echo "<a href='post_group_detail.php?id=" . $post_id . "' class='btn btn-secondary btn-sm w-100'>" . htmlspecialchars(t('congdong-11')) . " (" . $comment_count . ")</a>";
                                            echo '</div>';

                                        echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-center">Chưa có bài đăng nào.</p>';
                            }
                        }
                        ?>
                    </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="carousel-container">
                    <div class="carousel-slides">
                        <img src="./images-icon/vutru5.jpg" alt="Nhóm 1" class="carousel-img">
                        <img src="./images-icon/vutru8.jpg" alt="Nhóm 2" class="carousel-img">
                        <img src="./images-icon/vutru9.jpg" alt="Nhóm 3" class="carousel-img">
                        <img src="./images-icon/vutru10.jpg" alt="Nhóm 4" class="carousel-img">
                    </div>
                    <div class="carousel-text">
                        <h2>Chào mừng bạn, hãy chọn một nhóm để khám phá!</h2>
                    </div>
                </div>
            <?php endif; ?>
                </div>
            </div>
        </div>    
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/galaxy/js/nhom.js"></script>
<script src="https://cdn.socket.io/4.7.1/socket.io.min.js"></script>

    <script>
        const storedNotifications = <?php echo json_encode($notifications); ?>;
        let notificationCount = <?php echo $unreadCount; ?>;
        let notifications = storedNotifications;
        const user_id = "<?php echo $_SESSION['user_id']; ?>";
    </script>
    <script src="/galaxy/js/noti.js"></script>
</body>
<!-- Modal tạo nhóm -->
<div class="modal fade" id="modalTaoNhom" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="tao_nhom.php" method="POST" enctype="multipart/form-data">
  <div class="modal-header">
    <h5 class="modal-title">Tạo nhóm mới</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <input type="text" name="name" class="form-control mb-2" placeholder="Tên nhóm" required>
    <textarea name="description" class="form-control mb-2" placeholder="Mô tả nhóm"></textarea>
    <select name="privacy" class="form-select mb-2" required>
      <option value="public">Nhóm công khai</option>
      <option value="private">Nhóm riêng tư</option>
    </select>
    <input type="file" name="cover_image" class="form-control" accept="image/*">
  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">Tạo nhóm</button>
  </div>
</form>

  </div>
</div>
<!-- Modal sửa nhóm -->
<div class="modal fade" id="modalEditGroup" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="sua_nhom.php" method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Chỉnh sửa nhóm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Ẩn ID nhóm -->
        <input type="hidden" name="group_id" id="edit-group-id">

        <label>Tên nhóm</label>
        <input type="text" name="name" id="edit-group-name" class="form-control mb-2" required>

        <label>Mô tả nhóm</label>
        <textarea name="description" id="edit-group-description" class="form-control mb-2"></textarea>

        <select name="privacy" id="edit-group-privacy" class="form-select mb-2" required>
        <option value="public">Nhóm công khai</option>
        <option value="private">Nhóm riêng tư</option>
        </select>

        <label>Ảnh bìa mới (tùy chọn)</label>
        <input type="file" name="cover_image" class="form-control" accept="image/*">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>
<!-- modal duyet thanh vien -->
<div class="modal fade" id="modalDuyetThanhVien" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="duyet_thanhvien.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Duyệt thành viên</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php while ($req = $pending_requests->fetch_assoc()): ?>
          <div class="form-check d-flex align-items-center gap-2">
            <input class="form-check-input" type="checkbox" name="approve_users[]" value="<?= $req['id'] ?>" checked>
            <div>
            <a href="trangcanhan.php?user_id=<?= $req['id'] ?>" style="text-decoration: none;">
                <img src="<?= htmlspecialchars($req['avatar'] ?? '/default-avatar.png') ?>" alt="avatar" class="me-2 rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                <label class="form-check-label"><?= htmlspecialchars($req['username']) ?></label>
            </a>
            </div>
          </div>
        <?php endwhile; ?>
        <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Duyệt các thành viên</button>
      </div>
    </form>
  </div>
</div>


</html>
