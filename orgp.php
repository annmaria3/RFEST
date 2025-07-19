<?php
session_start();
include_once 'db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Handle Form Submissions ---

// Handle event creation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $event_name = $_POST['event_name'];
    $event_desc = $_POST['event_desc'];
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = $_POST['venue'];
    $registration_fee = $_POST['registration_fee'];
    $is_virtual = $_POST['is_virtual'];
    $max_participants = $_POST['max_participants'];
    $portal_id = $_POST['portal_id'];
    $event_image = $_POST['event_image'];
    $has_waitlist = isset($_POST['has_waitlist']) ? 1 : 0;
    $registration_enabled = isset($_POST['registration_enabled']) ? 1 : 1; // Default to enabled

    $insertEvent = "INSERT INTO events (
        event_name, event_desc, event_type, event_date, start_time, end_time,
        venue, registration_fee, organizer_id, is_virtual, max_participants,
        current_participants, portal_id, status, event_image, is_approved,
        has_waitlist, registration_enabled
    ) VALUES (
        '$event_name', '$event_desc', '$event_type', '$event_date', '$start_time', '$end_time',
        '$venue', '$registration_fee', '$user_id', '$is_virtual', '$max_participants',
        0, '$portal_id', 'open', '$event_image', 0,
        '$has_waitlist', '$registration_enabled'
    )";

    if (mysqli_query($conn, $insertEvent)) {
        echo "<script>alert('Event created successfully and is pending admin/faculty approval.');</script>";
    } else {
        echo "<script>alert('Error creating event: " . mysqli_error($conn) . "');</script>";
    }
}

// Handle notice creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_notice'])) {
    $notice_title = $_POST['notice_title'];
    $notice_description = $_POST['notice_description'];

    $insertNotice = "INSERT INTO noticeboard (title, description, created_by, creation_date)
                     VALUES ('$notice_title', '$notice_description', '$user_id', NOW())";
    mysqli_query($conn, $insertNotice);
}

// Handle poll creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_poll'])) {
    $poll_question = $_POST['poll_question'];
    $poll_options_str = $_POST['poll_options'];
    $poll_options = explode(',', $poll_options_str);
    $event_id_poll = $_POST['event_id_poll'] ?? null; // Assuming you might want to link polls to events

    $insertPoll = "INSERT INTO polls (event_id, question, options, created_by, created_at)
                   VALUES ('$event_id_poll', '$poll_question', '" . mysqli_real_escape_string($conn, json_encode($poll_options)) . "', '$user_id', NOW())";
    mysqli_query($conn, $insertPoll);
}

// Handle image upload to gallery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $image_url = $_POST['gallery_image']; // Assuming you are using a URL for now

    $insertImage = "INSERT INTO image_gallery (images) VALUES ('$image_url')";
    mysqli_query($conn, $insertImage);
}

// --- Fetch Data ---

// Fetch user information
$userQuery = "SELECT name FROM users WHERE user_id = '$user_id'";
$userResult = mysqli_query($conn, $userQuery);
$user = mysqli_fetch_assoc($userResult);

// Fetch user points
$pointsQuery = "SELECT SUM(points_awarded) AS total_points FROM activitypoints WHERE user_id = '$user_id'";
$pointsResult = mysqli_query($conn, $pointsQuery);
$points = mysqli_fetch_assoc($pointsResult)['total_points'] ?? 0;

// Fetch user certificate count
$certCountQuery = "SELECT COUNT(*) AS certs FROM certificates WHERE user_id = '$user_id'";
$certResult = mysqli_query($conn, $certCountQuery);
$certificates = mysqli_fetch_assoc($certResult)['certs'] ?? 0;

// Fetch all event portals
$portalsQuery = "SELECT * FROM event_portals";
$portalsResult = mysqli_query($conn, $portalsQuery);

// Determine selected portal
$selected_portal_id = $_GET['portal_id'] ?? null;
$selected_portal = null;
$portal_desc = "";

// Fetch selected portal details
if ($selected_portal_id) {
    $portalQuery = "SELECT * FROM event_portals WHERE portal_id = '$selected_portal_id'";
    $portalResult = mysqli_query($conn, $portalQuery);
    if (mysqli_num_rows($portalResult)) {
        $selected_portal = mysqli_fetch_assoc($portalResult);
        $portal_desc = $selected_portal['portal_desc'];
    }
}

