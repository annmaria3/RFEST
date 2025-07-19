<?php
// Include the database connection file
require_once 'db_connect.php';

// Verify $conn is defined
if (!isset($conn) || $conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . ($conn->connect_error ?? 'Connection not established')]));
}

try {
    // Fetch all events (no status filter)
    $query = "SELECT event_id, event_name, event_type, event_date, start_time, end_time, 
              venue, registration_fee, is_virtual, max_participants, current_participants 
              FROM events";
    $result = $conn->query($query);

    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }

    // Format events for FullCalendar
    $formattedEvents = [];
    while ($event = $result->fetch_assoc()) {
        $start = $event['event_date'] . 'T' . $event['start_time'];
        $end = $event['end_time'] ? $event['event_date'] . 'T' . $event['end_time'] : null;

        $formattedEvents[] = [
            'id' => $event['event_id'],
            'title' => $event['event_name'],
            'start' => $start,
            'end' => $end,
            'extendedProps' => [
                'event_type' => $event['event_type'],
                'venue' => $event['venue'],
                'registration_fee' => $event['registration_fee'],
                'is_virtual' => $event['is_virtual'],
                'max_participants' => $event['max_participants'],
                'current_participants' => $event['current_participants']
            ]
        ];
    }

    // Free result set
    $result->free();

    // Return JSON
    header('Content-Type: application/json');
    echo json_encode($formattedEvents);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>