<?php
session_start();
$loggedIn = isset($_SESSION['username']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/lang.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/load_noti.php';
require_once 'db.php';

$title_col   = ($current_lang == 'en') ? 'title_en'   : 'title_vi';
$content_col = ($current_lang == 'en') ? 'content_en' : 'content_vi';

?>
<?php
// sử lí search 
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$date    = $_GET['date'] ?? '';
$lat_search     = $_GET['lat_search'] ?? '';
$lng_search     = $_GET['lng_search'] ?? '';

$hasFilter = !empty($search_term) || !empty($date) || (!empty($lat_search) && !empty($lng_search));

if ($hasFilter) {
$sql = "SELECT id, title_vi, title_en, content_vi, content_en, image_url, latitude, longitude, place_name, event_date, care, created_at";

// Nếu có lat/lng thì thêm cột distance
if ($lat_search && $lng_search) {
    $sql .= ", (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
                 * cos(radians(longitude) - radians(?)) 
                 + sin(radians(?)) * sin(radians(latitude)))) AS distance";
}

$sql .= " FROM events WHERE 1=1";
$params = [];
$types  = "";

// Lọc theo từ khóa
if (!empty($search_term)) {
    $sql .= " AND (title_vi LIKE ? OR title_en LIKE ? OR content_vi LIKE ? OR content_en LIKE ?)";
    $like = "%$search_term%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "ssss";
}

// Lọc theo ngày
if (!empty($date)) {
    $sql .= " AND DATE(event_date) = ?";
    $params[] = $date;
    $types .= "s";
}

// Lọc theo vị trí trong bán kính 100km
if ($lat_search && $lng_search) {
    $sql .= " HAVING distance <= 100";
    $params = array_merge([$lat_search, $lng_search, $lat_search], $params);
    $types  = "ddd" . $types;
}

