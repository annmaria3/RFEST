<?php
include 'db.php';

$notices = [];
$stmt = $conn->prepare("SELECT title, description FROM noticeboard ORDER BY creation_date DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
}
$stmt->close();
?>

<link rel="stylesheet" href="sidebar.css">

<aside class="sidebar notice-board">
    <h2>NOTICE BOARD</h2>
    <div class="notices">
        <?php if (!empty($notices)): ?>
            <?php foreach ($notices as $notice): ?>
                <div class="notice">
                    <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                    <p><?php echo htmlspecialchars($notice['description']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notices available.</p>
        <?php endif; ?>
    </div>
</aside>
