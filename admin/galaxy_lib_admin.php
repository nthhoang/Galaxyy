<?php
require_once 'check_admin.php';
include 'header.php';
// Lấy danh sách chờ duyệt
$pending_result = $conn->query("SELECT * FROM cosmic_media WHERE status = 'pending' ORDER BY upload_date DESC");

// Lấy danh sách đã duyệt
$approved_result = $conn->query("SELECT * FROM cosmic_media WHERE status = 'approved' ORDER BY upload_date DESC");
?>

<div class="container py-4 py-md-5">
        <h1 class="text-center mb-4 mb-md-5">Bảng điều khiển Thư viện</h1>

        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="alert <?php echo strpos($_SESSION['admin_message'], 'lỗi') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['admin_message']; unset($_SESSION['admin_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <section class="mb-5">
            <h2 class="fs-4 fw-semibold border-bottom border-primary pb-2 mb-4">Nội dung chờ duyệt (<?php echo $pending_result->num_rows; ?>)</h2>
            <div class="row g-4">
                <?php if ($pending_result->num_rows > 0): ?>
                    <?php while($row = $pending_result->fetch_assoc()): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card bg-secondary text-white shadow-lg h-100 d-flex flex-column">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <?php $filePath = '../uploads/' . htmlspecialchars($row['file_name']); ?>
                                        <?php if ($row['file_type'] == 'image'): ?>
                                            <img src="<?php echo $filePath; ?>" class="img-fluid rounded mb-3" style="max-height: 200px; width: 100%; object-fit: contain; background-color: #000;">
                                        <?php else: ?>
                                            <video controls class="img-fluid rounded mb-3" style="max-height: 200px; width: 100%; background-color: #000;" preload="metadata"><source src="<?php echo $filePath; ?>"></video>
                                        <?php endif; ?>
                                        <p class="card-text text-white-50 small mb-1"><strong>Người gửi:</strong> <?php echo htmlspecialchars($row['uploader_name']); ?></p>
                                        <p class="card-text text-white-50 small"><strong>Mô tả:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <form action="galaxy_lib_action.php" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn duyệt?');">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Duyệt</button>
                                        </form>
                                        <form action="galaxy_lib_action.php" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn XÓA? Hành động này không thể hoàn tác.');">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-white-50 col-12">Không có nội dung nào đang chờ duyệt.</p>
                <?php endif; ?>
            </div>
        </section>

        <section>
            <h2 class="fs-4 fw-semibold border-bottom border-secondary pb-2 mb-4">Nội dung đã duyệt (<?php echo $approved_result->num_rows; ?>)</h2>
            <div class="row g-4">
                <?php if ($approved_result->num_rows > 0): ?>
                    <?php while($row = $approved_result->fetch_assoc()): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card bg-secondary text-white shadow-lg h-100 d-flex flex-column">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <?php $filePath = '../uploads/' . htmlspecialchars($row['file_name']); ?>
                                        <?php if ($row['file_type'] == 'image'): ?>
                                            <img src="<?php echo $filePath; ?>" class="img-fluid rounded mb-3" style="max-height: 200px; width: 100%; object-fit: contain; background-color: #000;">
                                        <?php else: ?>
                                            <video controls class="img-fluid rounded mb-3" style="max-height: 200px; width: 100%; background-color: #000;" preload="metadata"><source src="<?php echo $filePath; ?>"></video>
                                        <?php endif; ?>
                                        <p class="card-text text-white-50 small"><strong>Người gửi:</strong> <?php echo htmlspecialchars($row['uploader_name']); ?></p>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <form action="galaxy_lib_action.php" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn XÓA? Hành động này không thể hoàn tác.');">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-white-50 col-12">Chưa có nội dung nào được duyệt.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

</body>
</html>
<?php
$conn->close();
include 'footer.php';
?>