// Sắp xếp mới nhất
$sql .= " ORDER BY event_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$search_events = $stmt->get_result();

} else {
// lấy vị trí user hiện tại 

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

$nearby_events = [];
$today_events = [];

// Nếu có vị trí user
if ($lat && $lng) {
  $stmt = $conn->prepare("
      SELECT id, $title_col AS title, $content_col AS content, image_url, latitude, longitude, place_name, event_date, care,
      (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
      * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
      FROM events
      HAVING distance < 100
      ORDER BY event_date ASC
      LIMIT 5
  ");
    $stmt->bind_param("ddd", $lat, $lng, $lat);
    $stmt->execute();
    $nearby_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Sự kiện hôm nay
$stmt = $conn->prepare("
    SELECT id, $title_col AS title, $content_col AS content, image_url, latitude, longitude, place_name, event_date, care
    FROM events
    WHERE DATE(event_date) = CURDATE()
    ORDER BY event_date ASC
");
$stmt->execute();
$today_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// sự kiện nổi bật
$highlight_sql = "
    SELECT * FROM events
    ORDER BY 
        CASE 
            WHEN event_date > CURDATE() THEN 1   -- Sắp diễn ra
            WHEN DATE(event_date) = CURDATE() THEN 2  -- Đang diễn ra
            ELSE 3   -- Đã diễn ra
        END,
        care DESC,         -- Ưu tiên số lượt quan tâm
        event_date ASC     -- Nếu bằng care thì so ngày
    LIMIT 5
";
$highlight_new = $conn->query($highlight_sql)->fetch_all(MYSQLI_ASSOC);

// Hàm lấy sự kiện theo trạng thái
function getEventsByStatus($conn, $status) {
    $today = date("Y-m-d");

    if ($status == "sap") {
        $where = "event_date > '$today'";
    } elseif ($status == "dang") {
        $where = "event_date = '$today'";
    } else {
        $where = "event_date < '$today'";
    }

    $sql = "SELECT * FROM events WHERE $where ORDER BY event_date ASC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$events_sap  = getEventsByStatus($conn, "sap");
$events_dang = getEventsByStatus($conn, "dang");
$events_da   = getEventsByStatus($conn, "da");
}
?>
<?php
function getEventStatus($event_date) {
    $today = date("Y-m-d");
    $d = date("Y-m-d", strtotime($event_date));

    if ($d > $today) return "sap";
    elseif ($d == $today) return "dang";
    else return "da";
}
$user_id = $_SESSION['user_id'] ?? null;

function hasCared($conn, $user_id, $event_id) {
    if (!$user_id) return false;
    $stmt = $conn->prepare("SELECT 1 FROM event_cares WHERE user_id=? AND event_id=?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sự Kiện Thiên Văn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <link rel="stylesheet" href="/galaxy/css/sukien.css">
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/galaxy/css/noti.css">
  <link rel="stylesheet" href="/galaxy/css/header.css">
  <style>
    .event-card {
    background: #041b34ff; /* nền xanh */
    color: #fff;         /* chữ trắng toàn card */
}

.event-card .event-date,
.event-card .event-place {
    color: #ffd700; /* vàng nhạt để nổi bật */
}

  </style>
</head>
<body class=" text-white">
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
                        <a href="#" class="active"><img src="/galaxy/images-icon/black-hole.png" alt=""><?= t('3') ?></a>
                        <div class="dropdown-content">
                            <a class="item" href="vutru.php"><img src="/galaxy/images-icon/vutru.png" alt=""><?= t('3,3') ?> </a>
                            <a class="item" href="sukien.php"><img src="/galaxy/images-icon/sukien.png" alt=""><?= t('3,1') ?> </a>
                            <a class="item" href="tintuc.php"><img src="/galaxy/images-icon/news.png" alt=""><?= t('3,2') ?> </a>
                        </div>
                    </li>
                    <li><a href="congdong.php"><img src="/galaxy/images-icon/group (1).png" alt=""><?= t('4') ?></a></li>
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

<div class="container my-5 pt-5">

  <h1 class="mb-4">Sự Kiện Thiên Văn</h1>

  <!-- SEARCH FORM -->
  <form action="sukien.php" method="GET" class="mb-5">
  <div class="row g-3">

    <!-- Tìm theo từ khóa -->
    <div class="col-md-4">
      <input type="text" name="search" class="form-control form-control-lg" 
             placeholder="<?= t('sukien-1') ?>" value="<?= htmlspecialchars($search_term ?? '') ?>">
    </div>

    <!-- Tìm theo ngày -->
    <div class="col-md-3">
      <input type="date" name="date" class="form-control form-control-lg" 
             value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
    </div>

    <!-- Vị trí (ẩn input, map button để chọn vị trí) -->
  <div class="col-md-3">
    <input type="hidden" name="lat_search" id="lat_search" value="<?= htmlspecialchars($_GET['lat_search'] ?? '') ?>">
    <input type="hidden" name="lng_search" id="lng_search" value="<?= htmlspecialchars($_GET['lng_search'] ?? '') ?>">
    <input type="hidden" name="place_name_search" id="place_name_search" value="<?= htmlspecialchars($_GET['place_name_search'] ?? '') ?>">

    <button type="button" class="btn btn-outline-secondary btn-lg w-100" 
            id="btn-location"
            onclick="openMapPicker()">
      🌍 <?= !empty($_GET['place_name_search']) ? htmlspecialchars($_GET['place_name_search']) : "Chọn vị trí" ?>
    </button>
  </div>


    <!-- Nút search -->
    <div class="col-md-2">
      <button class="btn btn-primary btn-lg w-100" type="submit">
        <?= t('sukien-2') ?>
      </button>
    </div>
  </div>
</form>
<!-- kết quả lọc -->

<?php if ($hasFilter): ?>
  <div class="container my-4">
    <h3 class="mb-3">
      🔎 Kết quả lọc
      <?php 
      $conditions = [];
      if ($search_term) $conditions[] = "từ khóa <b>\"".htmlspecialchars($search_term)."\"</b>";
      if ($date) $conditions[] = "ngày <b>".date("d/m/Y", strtotime($date))."</b>";
      if ($lat_search && $lng_search) {
          $placeName = $_GET['place_name_search'] ?? "một vị trí";
          $conditions[] = "trong bán kính 100km quanh <b>".htmlspecialchars($placeName)."</b>";
      }
      echo " (" . implode(", ", $conditions) . ")";
    ?>
    </h3>

    <?php if ($search_events->num_rows > 0): ?>
      <div class="row g-4">
        <?php while ($ev = $search_events->fetch_assoc()):  
          $status = getEventStatus($ev['event_date']);
          $badgeClass = ($status=="sap") ? "bg-success" : (($status=="dang") ? "bg-warning text-dark" : "bg-danger");
          $badgeText  = ($status=="sap") ? "Sắp diễn ra" : (($status=="dang") ? "Đang diễn ra" : "Đã diễn ra");
          $alreadyCare = $user_id ? hasCared($conn, $user_id, $ev['id']) : false;
        ?>
          <div class="col-md-4 col-lg-3">
            <div class="card event-card shadow-sm h-100">
              
              <!-- Ảnh + badge -->
              <div class="position-relative">
                <img src="<?= htmlspecialchars($ev['image_url']) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($ev['title_vi']) ?>" 
                     style="height:180px;object-fit:cover;">
                <span class="badge <?= $badgeClass ?> position-absolute top-0 start-0 m-3 fs-6 px-3 py-2 shadow">
                  <?= $badgeText ?>
                </span>
              </div>

              <!-- Nội dung -->
              <div class="card-body d-flex flex-column" style="overflow: hidden;">
                <h5 class="card-title"><?= htmlspecialchars($ev['title_vi']) ?></h5>
                <small class="card-location">📍 <?= htmlspecialchars($ev['place_name']) ?></small>
                <small>📅 <?= date("d/m/Y", strtotime($ev['event_date'])) ?></small>

                <!-- footer luôn dính đáy -->
                <div class="d-flex gap-2 align-items-center mt-auto">
                  <?php if ($status == "sap"): ?>
                    <?php if ($alreadyCare): ?>
                      <button class="btn btn-sm btn-secondary" disabled>Đã quan tâm</button>
                    <?php else: ?>
                      <button class="btn btn-sm btn-primary btn-quan-tam" data-id="<?= $ev['id'] ?>">Quan tâm</button>
                    <?php endif; ?>
                  <?php endif; ?>

                  <span class="badge bg-info text-dark care-count" data-id="<?= $ev['id'] ?>">
                    ❤️ <?= (int)$ev['care'] ?>
                  </span>

                  <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-light ms-auto">Chi tiết</a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning">Không tìm thấy sự kiện phù hợp.</div>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="row mb-5">
  <!-- Bên trái: Sự kiện gần tôi -->
  <div class="col-md-7">
  <h3 class="mb-3">🔭 Sự kiện gần bạn</h3>

  <?php if (!empty($nearby_events)): ?>
  <div id="nearbyCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-inner">

      <?php foreach ($nearby_events as $index => $ev): 
          $status = getEventStatus($ev['event_date']);
          $badgeClass = ($status=="sap") ? "bg-success" : (($status=="dang") ? "bg-warning text-dark" : "bg-danger");
          $badgeText  = ($status=="sap") ? "Sắp diễn ra" : (($status=="dang") ? "Đang diễn ra" : "Đã diễn ra");
          $alreadyCare = $user_id ? hasCared($conn, $user_id, $ev['id']) : false;
      ?>
      <div class="carousel-item <?= $index==0 ? 'active' : '' ?>">
        <div class="card text-white border-0" style="height: 475px;">
          <div class="position-relative">
            <!-- ảnh -->
            <img src="<?= htmlspecialchars($ev['image_url']) ?>" 
                 class="d-block w-100" 
                 style="height: 475px; object-fit:cover;">

            <!-- badge nổi bật -->
            <span class="badge <?= $badgeClass ?> position-absolute top-0 start-0 m-3 fs-6 px-3 py-2 shadow">
              <?= $badgeText ?>
            </span>

            <!-- overlay chữ -->
            <div class="carousel-caption text-start bg-dark bg-opacity-50 p-3 rounded-3">
              <h5 class="card-title"><?= htmlspecialchars($ev['title']) ?></h5>
              <small class="card-location">📍 <?= htmlspecialchars($ev['place_name']) ?></small>
              <small>📅 <?= date("d/m/Y", strtotime($ev['event_date'])) ?></small>
              <div class="d-flex gap-2 align-items-center">
                <?php if ($status == "sap"): ?>
                  <?php if ($alreadyCare): ?>
                    <button class="btn btn-sm btn-secondary" disabled>Đã quan tâm</button>
                  <?php else: ?>
                    <button class="btn btn-sm btn-primary btn-quan-tam" data-id="<?= $ev['id'] ?>">
                      Quan tâm
                    </button>
                  <?php endif; ?>
                <?php endif; ?>

                <span class="badge bg-info text-dark care-count" data-id="<?= $ev['id'] ?>">
                    ❤️ <?= (int)$ev['care'] ?>
                  </span>

                <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-light ms-auto">Chi tiết</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

    </div>

    <!-- Nút điều hướng -->
    <button class="carousel-control-prev" type="button" data-bs-target="#nearbyCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#nearbyCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
  <?php else: ?>
    <p class="text-muted">Không có sự kiện nào gần bạn.</p>
  <?php endif; ?>
</div>



  <!-- Bên phải: Sự kiện hôm nay -->
  <div class="col-md-5">
  <h3 class="mb-3">📅 Sự kiện hôm nay</h3>
  <div class="today-events-wrapper" style="max-height: 475px; overflow-y: auto; overflow-x: hidden; ">
  <?php if (!empty($today_events)): ?>
    <div class="row g-3">
      <?php foreach ($today_events as $ev): ?>
        <div class="col-12">
          <div class="card event-card border-0 shadow-sm overflow-hidden">
            <div class="row g-0 h-100">
              <!-- Ảnh sự kiện -->
              <div class="col-4">
                <img src="<?= htmlspecialchars($ev['image_url']) ?>" 
                    class="img-fluid h-100 w-100" 
                    style="object-fit:cover;" 
                    alt="<?= htmlspecialchars($ev['title']) ?>">
              </div>

              <!-- Nội dung -->
              <div class="col-8">
                <div class="card-body d-flex flex-column h-100">
                  <div>
                     <h5 class="card-title"><?= htmlspecialchars($ev['title']) ?></h5>
                    <small class="text-white card-location">📍 <?= htmlspecialchars($ev['place_name']) ?></small>
                    <small class="text-white">📅 <?= date("d/m/Y", strtotime($ev['event_date'])) ?></small>
                  </div>


                  <!-- Footer -->
                  <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="badge bg-info text-dark care-count" data-id="<?= $ev['id'] ?>">
                      ❤️ <?= (int)$ev['care'] ?>
                    </span>
                    <a href="view_event.php?id=<?= $ev['id'] ?>" 
                      class="btn btn-sm btn-light ms-auto">
                      Chi tiết
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-muted">Hôm nay không có sự kiện nào.</p>
  <?php endif; ?>
  </div>
</div>

</div>
<div class="container my-4">
    <h3 class="mb-3">🌟 Sự kiện nổi bật</h3>
    <div class="d-flex flex-nowrap overflow-auto pb-3" style="gap:16px;">
        <?php foreach ($highlight_new as $ev):  
            $status = getEventStatus($ev['event_date']);
            $badgeClass = ($status=="sap") ? "bg-success" : (($status=="dang") ? "bg-warning text-dark" : "bg-danger");
            $badgeText  = ($status=="sap") ? "Sắp diễn ra" : (($status=="dang") ? "Đang diễn ra" : "Đã diễn ra");
            $alreadyCare = $user_id ? hasCared($conn, $user_id, $ev['id']) : false;
        ?>
            <div class="card event-card shadow-sm" style="min-width:300px;max-width:300px;flex:0 0 auto;height:350px;">
  
              <!-- Ảnh + badge -->
              <div class="position-relative">
                <img src="<?= htmlspecialchars($ev['image_url']) ?>" 
                    class="card-img-top" 
                    alt="<?= htmlspecialchars($ev['title_vi']) ?>" 
                    style="height:180px;object-fit:cover;">
                <span class="badge <?= $badgeClass ?> position-absolute top-0 start-0 m-3 fs-6 px-3 py-2 shadow">
                  <?= $badgeText ?>
                </span>
              </div>

              <!-- Nội dung -->
              <div class="card-body d-flex flex-column"  style="overflow: hidden;">
                <h5 class="card-title"><?= htmlspecialchars($ev['title_vi']) ?></h5>
                <small class="card-location">📍 <?= htmlspecialchars($ev['place_name']) ?></small>
                <small>📅 <?= date("d/m/Y", strtotime($ev['event_date'])) ?></small>

                <!-- footer luôn dính đáy -->
                <div class="d-flex gap-2 align-items-center" style="margin-top:auto;">
                  <?php if ($status == "sap"): ?>
                    <?php if ($alreadyCare): ?>
                      <button class="btn btn-sm btn-secondary" disabled>Đã quan tâm</button>
                    <?php else: ?>
                      <button class="btn btn-sm btn-primary btn-quan-tam" data-id="<?= $ev['id'] ?>">Quan tâm</button>
                    <?php endif; ?>
                  <?php endif; ?>

                  <span class="badge bg-info text-dark care-count" data-id="<?= $ev['id'] ?>">
                    ❤️ <?= (int)$ev['care'] ?>
                  </span>

                  <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-light ms-auto">Chi tiết</a>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="container my-5">
  <h3 class="mb-3">🏷️ Danh mục sự kiện</h3>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="eventTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabSap">Sắp diễn ra</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDang">Đang diễn ra</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDa">Đã diễn ra</button></li>
  </ul>

  <div class="tab-content mt-3">
    <!-- Tab Sắp diễn ra -->
<div class="tab-pane fade show active" id="tabSap" style="max-height: 68vh;  overflow-x: hidden;  overflow-y: auto;">
  <div class="row">
    <?php if (!empty($events_sap)): ?>
      <?php foreach ($events_sap as $ev): ?>
        <?php $alreadyCare = $user_id ? hasCared($conn, $user_id, $ev['id']) : false; ?>
        <div class="col-12 mb-3">
          <div class="card event-card border-0 shadow-sm overflow-hidden">
            <div class="row g-0 h-100">
              <div class="col-2">
                <img src="<?= htmlspecialchars($ev['image_url']) ?>" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="">
              </div>
              <div class="col-10">
                <div class="card-body d-flex flex-column h-100">
                  <h5 class="card-title"><?= htmlspecialchars($ev['title_vi']) ?></h5>
                <small class="card-location">📍 <?= htmlspecialchars($ev['place_name']) ?></small>
                <small>📅 <?= date("d/m/Y", strtotime($ev['event_date'])) ?></small>
                  <div class="mt-auto d-flex gap-2 align-items-center pt-3"> 
                    <?php if ($user_id): ?>
                      <?php if ($alreadyCare): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Đã quan tâm</button>
                      <?php else: ?>
                        <button class="btn btn-sm btn-primary btn-quan-tam" data-id="<?= $ev['id'] ?>">Quan tâm</button>
                      <?php endif; ?>
                    <?php endif; ?>
                    <span class="badge bg-info text-dark care-count" data-id="<?= $ev['id'] ?>">
                        ❤️ <?= (int)$ev['care'] ?>
                      </span>
                    <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-light ms-auto">Chi tiết</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">Chưa có sự kiện nào.</p>
    <?php endif; ?>
  </div>
</div>


    <!-- Tab Đang diễn ra -->
    <div class="tab-pane fade" id="tabDang" style="max-height: 60vh;  overflow-x: hidden;  overflow-y: auto;">
      <div class="row">
        <?php if (!empty($events_dang)): ?>
          <?php foreach ($events_dang as $ev): ?>
            <div class="col-12 mb-3">
              <div class="card event-card border-0 shadow-sm overflow-hidden">
                <div class="row g-0 h-100">
                  <div class="col-2">
                    <img src="<?= htmlspecialchars($ev['image_url']) ?>" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="">
                  </div>
                  <div class="col-10">
                    <div class="card-body d-flex flex-column h-100">
                      <h5 class="card-title"><?= htmlspecialchars($ev['title_vi']) ?></h5>
                    <small class="card-location">📍 <?= htmlspecialchars($ev['place_name']) ?></small>
                    <small>📅 <?= date("d/m/Y", strtotime($ev['event_date'])) ?></small>
                      <div class="mt-auto d-flex gap-2 align-items-center pt-3"> 
                        <span class="badge bg-info text-dark care-count" data-id="<?= $ev['id'] ?>">
                            ❤️ <?= (int)$ev['care'] ?>
                          </span>
                        <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-light ms-auto">Chi tiết</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted">Chưa có sự kiện nào.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Tab Đã diễn ra -->
    <div class="tab-pane fade" id="tabDa" style="max-height: 60vh;  overflow-x: hidden;  overflow-y: auto;">
      <div class="row">
        <?php if (!empty($events_da)): ?>
          <?php foreach ($events_da as $ev): ?>
            <div class="col-12 mb-3">
              <div class="card event-card border-0 shadow-sm overflow-hidden">
                <div class="row g-0 h-100">
                  <div class="col-2">
                    <img src="<?= htmlspecialchars($ev['image_url']) ?>" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="">
                  </div>
                  <div class="col-10">
                    <div class="card-body d-flex flex-column h-100">
                      <h5 class="card-title"><?= htmlspecialchars($ev['title_vi']) ?></h5>
                    <small class="card-location">📍 <?= htmlspecialchars($ev['place_name']) ?></small>
                    <small>📅 <?= date("d/m/Y", strtotime($ev['event_date'])) ?></small>
                      <div class="mt-auto d-flex gap-2 align-items-center pt-3"> 
                        <span class="badge bg-info text-dark care-count" data-id="<?= $ev['id'] ?>">
                            ❤️ <?= (int)$ev['care'] ?>
                          </span>
                        <a href="view_event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-light ms-auto">Chi tiết</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted">Chưa có sự kiện nào.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal map -->
<div class="modal fade" id="mapModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div id="map" style="height:500px"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="saveLocation()">Chọn vị trí</button>
      </div>
    </div>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Chỉ chạy khi chưa có lat/lng trong URL và cũng không phải đang search có giá trị
const urlParams = new URLSearchParams(window.location.search);
const hasLat = urlParams.has("lat");

// Lấy giá trị thật sự của search params
const searchVal     = (urlParams.get("search") || "").trim();
const dateVal       = (urlParams.get("date") || "").trim();
const latSearchVal  = (urlParams.get("lat_search") || "").trim();

// Kiểm tra có search hợp lệ (có giá trị thực sự)
const hasSearch = searchVal !== "" || dateVal !== "" || latSearchVal !== "";

if (!hasLat && !hasSearch) {
  navigator.geolocation.getCurrentPosition(function(position) {
      let lat = position.coords.latitude;
      let lng = position.coords.longitude;
      // Gửi lên server qua query string, giữ lại các tham số khác nếu có
      urlParams.set("lat", lat);
      urlParams.set("lng", lng);
      window.location.search = urlParams.toString();
  });
}
</script>

<script>
document.querySelectorAll('.btn-quan-tam').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    let eventId = this.dataset.id;

    fetch("quan_tam.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + encodeURIComponent(eventId)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Cập nhật tất cả nút cùng event
        let buttons = document.querySelectorAll('.btn-quan-tam[data-id="' + eventId + '"]');
        buttons.forEach(b => {
          b.classList.remove("btn-primary");
          b.classList.add("btn-secondary");
          b.textContent = "Đã quan tâm";
          b.disabled = true;
        });

        // Cập nhật tất cả số quan tâm
        let countSpans = document.querySelectorAll('.care-count[data-id="' + eventId + '"]');
        countSpans.forEach(span => {
          span.textContent = "❤️" + data.count ;
        });

      } else {
        alert("Có lỗi xảy ra: " + data.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert("Có lỗi xảy ra khi gửi yêu cầu.");
    });
  });
});

// sử lí chọn vị trí bản đồ khi search 
let map, marker;
function openMapPicker() {
  var modalEl = document.getElementById('mapModal');
  var modal = new bootstrap.Modal(modalEl);
  modal.show();

  modalEl.addEventListener('shown.bs.modal', function () {
    if (!map) {
      map = L.map('map').setView([16.0471, 108.2068], 12); // Đà Nẵng

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
      }).addTo(map);

      map.on('click', function(e) {
        if (marker) marker.remove();
        marker = L.marker(e.latlng).addTo(map);

        let lat = e.latlng.lat;
        let lng = e.latlng.lng;
        document.getElementById("lat_search").value = lat;
        document.getElementById("lng_search").value = lng;

        // Gọi API để lấy tên địa điểm
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
          .then(res => res.json())
          .then(data => {
            let placeName = data.display_name || "Vị trí đã chọn";
            document.getElementById("place_name_search").value = placeName;
            document.getElementById("btn-location").innerText = "🌍 " + placeName;
          });
      });
    } else {
      map.invalidateSize();
    }
  });
}

function saveLocation() {
  bootstrap.Modal.getInstance(document.getElementById('mapModal')).hide();
}

</script>
<script src="https://cdn.socket.io/4.7.1/socket.io.min.js"></script>

    <script>
        const storedNotifications = <?php echo json_encode($notifications); ?>;
        let notificationCount = <?php echo $unreadCount; ?>;
        let notifications = storedNotifications;
        const user_id = "<?php echo $_SESSION['user_id']; ?>";
    </script>
    <script src="/galaxy/js/noti.js"></script>
</body>
</html>
