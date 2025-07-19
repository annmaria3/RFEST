<?php
session_start();
include 'db_connect.php';

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Replace with actual session variable

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['poll_id']) && isset($_POST['option_id'])) {
    $poll_id = (int)$_POST['poll_id'];
    $option_id = (int)$_POST['option_id'];

    // Check if user has already voted
    $check_query = "SELECT * FROM poll_votes WHERE user_id = ? AND poll_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("si", $user_id, $poll_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('You have already voted on this poll.');</script>";
    } else {
        // Record the vote
        $vote_query = "INSERT INTO poll_votes (user_id, option_id, poll_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($vote_query);
        $stmt->bind_param("sii", $user_id, $option_id, $poll_id);
        $stmt->execute();

        // Update vote count
        $update_query = "UPDATE poll_options SET vote_count = vote_count + 1 WHERE option_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $option_id);
        $stmt->execute();

        echo "<script>alert('Vote recorded successfully!'); window.location.href='view_polls.php';</script>";
    }
    $stmt->close();
}

// Fetch all polls with options and user's voting status
$query = "
    SELECT p.poll_id, p.title, p.created_at, p.created_by, 
           po.option_id, po.option_text, po.vote_count,
           pv.user_id AS has_voted
    FROM polls p
    LEFT JOIN poll_options po ON p.poll_id = po.poll_id
    LEFT JOIN poll_votes pv ON p.poll_id = pv.poll_id AND pv.user_id = ?
    ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$polls = [];
while ($row = $result->fetch_assoc()) {
    $poll_id = $row['poll_id'];
    if (!isset($polls[$poll_id])) {
        $polls[$poll_id] = [
            'title' => $row['title'],
            'created_at' => $row['created_at'],
            'created_by' => $row['created_by'],
            'options' => [],
            'has_voted' => !is_null($row['has_voted'])
        ];
    }
    if ($row['option_id']) {
        $polls[$poll_id]['options'][] = [
            'option_id' => $row['option_id'],
            'text' => $row['option_text'],
            'votes' => $row['vote_count']
        ];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFEST - View Polls</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f4f4;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .poll {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        .poll h2 {
            margin: 0 0 10px;
            color: #28a745;
        }
        .poll p {
            margin: 5px 0;
            color: #666;
        }
        .options {
            margin-top: 15px;
        }
        .option {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        .vote-form label {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .vote-form input[type="radio"] {
            margin-right: 10px;
        }
        .vote-btn {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .vote-btn:hover {
            background: #218838;
        }
        .vote-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>RFEST Polls</h1>
        
        <?php if (!empty($polls)): ?>
            <?php foreach ($polls as $poll_id => $poll): ?>
                <div class="poll">
                    <h2><?php echo htmlspecialchars($poll['title']); ?></h2>
                    <p>Created by: <?php echo htmlspecialchars($poll['created_by']); ?></p>
                    <p>Created on: <?php echo htmlspecialchars($poll['created_at']); ?></p>
                    <div class="options">
                        <?php if (!empty($poll['options'])): ?>
                            <?php if ($poll['has_voted']): ?>
                                <?php foreach ($poll['options'] as $option): ?>
                                    <div class="option">
                                        <span><?php echo htmlspecialchars($option['text']); ?></span>
                                        <span>Votes: <?php echo $option['votes']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <p style="color: #666; margin-top: 10px;">You have already voted.</p>
                            <?php else: ?>
                                <form method="POST" class="vote-form">
                                    <input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>">
                                    <?php foreach ($poll['options'] as $option): ?>
                                        <label>
                                            <input type="radio" name="option_id" value="<?php echo $option['option_id']; ?>" required>
                                            <?php echo htmlspecialchars($option['text']); ?> (Votes: <?php echo $option['votes']; ?>)
                                        </label>
                                    <?php endforeach; ?>
                                    <button type="submit" class="vote-btn">Vote</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>No options available for this poll.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No polls have been created yet.</p>
        <?php endif; ?>
        
        <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>