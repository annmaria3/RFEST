<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 50px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <a href="backend/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mt-4">Welcome, Admin</h2>

        <div class="row">
            <!-- Manage Users -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Manage Users</h5>
                        <p>Add, Edit, or Delete Users</p>
                        <a href="manage_users.php" class="btn btn-primary">Go to Users</a>
                    </div>
                </div>
            </div>

            <!-- Manage Events -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Manage Events</h5>
                        <p>Create, Update, or Remove Events</p>
                        <a href="manage_events.php" class="btn btn-primary">Go to Events</a>
                    </div>
                </div>
            </div>

            <!-- View Reports -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">System Reports</h5>
                        <p>View User & Event Reports</p>
                        <a href="view_reports.php" class="btn btn-primary">View Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 