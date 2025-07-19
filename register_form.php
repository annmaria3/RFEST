<?php
session_start();
include 'db_connect.php';
include 'functions.php';

$user_id = $_GET['user_id'] ?? null;
$event_id = $_GET['event_id'] ?? null;
$portal = $_GET['portal'] ?? '';

if (!$event_id || !$user_id) {
    redirect("ind.php?portal=$portal&error=invalid_data");
}

$eventData = getEventData($conn, $event_id);
if (!$eventData) {
    redirect("ind.php?portal=$portal&error=invalid_event");
}

$isGroup = $eventData['is_group'] === 'yes';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register for <?php echo $eventData['event_name']; ?></title>
    <style>
        #groupDetails { display: none; }
    </style>
</head>
<body>
    <h1>Register for <?php echo $eventData['event_name']; ?></h1>
    <form method="POST" action="process_registration.php">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
        <input type="hidden" name="portal" value="<?php echo $portal; ?>">

        <?php if ($isGroup): ?>
            <label>
                <input type="radio" name="group_option" value="team" onclick="toggleGroupDetails(true)" checked> Form a Team
            </label>
            <label>
                <input type="radio" name="group_option" value="auto" onclick="toggleGroupDetails(false)"> Auto-Assign Group
            </label>
            <div id="groupDetails">
                <label>Team Name: <input type="text" name="group_name" required></label><br>
                <label>Team Members (User IDs, comma-separated): 
                    <input type="text" name="group_members" placeholder="e.g., 2,3,4" required>
                </label><br>
                <small>Include your ID (<?php echo $user_id; ?>) if in the team.</small>
            </div>
        <?php endif; ?>

        <input type="submit" value="Register">
    </form>

    <script>
        function toggleGroupDetails(show) {
            const groupDetails = document.getElementById('groupDetails');
            if (groupDetails) {
                groupDetails.style.display = show ? 'block' : 'none';
                groupDetails.querySelectorAll('input').forEach(input => input.required = show);
            }
        }
        <?php if ($isGroup): ?>
            toggleGroupDetails(true);
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conn->close(); ?>