// Fetch events created by the logged-in organizer for the selected portal
$eventQuery = "SELECT *,
               (SELECT COUNT(*) FROM registrations WHERE registrations.event_id = events.event_id) AS registered_count
               FROM events
               WHERE portal_id = '$selected_portal_id' AND organizer_id = '$user_id'";
$eventResult = mysqli_query($conn, $eventQuery);

// Fetch featured events (no change needed here, but consider if only relevant to the organizer's portals)
$featuredQuery = "SELECT event_name FROM events WHERE is_approved = 1 ORDER BY RAND() LIMIT 8";
$featuredResult = mysqli_query($conn, $featuredQuery);

// Fetch image gallery (no change needed here)
$galleryQuery = "SELECT * FROM image_gallery ORDER BY id DESC LIMIT 8";
$galleryResult = mysqli_query($conn, $galleryQuery);

// Fetch notice board items (no change needed here)
$noticeQuery = "SELECT * FROM noticeboard ORDER BY creation_date DESC";
$noticeResult = mysqli_query($conn, $noticeQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Portal</title>
    <link rel="stylesheet" href="stup.css">
    <link rel="stylesheet" href="styleor1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center; align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white; padding: 20px;
            border-radius: 8px; width: 90%; max-width: 600px;
            position: relative; /* For close button positioning */
        }
        .modal-content input[type="text"],
        .modal-content input[type="date"],
        .modal-content input[type="time"],
        .modal-content input[type="number"],
        .modal-content textarea,
        .modal-content select,
        .modal-content input[type="checkbox"] {
            width: calc(100% - 20px); /* Adjust for padding */
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .modal-content h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        .modal-content button, .modal-content a {
            display: inline-block;
            padding: 10px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            color: white;
            text-decoration: none;
        }
        .modal-content button[type="submit"] {
            background-color: #5cb85c;
        }
        .modal-content button[type="button"] {
            background-color: #f44336;
        }
        .modal-content .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 20px;
            font-weight: bold;
            color: #000;
            cursor: pointer;
            opacity: 0.6;
        }
        .modal-content .close:hover {
            opacity: 1;
        }

        /* Notice Board Scroll */
        .notices {
            max-height: 300px; /* Adjust as needed */
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .notices::-webkit-scrollbar {
            width: 8px;
        }
        .notices::-webkit-scrollbar-thumb {
            background-color: #aaa;
            border-radius: 4px;
        }
        .notices::-webkit-scrollbar-thumb:hover {
            background-color: #777;
        }
        .notice {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .notice:last-child {
            border-bottom: none;
        }
        .notice h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.1em;
        }
        .notice p {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .event-buttons a.event-edit-btn {
            background-color: #007bff; /* Blue */
        }

        .event-buttons a.event-participants-btn {
            background-color: #28a745; /* Green */
        }

        .modal-overlay#participantsModal {
            display: none;
        }

        .modal-overlay#participantsModal .modal-content {
            max-height: 80vh;
            overflow-y: auto;
        }

        .participant-list {
            list-style: none;
            padding: 0;
        }

        .participant-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .participant-list li:last-child {
            border-bottom: none;
        }

        .waitlist-indicator {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <aside class="sidebar user-sidebar">
        <div class="user-profile">
            <div class="avatar"></div>
            <div class="user-info">
                <h3><?php echo $user['name']; ?></h3>
                <p>Points: <?php echo $points; ?></p>
                <p>Certificates: <?php echo $certificates; ?></p>
            </div>
        </div>

        <div class="archives">
            <h3>PORTALS</h3>
            <form method="GET" action="">
                <select name="portal_id" onchange="this.form.submit()">
                    <option disabled selected>Select Portal</option>
                    <?php
                    $portalsResult = mysqli_query($conn, "SELECT * FROM event_portals");
                    while ($portal = mysqli_fetch_assoc($portalsResult)) :
                        // You might want to filter portals here based on user's organizer_type if needed on this page too
                        ?>
                        <option value="<?php echo $portal['portal_id']; ?>" <?php if ($selected_portal_id == $portal['portal_id']) echo 'selected'; ?>>
                            <?php echo $portal['portal_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <button class="logout-btn" onclick="window.location.href='../../login/log.html';">LOGOUT</button>
    </aside>

    <main class="main-content">
        <header class="header">
            <nav class="nav">
                <h1>RFEST</h1>
                <div class="nav-links">
                    <a href="org1.php" class="active">Home</a>
                    <a href="#">ABOUT</a>
                    <a href="#">CONTACT</a>
                </div>
                <div class="nav-controls">
                    <input type="search" placeholder="Search portal..." class="search-input">
                    <a href="#" class="profile-btn">👤</a>
                    <button class="calendar-btn">📅</button>
                    <button class="theme-toggle">🌙</button>
                    <button class="help-btn">?</button>
                </div>
            </nav>
        </header>

        <?php if ($selected_portal): ?>
            <div class="portal-heading">
                <h1><?php echo $selected_portal['portal_name']; ?></h1>
                <p class="portal-description"><?php echo $portal_desc; ?></p>
            </div>
        <?php endif; ?>

        <section class="event-dashboard">
            <div class="event-grid">
                <?php if ($selected_portal_id && mysqli_num_rows($eventResult)) : ?>
                    <?php while ($event = mysqli_fetch_assoc($eventResult)) : ?>
                        <div class="event-card">
                            <?php if ($event['status'] == 'closed'): ?>
                                <div class="registration-closed-banner">🚫 Registration Closed</div>
                            <?php elseif ($event['registered_count'] >= $event['max_participants'] && $event['registration_enabled']): ?>
                                <div class="waitlist-banner">Waitlist Active</div>
                            <?php elseif (!$event['registration_enabled']): ?>
                                <div class="no-registration-banner">No Registration</div>
                            <?php endif; ?>
                            <img src="<?php echo $event['event_image']; ?>" alt="Event Poster" class="event-poster">
                            <div class="event-info">
                                <p class="event-description"><?php echo $event['event_name']; ?>: <?php echo $event['event_desc']; ?></p>
                                <div class="event-buttons">
                                    <a href="edit_event.php?event_id=<?php echo $event['event_id']; ?>" class="event-edit-btn">Edit Details</a>
                                    <button onclick="openParticipantsModal(<?php echo $event['event_id']; ?>)" class="event-participants-btn">View Participants</button>
                                    <div class="max-participants">👥 <?php echo $event['max_participants']; ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Please select a portal to view events created by you.</p>
                <?php endif; ?>
            </div>

            <section class="recommended-events">
                <h2>FEATURED EVENTS</h2>
                <div class="event-carousel">
                    <button class="carousel-btn prev">←</button>
                    <div class="carousel-items">
                        <?php while ($feat = mysqli_fetch_assoc($featuredResult)) : ?>
                            <div class="carousel-item" data-specs="<?php echo $feat['event_name']; ?>">
    <?php echo $feat['event_name']; ?>
</div>
<?php endwhile; ?>
</div>
<button class="carousel-btn next">→</button>
</div>
<div class="event-specs" id="event-specs">Hover over an event to see details</div>
</section>

<section class="image-gallery">
    <h2>Image Gallery</h2>
    <div class="gallery-container">
        <div class="gallery">
            <?php while ($img = mysqli_fetch_assoc($galleryResult)) : ?>
                <img src="<?php echo $img['images']; ?>" alt="Event Image">
            <?php endwhile; ?>
        </div>
    </div>
</section>
</section>
</main>

<aside class="sidebar notice-board">
    <h2>NOTICE BOARD</h2>
    <div class="notices">
        <?php while ($notice = mysqli_fetch_assoc($noticeResult)) : ?>
            <div class="notice">
                <h3><?php echo $notice['title']; ?></h3>
                <p><?php echo $notice['description']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="notice-controls">
        <button id="openEventModal">Create Event</button>
        <button id="openNoticeModal">Create Notice</button>
        <button id="openPollModal">Create Poll</button>
        <button id="openImageModal">Add Image</button>
    </div>
</aside>
</div>

<!-- Event Modal -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('eventModal')">&times;</span>
        <h2>Create Event</h2>
        <form method="POST">
            <input type="hidden" name="create_event" value="1">
            <input type="text" name="event_name" placeholder="Event Name" required>
            <textarea name="event_desc" placeholder="Event Description" required></textarea>
            <input type="text" name="event_type" placeholder="Type (e.g., Workshop)">
            <input type="date" name="event_date" required>
            <input type="time" name="start_time" required>
            <input type="time" name="end_time" required>
            <input type="text" name="venue" placeholder="Venue">
            <input type="number" name="registration_fee" placeholder="Fee (₹)">
            <input type="text" name="is_virtual" placeholder="Virtual? (yes/no)">
            <input type="number" name="max_participants" placeholder="Max Participants" required>
            <select name="portal_id" required>
                <option disabled selected>Select Portal</option>
                <?php
                $portalsResult = mysqli_query($conn, "SELECT * FROM event_portals");
                while ($p = mysqli_fetch_assoc($portalsResult)) {
                    echo "<option value='{$p['portal_id']}'>{$p['portal_name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="event_image" placeholder="Image URL">
            <input type="checkbox" name="has_waitlist" id="has_waitlist">
            <label for="has_waitlist">Enable Waitlist</label><br>
            <input type="checkbox" name="registration_enabled" id="registration_enabled" checked>
            <label for="registration_enabled">Enable Registration</label><br>
            <button type="submit">Create</button>
            <button type="button" onclick="closeModal('eventModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Notice Modal -->
<div class="modal-overlay" id="noticeModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('noticeModal')">&times;</span>
        <h2>Create Notice</h2>
        <form method="POST">
            <input type="hidden" name="create_notice" value="1">
            <input type="text" name="notice_title" placeholder="Title" required>
            <textarea name="notice_description" placeholder="Description" required></textarea>
            <button type="submit">Post</button>
            <button type="button" onclick="closeModal('noticeModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Poll Modal -->
<div class="modal-overlay" id="pollModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('pollModal')">&times;</span>
        <h2>Create Poll</h2>
        <form method="POST">
            <input type="hidden" name="create_poll" value="1">
            <input type="text" name="poll_question" placeholder="Poll Question" required>
            <input type="text" name="poll_options" placeholder="Options (comma separated)" required>
            <input type="number" name="event_id_poll" placeholder="Event ID (optional)">
            <button type="submit">Post</button>
            <button type="button" onclick="closeModal('pollModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Image Modal -->
<div class="modal-overlay" id="imageModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('imageModal')">&times;</span>
        <h2>Upload Image</h2>
        <form method="POST">
            <input type="hidden" name="upload_image" value="1">
            <input type="text" name="gallery_image" placeholder="Image URL" required>
            <button type="submit">Upload</button>
            <button type="button" onclick="closeModal('imageModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Participants Modal -->
<div class="modal-overlay" id="participantsModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('participantsModal')">&times;</span>
        <h2>Participants List</h2>
        <div id="participantsListContainer"></div>
    </div>
</div>

<!-- Scripts -->
<script>
    // Open modal handlers
    document.getElementById('openEventModal').onclick = function () {
        document.getElementById('eventModal').style.display = 'flex';
    };
    document.getElementById('openNoticeModal').onclick = function () {
        document.getElementById('noticeModal').style.display = 'flex';
    };
    document.getElementById('openPollModal').onclick = function () {
        document.getElementById('pollModal').style.display = 'flex';
    };
    document.getElementById('openImageModal').onclick = function () {
        document.getElementById('imageModal').style.display = 'flex';
    };

    function openParticipantsModal(eventId) {
        document.getElementById('participantsModal').style.display = 'flex';
        loadParticipants(eventId);
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    window.onclick = function (event) {
        ['eventModal', 'noticeModal', 'pollModal', 'imageModal', 'participantsModal'].forEach(function (id) {
            var modal = document.getElementById(id);
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    }

    function loadParticipants(eventId) {
        var participantsListContainer = document.getElementById('participantsListContainer');
        participantsListContainer.innerHTML = '<p>Loading participants...</p>';
        fetch('participant_list.php?event_id=' + eventId)
            .then(response => response.text())
            .then(data => {
                participantsListContainer.innerHTML = data;
            })
            .catch(error => {
                participantsListContainer.innerHTML = '<p>Error loading participants.</p>';
            });
    }
</script>

<script src="org.js"></script>
<script src="stup.js"></script>
</body>
</html>
