<?php
include 'db_connect.php'; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Common fields
    $uid = $_POST["uid"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $role = $_POST["role"];
    $status = "Pending"; // User approval needed
    $department = $_POST["department"] ?? NULL;

    // Initialize role-specific variables
    $batch = $house = $clubs = $organization_name = $position = NULL;

    // Handle role-specific fields
    if (strtolower($role) === "student") {
        $batch = $_POST["batch"] ?? NULL;
        $house = $_POST["house"] ?? NULL;
        $clubs = $_POST["clubs"] ?? NULL;
    } 
    elseif (strtolower($role) === "organizer") {
        $orgType = $_POST["organization_type"] ?? NULL;
        
        // Determine organization name based on type
        if ($orgType === "house") {
            $organization_name = $_POST["house_selection"] ?? NULL;
        } 
        elseif ($orgType === "club") {
            $organization_name = $_POST["club_selection"] ?? NULL;
        } 
        elseif ($orgType === "fest") {
            $organization_name = $_POST["fest_selection"] ?? NULL;
        }
        
        $position = $_POST["position"] ?? NULL;
        
        // First check if the position is already taken
        $check_sql = "SELECT user_id FROM organizers_position 
                      WHERE organization_name = ? AND position = ?";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            die("<script>alert('Database error: ".addslashes($conn->error)."'); window.history.back();</script>");
        }
        $check_stmt->bind_param("ss", $organization_name, $position);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            die("<script>alert('This position is already taken. Please choose another.'); window.history.back();</script>");
        }
        $check_stmt->close();
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1️⃣ Insert into users table
        $sql = "INSERT INTO users (user_id, name, email, pwd, rolee, phone_no, registration_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error in user statement: " . $conn->error);
        }
        
        $stmt->bind_param("sssssss", $uid, $name, $email, $password, $role, $phone, $status);

        if (!$stmt->execute()) {
            throw new Exception("Error inserting user: " . $stmt->error);
        }
        $stmt->close();

        // 2️⃣ Insert into role-specific tables
        if (strtolower($role) === "student") {
            // Insert into department table
            $student_sql = "INSERT INTO department (user_id, department) VALUES (?, ?)";
            $stmt = $conn->prepare($student_sql);
            if (!$stmt) {
                throw new Exception("Error in department statement: " . $conn->error);
            }
            $stmt->bind_param("ss", $uid, $department);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting department: " . $stmt->error);
            }
            $stmt->close();

            // Insert into clubandhouse table if house or clubs exist
            if ($house || $clubs) {
                $club_sql = "INSERT INTO clubandhouse (user_id, batch, house, clubs) 
                             VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($club_sql);
                if (!$stmt) {
                    throw new Exception("Error in clubandhouse statement: " . $conn->error);
                }
                $stmt->bind_param("ssss", $uid, $batch, $house, $clubs);
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting clubandhouse: " . $stmt->error);
                }
                $stmt->close();
            }
        } 
        elseif (strtolower($role) === "organizer") {
            // Insert into organizers_position table
            $organizer_sql = "INSERT INTO organizers_position (user_id, organization_name, position) 
                             VALUES (?, ?, ?)";
            $stmt = $conn->prepare($organizer_sql);
            if (!$stmt) {
                throw new Exception("Error in organizer statement: " . $conn->error);
            }
            $stmt->bind_param("sss", $uid, $organization_name, $position);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting organizer: " . $stmt->error);
            }
            $stmt->close();

            // Also insert into department table
            $dept_sql = "INSERT INTO department (user_id, department) VALUES (?, ?)";
            $stmt = $conn->prepare($dept_sql);
            if (!$stmt) {
                throw new Exception("Error in department statement: " . $conn->error);
            }
            $stmt->bind_param("ss", $uid, $department);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting department: " . $stmt->error);
            }
            $stmt->close();
        } 
        elseif (strtolower($role) === "faculty") {
            // Insert into department table
            $faculty_sql = "INSERT INTO department (user_id, department) VALUES (?, ?)";
            $stmt = $conn->prepare($faculty_sql);
            if (!$stmt) {
                throw new Exception("Error in faculty statement: " . $conn->error);
            }
            $stmt->bind_param("ss", $uid, $department);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting faculty: " . $stmt->error);
            }
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        
        // Success message
        echo "<script>
                alert('Sign-up request submitted! Awaiting admin approval.');
                window.location.href = 'loginn.php';
              </script>";
    } 
    catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>");
    }
}

$conn->close();
?>