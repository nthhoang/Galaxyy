<?php
include 'db.php';

$author_id = intval($_POST['author_id']);

$sql = "SELECT follower_id FROM follows WHERE followed_id = ? AND follower_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $author_id, $author_id);
$stmt->execute();
$result = $stmt->get_result();

$followers = [];
while ($row = $result->fetch_assoc()) {
    $followers[] = $row['follower_id'];
}

echo json_encode(['followers' => $followers]);
?>
