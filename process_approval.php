<?php
include 'db_connect.php'; // Database connection

// Check if user_id and action are set
if (isset($_GET['user_id']) && isset($_GET['action'])) {
    $user_id = $_GET['user_id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        // ✅ Approve user → Update status to 'Approved'
        $stmt = $conn->prepare("UPDATE users SET status = 'Approved' WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            header("Location: approve_user.php?message=User Approved Successfully");
        } else {
            header("Location: approve_user.php?error=Failed to Approve User");
        }
    } elseif ($action == 'reject') {
        // ❌ Reject user → DELETE from the database
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            header("Location: approve_user.php?message=User Rejected and Deleted");
        } else {
            header("Location: approve_user.php?error=Failed to Delete User");
        }
    } else {
        header("Location: approve_user.php?error=Invalid Action");
    }

    $stmt->close();
} else {
    header("Location: approve_user.php?error=Missing Parameters");
}

$conn->close();
?>
