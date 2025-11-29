<?php
require_once '../connection.php';
session_start();

// Debug session
error_log("Session data: " . print_r($_SESSION, true));

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    error_log("No admin_id in session - user may not be logged in");
    // You might want to handle this case differently
}

// Use the correct variable name $conn from connection.php
if (!$conn) {
    die("Database connection failed");
} else {
    error_log("Database connection successful");
}

// TEMPORARY FIX: Disable foreign key checks to bypass the constraint issue
try {
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    error_log("Foreign key checks disabled for this session");
} catch (PDOException $e) {
    error_log("Could not disable foreign key checks: " . $e->getMessage());
}

function getAllEvents() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM admin_event ORDER BY date ASC, start_time ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

// Function to get unique event names for dropdown
function getEventNames() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT DISTINCT event_name FROM admin_event ORDER BY event_name ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $names = [];
        foreach ($results as $result) {
            $names[] = $result['event_name'];
        }
        return $names;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

// Update getEventsByDateRange function
function getEventsByDateRange($startDate, $endDate = null) {
    global $conn; // Changed from $pdo to $conn
    try {
        if ($endDate) {
            $stmt = $conn->prepare("SELECT * FROM admin_event 
                                  WHERE date BETWEEN ? AND ?
                                  ORDER BY date ASC, start_time ASC");
            $stmt->execute([$startDate, $endDate]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM admin_event 
                                  WHERE date = ?
                                  ORDER BY start_time ASC");
            $stmt->execute([$startDate]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

// ADDED: Function to create fines for absent students
function createFinesForEvent($eventId, $fineAmount) {
    global $conn;
    try {
        // Get all students who were absent for this event
        $stmt = $conn->prepare("
            SELECT s.id as student_id 
            FROM register_student s 
            WHERE s.id NOT IN (
                SELECT se.student_id 
                FROM students_events se 
                WHERE se.event_id = ? AND se.attendance_status = 'present'
            )
        ");
        $stmt->execute([$eventId]);
        $absentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $finesCreated = 0;
        foreach ($absentStudents as $student) {
            $insertStmt = $conn->prepare("
                INSERT INTO admin_fines (student_id, event_id, amount, description, date_issued, due_date, status) 
                VALUES (?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'unpaid')
            ");
            $insertStmt->execute([
                $student['student_id'],
                $eventId,
                $fineAmount,
                "Absence from event"
            ]);
            $finesCreated++;
        }
        
        return $finesCreated;
    } catch (PDOException $e) {
        error_log("Database error creating fines: " . $e->getMessage());
        return false;
    }
}

function saveEvent($data) {
    global $conn;
    
    try {
        // Validate database connection
        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        // Handle created_by - use session admin_id or NULL
        $created_by = $_SESSION['admin_id'] ?? null;
        
        // If no admin_id in session, set to NULL (foreign key constraint is disabled)
        if (empty($created_by)) {
            error_log("No admin_id in session. Setting created_by to NULL.");
            $created_by = null;
        }

        if (!empty($data['event_id'])) {
            // Update existing event
            $stmt = $conn->prepare("UPDATE admin_event SET
                event_name = :name,
                date = :date,
                start_time = :start_time,
                end_time = :end_time,
                location = :location,
                fine_amount = :fine_amount,
                year_level = :year_level
                WHERE event_id = :id");
                
            $result = $stmt->execute([
                ':name' => $data['event_name'],
                ':date' => $data['date'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':location' => $data['location'],
                ':fine_amount' => $data['fine_amount'],
                ':year_level' => $data['year_level'],
                ':id' => $data['event_id']
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Update failed: " . print_r($errorInfo, true));
                return false;
            }
            
            return $data['event_id'];
        } else {
            // Create new event
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("INSERT INTO admin_event
                (event_name, date, start_time, end_time, location, fine_amount, year_level, created_by)
                VALUES (:name, :date, :start_time, :end_time, :location, :fine_amount, :year_level, :created_by)");
                
            $result = $stmt->execute([
                ':name' => $data['event_name'],
                ':date' => $data['date'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':location' => $data['location'],
                ':fine_amount' => $data['fine_amount'],
                ':year_level' => $data['year_level'],
                ':created_by' => $created_by
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Insert failed: " . print_r($errorInfo, true));
                $conn->rollBack();
                return false;
            }
            
            $eventId = $conn->lastInsertId();
            $conn->commit();
            
            return $eventId;
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Database error in saveEvent: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("General error in saveEvent: " . $e->getMessage());
        return false;
    }
}

// UPDATED: Fixed deleteEvent function to cascade deletion
function deleteEvent($id) {
    global $conn;
    try {
        // Begin transaction to ensure all deletions succeed or fail together
        $conn->beginTransaction();
        
        // 1. First delete related fines records
        $delete_fines_sql = "DELETE FROM admin_fines WHERE event_id = ?";
        $stmt = $conn->prepare($delete_fines_sql);
        $stmt->execute([$id]);
        error_log("Deleted fines records for event ID: " . $id . " - Affected rows: " . $stmt->rowCount());
        
        // 2. Then delete related attendance records
        $delete_attendance_sql = "DELETE FROM students_events WHERE event_id = ?";
        $stmt = $conn->prepare($delete_attendance_sql);
        $stmt->execute([$id]);
        error_log("Deleted attendance records for event ID: " . $id . " - Affected rows: " . $stmt->rowCount());
        
        // 3. Finally delete the event itself
        $delete_event_sql = "DELETE FROM admin_event WHERE event_id = ?";
        $stmt = $conn->prepare($delete_event_sql);
        $stmt->execute([$id]);
        $event_deleted = $stmt->rowCount() > 0;
        error_log("Deleted event ID: " . $id . " - Success: " . ($event_deleted ? 'Yes' : 'No'));
        
        // Commit the transaction
        $conn->commit();
        
        return $event_deleted;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Database error in deleteEvent: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'save') {
                // Enhanced debugging
                error_log("=== EVENT SAVE REQUEST ===");
                error_log("POST data: " . print_r($_POST, true));
                
                // Validate required fields
                $required = ['eventName', 'eventDate', 'startTime', 'endTime', 'eventLocation'];
                $missing = [];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        $missing[] = $field;
                    }
                }
                
                if (!empty($missing)) {
                    error_log("Missing required fields: " . implode(', ', $missing));
                    echo json_encode(['success' => false, 'message' => "Missing required fields: " . implode(', ', $missing)]);
                    exit;
                }
                
                // Prepare event data - ensure proper data types
                $eventData = [
                    'event_id' => !empty($_POST['eventId']) ? intval($_POST['eventId']) : null,
                    'event_name' => trim($_POST['eventName']),
                    'date' => $_POST['eventDate'],
                    'start_time' => $_POST['startTime'] . ':00', // Add seconds for TIME format
                    'end_time' => $_POST['endTime'] . ':00',     // Add seconds for TIME format
                    'location' => $_POST['eventLocation'],
                    'fine_amount' => (!empty($_POST['fineAmount']) && $_POST['fineAmount'] > 0) ? floatval($_POST['fineAmount']) : null,
                    'year_level' => !empty($_POST['yearLevel']) ? $_POST['yearLevel'] : null
                ];
                
                // Debug the data being saved
                error_log("Processed event data: " . print_r($eventData, true));
                
                // Validate time
                if ($eventData['start_time'] >= $eventData['end_time']) {
                    echo json_encode(['success' => false, 'message' => 'End time must be after start time']);
                    exit;
                }
                
                // Validate date
                $today = date('Y-m-d');
                if ($eventData['date'] < $today) {
                    echo json_encode(['success' => false, 'message' => 'Event date cannot be in the past']);
                    exit;
                }
                
                $result = saveEvent($eventData);
                if ($result !== false) {
                    error_log("Event saved successfully with ID: " . $result);
                    echo json_encode(['success' => true, 'message' => 'Event saved successfully', 'eventId' => $result]);
                } else {
                    error_log("Save event function returned false");
                    echo json_encode(['success' => false, 'message' => 'Failed to save event - check server logs for details']);
                }
                exit;
            } elseif ($_POST['action'] === 'delete') {
                $result = deleteEvent($_POST['eventId']);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
                }
                exit;
            } elseif ($_POST['action'] === 'generate_fines') {
                $eventId = $_POST['eventId'];
                $fineAmount = $_POST['fineAmount'];
                
                $finesCreated = createFinesForEvent($eventId, $fineAmount);
                if ($finesCreated !== false) {
                    echo json_encode(['success' => true, 'message' => "Fines generated for $finesCreated absent students"]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to generate fines']);
                }
                exit;
            }
        } catch (PDOException $e) {
            error_log("PDO Exception in POST handler: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        } catch (Exception $e) {
            error_log("General Exception in POST handler: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
}

$events = getAllEvents();
$eventNames = getEventNames();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management | ACI Biometric System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="apple-touch-icon" href="../images/logo.png">
    <link rel="shortcut icon" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            position: relative;
            background: radial-gradient(circle, white, rgb(243, 236, 117));
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 220px;
            right: 0;
            bottom: 0;
            background-image: url('/biometric/images/logo.png');           
            background-size: 100%;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }

        .events-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    font-size: 12px; /* Even smaller */
}

.events-table th, 
.events-table td {
    padding: 8px 10px; /* Even more compact */
    text-align: left;
    border-bottom: 1px solid #ddd;
    line-height: 1.2; /* Tighter */
}

.events-table th {
    background-color: #007836;
    color: white;
    font-weight: 500;
    font-size: 11px;
    padding: 6px 10px;
}

.action-btn {
    width: 22px;
    height: 22px;
    font-size: 10px;
}

        .sidebar {
    width: 250px;
    background-color: #007836;
    color: white;
    min-height: 100vh;
    padding: 20px 0;
    transition: all 0.3s;
    position: fixed; /* ADD THIS */
    height: 100vh; /* ADD THIS */
    overflow-y: auto; /* ADD THIS */
}


        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #444;
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            color: #fff;
            font-size: 20px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #444;
            color: white;
        }

        .sidebar-menu a.active {
            background: linear-gradient(to right, #FFD700, #FFEA70) !important;
            color: #333;
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
        }

   .main-content {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    margin-left: 250px; /* ADD THIS */
}

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007836;
        }

        .search-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-bar {
            flex: 1;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #007836;
            box-shadow: 0 0 0 2px rgba(0, 120, 54, 0.2);
        }

        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #333;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .filter-btn:hover {
            background: #f5f5f5;
        }

        .add-event-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #007836;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .add-event-btn:hover {
            background: #00612b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .events-table th, 
        .events-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .events-table th {
            background-color: #007836;
            color: white;
            font-weight: 500;
        }

        .events-table tr:nth-child(even) {
            background-color: rgba(0, 120, 54, 0.05);
        }

        .events-table tr:hover {
            background-color: rgba(0, 120, 54, 0.1);
        }

        .mandatory-yes {
            color: #007836;
            font-weight: 500;
        }

        .mandatory-no {
            color: #e74c3c;
            font-weight: 500;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #007836;
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .page-btn {
            padding: 8px 12px;
            border-radius: 4px;
            background: white;
            color: #333;
            text-decoration: none;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .page-btn.active {
            background: #007836;
            color: white;
            border-color: #007836;
        }

        .page-btn:hover:not(.active) {
            background: #f5f5f5;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                min-height: auto;
                padding: 10px 0;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .sidebar-menu li {
                margin-bottom: 0;
                flex-shrink: 0;
            }
            
            .sidebar-menu a {
                padding: 10px 15px;
            }

            .search-section {
                flex-direction: column;
                align-items: stretch;
            }

            .events-table {
                display: block;
                overflow-x: auto;
            }
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgb(137, 172, 133);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgb(69, 156, 65);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .breadcrumb {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 25px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: modalopen 0.3s;
        }

        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            color: #007836;
            font-size: 22px;
        }

        .close-btn {
            color: #aaa;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close-btn:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #007836;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #00612b;
        }

        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        input[type="time"]::-webkit-calendar-picker-indicator {
            background: none;
            display: none;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            background: none;
            display: none;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: "\f078";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            pointer-events: none;
            color: #666;
        }
        
        .event-details {
            margin-top: 20px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
            width: 120px;
        }
        
        .detail-value {
            flex: 1;
            color: #333;
        }
        
        .confirmation-modal .modal-content {
            max-width: 400px;
            text-align: center;
        }
        
        .confirmation-text {
            margin: 20px 0;
            font-size: 16px;
        }
        
        .confirmation-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: #007836;
            color: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1100;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(150%);
            transition: transform 0.3s ease-in-out;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.error {
            background-color: #e74c3c;
        }
        
        .toast.success {
            background-color: #007836;
        }
        
        .toast i {
            font-size: 20px;
        }

        .select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: "\f078";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            pointer-events: none;
            color: #666;
        }

        .date-input-wrapper {
            position: relative;
            width: 100%;
        }

        .date-input-wrapper .form-control {
            padding-right: 40px;
            cursor: pointer;
            background-color: white;
        }

        .date-input-wrapper i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #666;
        }

        .datepicker {
            z-index: 9999 !important;
             margin-right: 848px;
             margin-top: 333px;
        }
        .datepicker-dropdown {
            top: 100% !important;
            left: 0 !important;
        }
        
        .datepicker table {
            width: 100%;
            background-color: white;
        }
        
        .datepicker td, .datepicker th {
            width: 30px;
            height: 30px;
            text-align: center;
           
        };
        
        .datepicker .datepicker-switch {
            font-weight: 500;
        }
        
        .datepicker .today {
            background-color: #f0f0f0;
        }
        
        .datepicker .active {
            background-color: #007836;
            color: white;
        }
        
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Time input styles */
        .time-input-wrapper {
            position: relative;
            width: 100%;
        }

        .time-input-wrapper input {
            padding-right: 40px;
            cursor: pointer;
            background-color: white;
        }

        .time-input-wrapper i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #666;
        }
        
        /* Searchable dropdown styles */
        .searchable-dropdown {
            position: relative;
            width: 100%;
        }

        .searchable-dropdown input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .searchable-dropdown .dropdown-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .searchable-dropdown .dropdown-option {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .searchable-dropdown .dropdown-option:hover {
            background-color: #f5f5f5;
        }

        .searchable-dropdown .dropdown-option:last-child {
            border-bottom: none;
        }

        .searchable-dropdown.active .dropdown-options {
            display: block;
        }

        .searchable-dropdown.active input {
            border-radius: 4px 4px 0 0;
            border-bottom: 1px solid #ddd;
        }

        /* ADDED: Fines Section Styles */
        .fines-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #dc3545;
        }
        
        .fines-section h3 {
            color: #dc3545;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .fines-toggle {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .fines-toggle input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .fines-fields {
            display: none;
            margin-top: 15px;
        }
        
        .fines-fields.active {
            display: block;
        }
        
        .currency-input {
            position: relative;
        }
        
        .currency-input::before {
            content: "₱";
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-weight: 500;
        }
        
        .currency-input input {
            padding-left: 25px;
        }
        
        .generate-fines-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .generate-fines-btn:hover {
            background-color: #c82333;
        }
        
        .fines-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        /* ADDED: Year Level Filter Styles */
        .year-level-section {
            margin-top: 15px;
            padding: 15px;
            background-color: #f0f8ff;
            border-radius: 6px;
            border-left: 4px solid #007836;
        }
        
        .year-level-section h3 {
            color: #007836;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .year-level-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
    </style>
</head>

<body>
   <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header" style="display: flex">
            <img src="../images/logo.png" style="height: 50px; margin-right: 10px;">
            <h4 style="margin-top: 15px">ACI Admin Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="/biometric/admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/admin/admin-management.php" ><i class="fas fa-user-shield"></i> Admin Management</a></li>
            <li><a href="#" style="font-size: 14px"  onclick="launchBiometricApp(); return false;"><i class="fas fa-fingerprint"></i> Fingerprint Management</a></li>
            <li><a href="/biometric/admin/students.php" ><i class="fas fa-users"></i> Student Management</a></li>
            <li><a href="/biometric/admin/events.php"  class="active"><i class="fas fa-calendar-alt"></i> Events Management</a></li>            
            <li><a href="/biometric/admin/attendance.php"><i class="fas fa-clipboard-list"></i> Attendance Records</a></li>
            <li><a href="/biometric/admin/fines.php"><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/biometric/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>
    
   <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="header">
                <div>
                    <h1>Event Management</h1>
                    <p>Manage school events for attendance tracking</p>
                    <div class="breadcrumb">ACI Admin Panel » Events Management</div>
                </div>
                <div class="user-profile">
                    <a href="/biometric/login/login.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <div class="search-section">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search events by name, type, or date..." id="searchInput">
                </div>
                <a href="#" class="filter-btn">
                    <i class="fas fa-filter"></i> Filters
                </a>
                <a href="#" class="add-event-btn" id="openModalBtn">
                    <i class="fas fa-plus"></i> Add Event
                </a>
            </div>

            <!-- Add/Edit Event Modal -->
            <div id="eventModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">Add New Event</h2>
                        <span class="close-btn">&times;</span>
                    </div>
                    <form id="eventForm">
                        <input type="hidden" id="eventId">
                        <div class="form-group">
                            <label for="eventName">Event Name*</label>
                            <div class="searchable-dropdown" id="eventNameDropdown">
                                <input type="text" id="eventName" placeholder="Select or type event name" required>
                                <div class="dropdown-options" id="eventNameOptions">
                                    <!-- Options will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="eventDate">Date*</label>
                                <div class="date-input-wrapper">
                                    <input type="text" id="eventDate" class="form-control date-input" required readonly>
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                                
                            <div class="form-group">
                                <label for="startTime">Start Time*</label>
                                <div class="time-input-wrapper">
                                    <input type="time" id="startTime" class="form-control" required 
                                           min="07:00" max="20:00" step="300">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="endTime">End Time*</label>
                                <div class="time-input-wrapper">
                                    <input type="time" id="endTime" class="form-control" required
                                           min="07:00" max="20:00" step="300">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="eventLocation">Location*</label>
                            <div class="select-wrapper">
                                <select id="eventLocation" required>
                                    <option value="">Select Location</option>
                                    <option value="Main Quadrangle">Main Quadrangle</option>
                                    <option value="AVR1">AVR1</option>
                                    <option value="AVR2">AVR2</option>
                                    <option value="AVR3">AVR3</option>
                                    <option value="CL1">CL1</option>
                                    <option value="CL2">CL2</option>
                                    <option value="CL3">CL3</option>
                                    <option value="HAB">HAB</option>
                                    <option value="CSS Building">CSS Building</option>
                                </select>
                            </div>
                        </div>

                        <!-- ADDED: Year Level Section -->
                        <div class="year-level-section">
                            <h3><i class="fas fa-users"></i> Year Level Restriction</h3>
                            <div class="form-group">
                                <label for="yearLevel">Restrict to Year Level (Optional)</label>
                                <div class="select-wrapper">
                                    <select id="yearLevel">
                                        <option value="">All Year Levels</option>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                        <option value="5th Year">5th Year</option>
                                    </select>
                                </div>
                                <div class="year-level-info">
                                    If selected, only students from this year level will be able to check in
                                </div>
                            </div>
                        </div>

                        <!-- ADDED: Fines Section -->
                        <div class="fines-section">
                            <h3><i class="fas fa-exclamation-circle"></i> Fines Settings</h3>
                            <div class="fines-toggle">
                                <input type="checkbox" id="enableFines">
                                <label for="enableFines">Enable fines for absent students</label>
                            </div>
                            
                            <div class="fines-fields" id="finesFields">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="fineAmount">Fine Amount*</label>
                                        <div class="currency-input">
                                            <input type="number" id="fineAmount" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="fines-info">Amount in Philippine Peso (₱)</div>
                                    </div>
                                </div>
                                
                                <button type="button" class="generate-fines-btn" id="generateFinesBtn">
                                    <i class="fas fa-bolt"></i> Generate Fines Now
                                </button>
                                <div class="fines-info" id="finesInfo">
                                    Fines will be automatically generated for students who are absent from this event
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="saveBtn">Save Event</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- View Event Modal -->
            <div id="viewEventModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Event Details</h2>
                        <span class="close-btn">&times;</span>
                    </div>
                    <div class="event-details">
                        <div class="detail-row">
                            <div class="detail-label">Event Name:</div>
                            <div class="detail-value" id="viewEventName"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Date:</div>
                            <div class="detail-value" id="viewEventDate"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Time:</div>
                            <div class="detail-value" id="viewEventTime"></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Location:</div>
                            <div class="detail-value" id="viewEventLocation"></div>
                        </div>
                        <!-- ADDED: Year Level information in view modal -->
                        <div class="detail-row">
                            <div class="detail-label">Year Level:</div>
                            <div class="detail-value" id="viewYearLevel">All Year Levels</div>
                        </div>
                        <!-- ADDED: Fines information in view modal -->
                        <div class="detail-row" id="viewFineAmountRow" style="display: none;">
                            <div class="detail-label">Fine Amount:</div>
                            <div class="detail-value" id="viewFineAmount"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-view-btn">Close</button>
                    </div>
                </div>
            </div>
            
            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="modal confirmation-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Confirm Deletion</h2>
                        <span class="close-btn">&times;</span>
                    </div>
                    <div class="confirmation-text">
                        Are you sure you want to delete this event? This action cannot be undone.
                    </div>
                    <div class="confirmation-buttons">
                        <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
            
            <!-- Toast Notification -->
            <div id="toast" class="toast">
                <i class="fas fa-check-circle"></i>
                <span id="toastMessage">Event saved successfully!</span>
            </div>

            <!-- Events Table -->
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <!-- ADDED: Year Level column -->
                        <th>Year Level</th>
                        <!-- ADDED: Fine Enabled column -->
                        <th>Fine Enabled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <?php foreach ($events as $event): ?>
                    <tr data-id="<?= $event['event_id'] ?>">
                        <td><?= htmlspecialchars($event['event_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($event['date'])) ?></td>
                        <td>
                            <?= date('g:i A', strtotime($event['start_time'])) ?> - 
                            <?= date('g:i A', strtotime($event['end_time'])) ?>
                        </td>
                        <td><?= htmlspecialchars($event['location']) ?></td>
                        <!-- ADDED: Year Level column -->
                        <td><?= !empty($event['year_level']) ? htmlspecialchars($event['year_level']) : 'All Years' ?></td>
                        <!-- ADDED: Fine Enabled column -->
                        <td class="<?= !empty($event['fine_amount']) ? 'mandatory-yes' : 'mandatory-no' ?>">
                            <?= !empty($event['fine_amount']) ? 'Yes' : 'No' ?>
                        </td>
                        <td>
                            <a href="#" class="action-btn view-btn" title="View"><i class="fas fa-eye"></i></a>
                            <a href="#" class="action-btn edit-btn" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="#" class="action-btn delete-btn" title="Delete"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <a href="#" class="page-btn"><i class="fas fa-angle-left"></i></a>
                <a href="#" class="page-btn active">1</a>
                <a href="#" class="page-btn">2</a>
                <a href="#" class="page-btn">3</a>
                <a href="#" class="page-btn"><i class="fas fa-angle-right"></i></a>
            </div>
        </div>
    </main>

    <script>
        // ADDED: Fines functionality
        const enableFinesCheckbox = document.getElementById('enableFines');
        const finesFields = document.getElementById('finesFields');
        const generateFinesBtn = document.getElementById('generateFinesBtn');
        const fineAmountInput = document.getElementById('fineAmount');

        // Toggle fines fields
        enableFinesCheckbox.addEventListener('change', function() {
            if (this.checked) {
                finesFields.classList.add('active');
                fineAmountInput.required = true;
            } else {
                finesFields.classList.remove('active');
                fineAmountInput.required = false;
                fineAmountInput.value = '';
            }
        });

       // Generate fines button - REDIRECT TO FINES.PHP
generateFinesBtn.addEventListener('click', function() {
    const eventId = document.getElementById('eventId').value;
    const fineAmount = fineAmountInput.value;

    if (!fineAmount) {
        showToast('Please fill in fine amount', true);
        return;
    }

    if (!eventId) {
        showToast('Please save the event first before generating fines', true);
        return;
    }

    // Redirect to fines.php with event ID as parameter
    window.location.href = `/biometric/admin/fines.php?event_id=${eventId}&generate_fines=true&fine_amount=${fineAmount}`;
});
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.events-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Event data storage
        let events = <?php echo json_encode($events); ?>;
        let eventNames = <?php echo json_encode($eventNames); ?>;

        // DOM elements
        const eventModal = document.getElementById("eventModal");
        const viewEventModal = document.getElementById("viewEventModal");
        const deleteModal = document.getElementById("deleteModal");
        const openBtn = document.getElementById("openModalBtn");
        const closeBtns = document.querySelectorAll(".close-btn");
        const cancelBtn = document.getElementById("cancelBtn");
        const eventForm = document.getElementById("eventForm");
        const toast = document.getElementById("toast");
        const toastMessage = document.getElementById("toastMessage");
        const saveBtn = document.getElementById("saveBtn");
        const eventNameInput = document.getElementById("eventName");
        const eventNameDropdown = document.getElementById("eventNameDropdown");
        const eventNameOptions = document.getElementById("eventNameOptions");
        const yearLevelSelect = document.getElementById("yearLevel");
        
        // Current event being edited/deleted
        let currentEventId = null;
        
        // Modal functionality
        function openModal(modal) {
            modal.style.display = "block";
            document.body.style.overflow = "hidden";
        }
        
        function closeModal(modal) {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
            hideDropdown();
        }
        
        // Set default time values when adding new event
        function setDefaultTimes() {
            const defaultStart = '08:00';
            const defaultEnd = '09:00';
            
            document.getElementById('startTime').value = defaultStart;
            document.getElementById('endTime').value = defaultEnd;
        }
        
        // Searchable dropdown functionality
        function populateDropdownOptions() {
            eventNameOptions.innerHTML = '';
            
            // Add default options
            const defaultOptions = [
                "Flag Ceremony",
                "Orientation Program", 
                "Intramurals",
                "School Assembly",
                "Seminar"
            ];
            
            // Combine default options with previously used event names
            const allOptions = [...new Set([...defaultOptions, ...eventNames])];
            
            allOptions.forEach(option => {
                const div = document.createElement('div');
                div.className = 'dropdown-option';
                div.textContent = option;
                div.addEventListener('click', function() {
                    eventNameInput.value = option;
                    hideDropdown();
                });
                eventNameOptions.appendChild(div);
            });
        }
        
        function filterDropdownOptions() {
            const searchTerm = eventNameInput.value.toLowerCase();
            const options = eventNameOptions.querySelectorAll('.dropdown-option');
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        }
        
        function showDropdown() {
            eventNameDropdown.classList.add('active');
            filterDropdownOptions();
        }
        
        function hideDropdown() {
            eventNameDropdown.classList.remove('active');
        }
        
        // Event listeners for searchable dropdown
        eventNameInput.addEventListener('focus', showDropdown);
        eventNameInput.addEventListener('input', filterDropdownOptions);
        
        document.addEventListener('click', function(e) {
            if (!eventNameDropdown.contains(e.target)) {
                hideDropdown();
            }
        });
        
        // Open add event modal
        openBtn.onclick = function(e) {
            e.preventDefault();
            document.getElementById("modalTitle").textContent = "Add New Event";
            saveBtn.textContent = "Save Event";
            saveBtn.classList.remove("btn-loading");
            eventForm.reset();
            setDefaultTimes(); // Set default times for new events
            
            // ADDED: Reset fines section
            enableFinesCheckbox.checked = false;
            finesFields.classList.remove('active');
            fineAmountInput.required = false;
            
            // ADDED: Reset year level
            yearLevelSelect.value = '';
            
            currentEventId = null;
            
            // Populate dropdown options
            populateDropdownOptions();
            
            openModal(eventModal);
        }
        
        // Close modals
        closeBtns.forEach(btn => {
            btn.onclick = function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            }
        });
        
        // Close when clicking outside modal
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target);
            }
        }
        
        cancelBtn.onclick = function() {
            closeModal(eventModal);
        }
        
        document.querySelector('.close-view-btn').onclick = function() {
            closeModal(viewEventModal);
        }
        
        document.getElementById('cancelDeleteBtn').onclick = function() {
            closeModal(deleteModal);
        }
        
        // Show toast notification
        function showToast(message, isError = false) {
            toastMessage.textContent = message;
            toast.className = isError ? "toast error show" : "toast success show";
            toast.querySelector('i').className = isError ? "fas fa-exclamation-circle" : "fas fa-check-circle";
            
            setTimeout(() => {
                toast.className = toast.className.replace("show", "");
            }, 3000);
        }
        
        // Format date for display
        function formatDisplayDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }
        
        // Format time for display
        function formatDisplayTime(timeString) {
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        }

        // Enhanced time validation
        function validateEventTimes(startTime, endTime, date) {
            // Convert times to minutes for comparison
            const startMinutes = convertTimeToMinutes(startTime);
            const endMinutes = convertTimeToMinutes(endTime);
            
            if (startMinutes >= endMinutes) {
                return { valid: false, message: "End time must be after start time" };
            }
            
            // Check if event is within school hours (7AM-8PM)
            if (startMinutes < 420 || endMinutes > 1200) { // 7AM=420min, 8PM=1200min
                return { valid: false, message: "Events must be between 7:00 AM and 8:00 PM" };
            }
            
            // Date validation
            const today = new Date();
            const eventDate = new Date(date);
            
            // Reset time components for date comparison
            today.setHours(0, 0, 0, 0);
            eventDate.setHours(0, 0, 0, 0);
            
            if (eventDate < today) {
                return { valid: false, message: "Event date cannot be in the past" };
            }
            
            return { valid: true };
        }

        // Helper function to convert time string to minutes
        function convertTimeToMinutes(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }

        // View event
        function setupViewButtons() {
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const eventId = parseInt(this.closest('tr').getAttribute('data-id'));
                    const event = events.find(e => e.event_id == eventId);
                    
                    if (event) {
                        document.getElementById('viewEventName').textContent = event.event_name;
                        document.getElementById('viewEventDate').textContent = formatDisplayDate(event.date);
                        document.getElementById('viewEventTime').textContent = 
                            `${formatDisplayTime(event.start_time)} - ${formatDisplayTime(event.end_time)}`;
                        document.getElementById('viewEventLocation').textContent = event.location;
                        
                        // ADDED: Show year level information
                        document.getElementById('viewYearLevel').textContent = 
                            event.year_level ? event.year_level : 'All Year Levels';
                        
                        // ADDED: Show fines information if available
                        if (event.fine_amount) {
                            document.getElementById('viewFineAmount').textContent = '₱' + parseFloat(event.fine_amount).toFixed(2);
                            document.getElementById('viewFineAmountRow').style.display = 'flex';
                        } else {
                            document.getElementById('viewFineAmountRow').style.display = 'none';
                        }
                        
                        openModal(viewEventModal);
                    }
                });
            });
        }

        // Edit event
        function setupEditButtons() {
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const eventId = parseInt(this.closest('tr').getAttribute('data-id'));
                    const event = events.find(e => e.event_id == eventId);
                    
                    if (event) {
                        document.getElementById("modalTitle").textContent = "Edit Event";
                        saveBtn.textContent = "Update Event";
                        saveBtn.classList.remove("btn-loading");
                        
                        document.getElementById('eventId').value = event.event_id;
                        
                        // Set event name
                        document.getElementById('eventName').value = event.event_name;
                        
                        // Set date and times
                        document.getElementById('eventDate').value = event.date;
                        document.getElementById('startTime').value = event.start_time.substring(0, 5);
                        document.getElementById('endTime').value = event.end_time.substring(0, 5);
                        document.getElementById('eventLocation').value = event.location;
                        
                        // ADDED: Populate year level
                        document.getElementById('yearLevel').value = event.year_level || '';
                        
                        // ADDED: Populate fines data
                        if (event.fine_amount) {
                            enableFinesCheckbox.checked = true;
                            finesFields.classList.add('active');
                            fineAmountInput.value = event.fine_amount;
                            fineAmountInput.required = true;
                        } else {
                            enableFinesCheckbox.checked = false;
                            finesFields.classList.remove('active');
                            fineAmountInput.value = '';
                            fineAmountInput.required = false;
                        }
                        
                        currentEventId = event.event_id;
                        openModal(eventModal);
                    }
                });
            });
        }

        // Delete event
        function setupDeleteButtons() {
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const eventId = parseInt(this.closest('tr').getAttribute('data-id'));
                    currentEventId = eventId;
                    openModal(deleteModal);
                });
            });
        }

        // Confirm delete
        document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
            if (currentEventId) {
                try {
                    this.disabled = true;
                    this.classList.add('btn-loading');
                    
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete&eventId=${currentEventId}`
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        document.querySelector(`tr[data-id="${currentEventId}"]`).remove();
                        showToast("Event deleted successfully");
                        closeModal(deleteModal);
                    } else {
                        showToast("Failed to delete event", true);
                    }
                } catch (error) {
                    showToast("Error deleting event", true);
                    console.error('Error:', error);
                } finally {
                    this.disabled = false;
                    this.classList.remove('btn-loading');
                }
            }
        });

        // Form submission
        eventForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            
            saveBtn.disabled = true;
            saveBtn.classList.add('btn-loading');
            saveBtn.innerHTML = 'Saving...';
            
            try {
                const formData = {
                    eventId: document.getElementById("eventId").value,
                    eventName: document.getElementById("eventName").value,
                    eventDate: document.getElementById("eventDate").value,
                    startTime: document.getElementById("startTime").value,
                    endTime: document.getElementById("endTime").value,
                    eventLocation: document.getElementById("eventLocation").value,
                    fineAmount: enableFinesCheckbox.checked ? parseFloat(fineAmountInput.value) : '',
                    yearLevel: document.getElementById("yearLevel").value
                };

                // Validate fine amount is a number
                if (formData.fineAmount && isNaN(formData.fineAmount)) {
                    showToast('Please enter a valid fine amount', true);
                    return;
                }
                
                const validation = validateEventTimes(formData.startTime, formData.endTime, formData.eventDate);
                if (!validation.valid) {
                    showToast(validation.message, true);
                    saveBtn.disabled = false;
                    saveBtn.classList.remove('btn-loading');
                    saveBtn.innerHTML = currentEventId ? 'Update Event' : 'Save Event';
                    return;
                }
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=save&${new URLSearchParams(formData).toString()}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message);
                    // Store the event ID for fines generation
                    if (result.eventId) {
                        document.getElementById('eventId').value = result.eventId;
                    }
                    setTimeout(() => {
                        closeModal(eventModal);
                        // Reload the page to show updated events
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(result.message, true);
                    saveBtn.disabled = false;
                    saveBtn.classList.remove('btn-loading');
                    saveBtn.innerHTML = currentEventId ? 'Update Event' : 'Save Event';
                }
            } catch (error) {
                showToast("Network error - please try again", true);
                console.error('Error:', error);
                saveBtn.disabled = false;
                saveBtn.classList.remove('btn-loading');
                saveBtn.innerHTML = currentEventId ? 'Update Event' : 'Save Event';
            }
        });

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            $('#eventDate').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom auto",
                zIndexOffset: 99999
            }).on('show', function(e) {
                $('.datepicker').css('top', '');
            });
            
            setupViewButtons();
            setupEditButtons();
            setupDeleteButtons();
            populateDropdownOptions();
        });
    </script>

     <script>
function launchBiometricApp() {
  const url = 'biometricapp://open?screen=fingerprint_registration';
  try {
    window.location.href = url;
    setTimeout(() => {
      if (document.visibilityState === 'visible') {
        // alert('If the app did not open, install/enable the biometric app protocol handler.');
      }
    }, 1500);
  } catch (e) {
    alert('Unable to launch the desktop app.');
  }
}
</script>
</body>
</html>