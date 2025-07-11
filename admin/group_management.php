<?php
require_once 'check_admin.php';
include 'header.php';

// Lấy từ khóa tìm kiếm
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Truy vấn nhóm + đếm số thành viên và bài viết
$sql = "SELECT g.*, 
            (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) AS member_count,
            (SELECT COUNT(*) FROM group_posts gp WHERE gp.group_id = g.id) AS post_count,
            u.username AS creator_name
        FROM groups g
        JOIN users u ON g.created_by = u.id";

if (!empty($search_term)) {
    $sql .= " WHERE g.name LIKE ?";
}

$sql .= " ORDER BY g.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($search_term)) {
    $like = '%' . $search_term . '%';
    $stmt->bind_param("s", $like);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h2>Quản lý Nhóm</h2>
</div>

<!-- Form tìm kiếm -->
<div class="row mb-3">
    <div class="col-md-6">
        <form action="group_management.php" method="GET" class="form-inline">
            <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo tên nhóm..." value="<?= htmlspecialchars($search_term) ?>">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </form>
    </div>
</div>

<!-- Bảng danh sách nhóm -->
<div class="table-responsive">
    <table class="table table-bordered table-hover text-center align-middle">
        <thead class="thead-dark">
            <tr>
                <th>Ảnh bìa</th>
                <th>Tên nhóm</th>
                <th>Mô tả</th>
                <th>Người tạo</th>
                <th>Thành viên</th>
                <th>Bài viết</th>
                <th>Loại nhóm</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($group = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($group['cover_image']) ?>" alt="Ảnh bìa" width="80" height="60" style="object-fit: cover; border-radius: 6px;">
                    </td>
                    <td><strong><?= htmlspecialchars($group['name']) ?></strong></td>
                    <td><?= htmlspecialchars(substr($group['description'], 0, 60)) ?>...</td>
                    <td><?= htmlspecialchars($group['creator_name']) ?></td>
                    <td><?= $group['member_count'] ?></td>
                    <td><?= $group['post_count'] ?></td>
                    <td><?= $group['privacy'] === 'private' ? 'Riêng tư' : 'Công khai' ?></td>
                    <td><?= $group['created_at'] ?></td>
                    <td>
                        <a href="delete_group.php?id=<?= $group['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa nhóm này sẽ xóa toàn bộ bài viết và thành viên trong nhóm. Bạn chắc chắn?');">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$stmt->close();
include 'footer.php';
?>
