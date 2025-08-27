<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/galaxy/lang.php';
require_once 'db.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id === 0) {
    die("Sự kiện không hợp lệ.");
}

// Xác định các cột ngôn ngữ
$title_col = ($current_lang == 'en') ? 'title_en' : 'title_vi';
$content_col = ($current_lang == 'en') ? 'content_en' : 'content_vi';

// Lấy thông tin sự kiện chính
$stmt = $conn->prepare(
    "SELECT id, {$title_col} AS title, {$content_col} AS content, image_url, event_date, place_name, latitude, longitude 
     FROM events WHERE id = ?"
);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Không tìm thấy sự kiện.");
}
$event = $result->fetch_assoc();
$stmt->close();

// Lấy 3 sự kiện NỔI BẬT dựa trên lượt 'care' cao nhất
$featured_events = [];
$stmt_featured = $conn->prepare(
    "SELECT id, {$title_col} AS title, image_url, event_date, care 
     FROM events 
     WHERE id != ? 
     ORDER BY care DESC 
     LIMIT 3"
);
$stmt_featured->bind_param("i", $event_id);
$stmt_featured->execute();
$result_featured = $stmt_featured->get_result();
while ($row = $result_featured->fetch_assoc()) {
    $featured_events[] = $row;
}
$stmt_featured->close();

// Lấy 4 sự kiện sắp diễn ra khác để đề xuất
$suggested_events = [];
$stmt_suggest = $conn->prepare(
    "SELECT id, {$title_col} AS title, image_url, event_date 
     FROM events 
     WHERE id != ? AND event_date > CURDATE() 
     ORDER BY event_date ASC 
     LIMIT 4"
);
$stmt_suggest->bind_param("i", $event_id);
$stmt_suggest->execute();
$result_suggest = $stmt_suggest->get_result();
while ($row = $result_suggest->fetch_assoc()) {
    $suggested_events[] = $row;
}
$stmt_suggest->close();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="bot_cosmos/static/style.css">
    <style>
        body { background-color: #0a0f1f; color: #e0e0e0; font-family: 'Lato', sans-serif; cursor: url('/galaxy/cursor.cur'), auto !important; }
        .article-header { border-bottom: 1px solid #4a5568; }
        .article-title { font-family: 'Merriweather', serif; font-weight: 700; color: #fff; }
        .featured-image { width: 100%; height: auto; max-height: 500px; object-fit: cover; margin-bottom: 30px; border-radius: 0.5rem; }
        .article-content { font-size: 1.1rem; line-height: 1.9; color: #cbd5e1; }
        .article-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 25px 0; }
        .article-content h2, .article-content h3 { font-family: 'Merriweather', serif; color: #fbbf24; margin-top: 2rem; }
        .back-link { color: #f59e0b; text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 2rem; }
        .back-link:hover { color: #fef3c7; }
        .event-meta { display: flex; flex-wrap: wrap; gap: 20px; font-size: 1rem; margin-bottom: 20px; color: #fbbf24; }
        .event-meta span { display: flex; align-items: center; }
        .event-meta i { margin-right: 8px; font-size: 1.2rem; }
        #map { height: 400px; border-radius: 0.5rem; margin-top: 2rem; }
        .sidebar-title { font-family: 'Merriweather', serif; color: #f59e0b; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; margin-bottom: 20px; }
        .sidebar-event-card { position: relative; display: flex; align-items: center; background-color: #1a202c; border-radius: 0.5rem; margin-bottom: 15px; overflow: hidden; text-decoration: none; color: #e0e0e0; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .sidebar-event-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.4); text-decoration: none; color: #fff; }
        .sidebar-event-img { width: 100px; height: 80px; object-fit: cover; }
        .sidebar-event-info { padding: 10px 15px; }
        .sidebar-event-info h5 { margin-bottom: 5px; font-size: 1rem; font-weight: bold; }
        .sidebar-event-info p { margin: 0; font-size: 0.85rem; color: #a0aec0; }
        .sidebar-event-info p i { color: #f6ad55; margin-right: 4px; } /* Thêm margin phải cho icon */
    </style>
</head>
<body>
    <?php // Include header chung ?>
    <main class="container my-5">
        <a href="sukien.php" class="back-link">&laquo; <?= t('sukien-7') ?></a>
        <div class="row">
            <div class="col-lg-8">
                <article>
                    <header class="article-header mb-4 pb-3">
                        <h1 class="article-title display-4"><?= htmlspecialchars($event['title']) ?></h1>
                        <div class="event-meta">
                            <span><i class="fas fa-calendar-alt"></i><?= date("d/m/Y", strtotime($event['event_date'])) ?></span>
                            <?php if (!empty($event['place_name'])): ?>
                                <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($event['place_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </header>
                    <?php if (!empty($event['image_url'])): ?>
                        <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="featured-image">
                    <?php endif; ?>
                    <div class="article-content mt-4"><?= $event['content']; ?></div>
                    <?php if (!empty($event['latitude']) && !empty($event['longitude'])): ?>
                        <h3 class="mt-5"><?= t('vi_tri_su_kien') ?></h3>
                        <div id="map"></div>
                    <?php endif; ?>
                </article>
            </div>

            <div class="col-lg-4">
                <aside class="sidebar mt-5 mt-lg-0">
                    <?php if (!empty($featured_events)): ?>
                        <div class="mb-5">
                            <h4 class="sidebar-title"><?= t('su_kien_noi_bat') ?></h4>
                            <?php foreach ($featured_events as $feat_event): ?>
                                <a href="view_event.php?id=<?= $feat_event['id'] ?>" class="sidebar-event-card">
                                    <img src="<?= htmlspecialchars($feat_event['image_url']) ?>" alt="<?= htmlspecialchars($feat_event['title']) ?>" class="sidebar-event-img">
                                    <div class="sidebar-event-info">
                                        <h5><?= htmlspecialchars($feat_event['title']) ?></h5>
                                        <p>
                                            <i class="fas fa-heart"></i> <?= number_format($feat_event['care']) ?>
                                            &nbsp;&bull;&nbsp;
                                            <i class="fas fa-calendar-day"></i> <?= date("d/m/Y", strtotime($feat_event['event_date'])) ?>
                                        </p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h4 class="sidebar-title"><?= t('su_kien_sap_dien_ra') ?></h4>
                    <?php if (empty($suggested_events)): ?>
                        <p><?= t('khong_co_su_kien_khac') ?></p>
                    <?php else: ?>
                        <?php foreach ($suggested_events as $suggest_event): ?>
                            <a href="view_event.php?id=<?= $suggest_event['id'] ?>" class="sidebar-event-card">
                                <img src="<?= htmlspecialchars($suggest_event['image_url']) ?>" alt="<?= htmlspecialchars($suggest_event['title']) ?>" class="sidebar-event-img">
                                <div class="sidebar-event-info">
                                    <h5><?= htmlspecialchars($suggest_event['title']) ?></h5>
                                    <p><i class="fas fa-calendar-day"></i> <?= date("d/m/Y", strtotime($suggest_event['event_date'])) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
        <?php include 'bot_cosmos/templates/chat_window.html'; ?>
    </main>
    <?php // Include footer chung ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php if (!empty($event['latitude']) && !empty($event['longitude'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = <?= json_encode($event['latitude']) ?>;
            const lng = <?= json_encode($event['longitude']) ?>;
            const placeName = <?= json_encode($event['place_name']) ?>;
            const map = L.map('map').setView([lat, lng], 15);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map).bindPopup(`<b>${placeName}</b>`).openPopup();
        });
    </script>
    <?php endif; ?>
</body>
</html>