<?php
function logDebug($message) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

function redirect($location) {
    header("Location: $location");
    exit();
}

function getEventData($conn, $event_id) {
    $query = "SELECT event_id, event_name, max_participants, current_participants, registration_fee, is_group 
              FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

function isUserRegistered($conn, $user_id, $event_id) {
    $query = "SELECT registration_id FROM registrations WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $user_id, $event_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($registration_id);
        $stmt->fetch();
        $stmt->close();
        return $registration_id;
    }
    $stmt->close();
    return false;
}

function hasTicket($conn, $registration_id) {
    $query = "SELECT ticket_id FROM tickets WHERE registration_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $registration_id);
    $stmt->execute();
    $stmt->store_result();
    $hasTicket = $stmt->num_rows > 0;
    $stmt->close();
    return $hasTicket;
}
?>