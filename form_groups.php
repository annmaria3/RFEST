<?php
require_once 'db_connect.php';
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($event_id <= 0) {
    die("Invalid event ID");
}

// Fetch auto-group registrants
$query = "SELECT r.user_id, u.name 
          FROM registrations r 
          JOIN users u ON r.user_id = u.user_id 
          WHERE r.event_id = ? AND r.auto_group = 'yes' 
          AND r.user_id NOT IN (SELECT group_leader FROM groups WHERE event_id = ?)
          AND r.user_id NOT IN (SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(t.group_members, ',', n.n), ',', -1) 
                                FROM groups t 
                                CROSS JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) n 
                                WHERE t.event_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $event_id, $event_id, $event_id);
$stmt->execute();
$registrants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($registrants)) {
    die("No users available for auto-grouping");
}

// Group size (e.g., 4 members per team)
$group_size = 4;
$groups = array_chunk($registrants, $group_size);

$conn->begin_transaction();
try {
    foreach ($groups as $index => $group) {
        $group_name = "AutoGroup_" . ($index + 1);
        $leader_id = $group[0]['user_id'];
        $member_ids = implode(',', array_column($group, 'user_id'));

        $query = "INSERT INTO groups (event_id, group_name, group_leader, group_members) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isis", $event_id, $group_name, $leader_id, $member_ids);
        $stmt->execute();

        // Update auto_group status
        $query = "UPDATE registrations SET auto_group = 'no' WHERE user_id IN (" . 
                 implode(',', array_fill(0, count($group), '?')) . ") AND event_id = ?";
        $stmt = $conn->prepare($query);
        $params = array_merge(array_column($group, 'user_id'), [$event_id]);
        $types = str_repeat('i', count($group)) . 'i';
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
    $conn->commit();
    echo "Groups formed successfully!";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
$conn->close();
?>