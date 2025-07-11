<?php
require_once 'check_admin.php';
include 'header.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'community';

$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$search_like = "%" . $search_term . "%";

if ($type == 'group') {
    $sql = "SELECT g.id, g.content, g.created_at, u.username, gr.name AS group_name
            FROM group_posts g
            JOIN users u ON g.user_id = u.id
            JOIN groups gr ON g.group_id = gr.id";

    if (!empty($search_term)) {
        $sql .= " WHERE g.content LIKE ? OR u.username LIKE ? OR gr.name LIKE ?";
    }

    $sql .= " ORDER BY g.created_at DESC";
    $stmt = $conn->prepare($sql);

    if (!empty($search_term)) {
        $stmt->bind_param("sss", $search_like, $search_like, $search_like);
    }
} else {
    // community
    $sql = "SELECT p.id, p.content, p.created_at, u.username
            FROM posts p
            JOIN users u ON p.user_id = u.id";

    if (!empty($search_term)) {
        $sql .= " WHERE p.content LIKE ? OR u.username LIKE ?";
    }

    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);

    if (!empty($search_term)) {
        $stmt->bind_param("ss", $search_like, $search_like);
    }
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý Bài đăng Cộng đồng</h1>
</div>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link <?= $type == 'community' ? 'active' : '' ?>" href="?type=community">Bài đăng Cộng đồng</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $type == 'group' ? 'active' : '' ?>" href="?type=group">Bài đăng Nhóm</a>
  </li>
</ul>


<div class="row mb-3">
    <div class="col-md-6">
        <form action="community_management.php" method="GET" class="form-inline">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
            <input type="text" name="search" class="form-control mr-2" placeholder="Tìm theo nội dung, username..." value="<?= htmlspecialchars($search_term) ?>">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </form>
    </div>
</div>


<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Người đăng</th>
                <th>Nội dung</th>
                <th>Ngày đăng</th>
                <?php if ($type == 'group'): ?> <th>Nhóm</th><?php endif; ?>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
           <?php while ($post = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $post['id'] ?></td>
                <td><?= htmlspecialchars($post['username']) ?></td>
                <td><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</td>
                <td><?= $post['created_at'] ?></td>
                    <?php if ($type == 'group'): ?>
                        <td> <div><strong>Nhóm:</strong> <?= htmlspecialchars($post['group_name']) ?></div></td>
                        <td>
                        <a href="view_group_post.php?id=<?= $post['id'] ?>" class="btn btn-info btn-sm">Xem</a>
                        <a href="delete_group_post.php?id=<?= $post['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa bài đăng nhóm này sẽ xóa toàn bộ dữ liệu liên quan. Bạn chắc chắn?');">Xóa</a>
                        </td>
                    <?php else: ?>
                        <td>
                        <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-info btn-sm">Xem</a>
                        <a href="delete_community_post.php?id=<?= $post['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa bài đăng này sẽ xóa toàn bộ dữ liệu liên quan. Bạn chắc chắn?');">Xóa</a>
                        </td>
                    <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$stmt->close();
include 'footer.php';
?>