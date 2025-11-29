<!-- < ?php
function admin_login($username, $password) {
    $conn = new mysqli("localhost", "root", "", "your_db_name");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password); // Use password hashing in production!
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Login success
        return true;
    } else {
        // Login failed
        return false;
    }
}
?> -->
<?php
// functions.php
session_start();

/**
 * Database Connection Function
 */
function getDBConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "biometric";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Admin Authentication Functions
 */
function admin_login($username, $password) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && $password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['admin_logged_in'] = true;
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Admin login error: " . $e->getMessage());
        return false;
    }
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_logout() {
    session_destroy();
    header('Location: /biometric/login/login.php');
    exit;
}

/**
 * Student Management Functions
 */
function register_student($uid, $student_name, $course, $year_level, $fingerprint_data, $password = '0000') {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        // Check if UID already exists
        $check_stmt = $conn->prepare("SELECT id FROM register_student WHERE uid = ?");
        $check_stmt->execute([$uid]);
        
        if ($check_stmt->fetch()) {
            throw new Exception("UID already exists");
        }
        
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO register_student (uid, student_name, course, year_level, fingerprint_data, password) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$uid, $student_name, $course, $year_level, $fingerprint_data, $password]);
        
        return $result;
    } catch(PDOException $e) {
        error_log("Student registration error: " . $e->getMessage());
        return false;
    }
}

function get_all_students() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM register_student ORDER BY student_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get students error: " . $e->getMessage());
        return [];
    }
}

function get_student_by_id($student_id) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM register_student WHERE id = ?");
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get student error: " . $e->getMessage());
        return null;
    }
}

/**
 * Event Management Functions
 */
function create_event($event_name, $date, $start_time, $end_time, $fine_amount = 0) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("INSERT INTO admin_event (event_name, date, start_time, end_time, fine_amount) 
                              VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$event_name, $date, $start_time, $end_time, $fine_amount]);
        return $result;
    } catch(PDOException $e) {
        error_log("Create event error: " . $e->getMessage());
        return false;
    }
}

function get_all_events() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM admin_event ORDER BY date DESC, start_time DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get events error: " . $e->getMessage());
        return [];
    }
}

/**
 * Attendance Functions
 */
function record_attendance($student_id, $event_id, $attendance_status, $time_in = null, $time_out = null) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        $date_recorded = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO students_events (student_id, event_id, attendance_status, time_in, time_out, date_recorded) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$student_id, $event_id, $attendance_status, $time_in, $time_out, $date_recorded]);
        
        // If absent or late, automatically generate fine
        if ($result && in_array($attendance_status, ['absent', 'late'])) {
            generate_fine_for_attendance($conn->lastInsertId());
        }
        
        return $result;
    } catch(PDOException $e) {
        error_log("Record attendance error: " . $e->getMessage());
        return false;
    }
}

function get_recent_checkins($limit = 10) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $stmt = $conn->prepare("
            SELECT se.*, s.uid, s.student_name, e.event_name 
            FROM students_events se 
            LEFT JOIN register_student s ON se.student_id = s.id 
            LEFT JOIN admin_event e ON se.event_id = e.event_id 
            WHERE se.attendance_status IN ('present', 'late')
            ORDER BY se.date_recorded DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get recent checkins error: " . $e->getMessage());
        return [];
    }
}

function get_todays_checkins() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $today = date('Y-m-d');
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM students_events 
            WHERE DATE(date_recorded) = ? AND attendance_status IN ('present', 'late')
        ");
        $stmt->execute([$today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Get today's checkins error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Fines Management Functions
 */
function generate_fine_for_attendance($attendance_id) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    try {
        // Get attendance record with event fine amount
        $stmt = $conn->prepare("
            SELECT se.*, e.fine_amount, se.student_id, se.event_id
            FROM students_events se
            LEFT JOIN admin_event e ON se.event_id = e.event_id
            WHERE se.id = ?
        ");
        $stmt->execute([$attendance_id]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$attendance) return false;
        
        // Check if fine already exists
        $check_stmt = $conn->prepare("SELECT fine_id FROM admin_fines WHERE attendance_id = ?");
        $check_stmt->execute([$attendance_id]);
        
        if ($check_stmt->fetch()) {
            return true; // Fine already exists
        }
        
        // Determine fine type and amount
        $fine_type = $attendance['attendance_status'];
        $fine_amount = $attendance['fine_amount'] ? $attendance['fine_amount'] : 50.00; // Default amount
        
        // Insert fine
        $fine_stmt = $conn->prepare("
            INSERT INTO admin_fines (student_id, event_id, attendance_id, fine_type, amount, status, date_issued) 
            VALUES (?, ?, ?, ?, ?, 'unpaid', NOW())
        ");
        $result = $fine_stmt->execute([
            $attendance['student_id'], 
            $attendance['event_id'], 
            $attendance_id, 
            $fine_type, 
            $fine_amount
        ]);
        
        return $result;
    } catch(PDOException $e) {
        error_log("Generate fine error: " . $e->getMessage());
        return false;
    }
}

function generate_all_fines_for_absences() {
    $conn = getDBConnection();
    if (!$conn) return ['success' => false, 'count' => 0];
    
    try {
        // Get all absent/late records without fines
        $stmt = $conn->prepare("
            SELECT se.id 
            FROM students_events se
            LEFT JOIN admin_fines f ON se.id = f.attendance_id
            WHERE se.attendance_status IN ('absent', 'late') 
            AND f.fine_id IS NULL
        ");
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        foreach ($records as $record) {
            if (generate_fine_for_attendance($record['id'])) {
                $count++;
            }
        }
        
        return ['success' => true, 'count' => $count];
    } catch(PDOException $e) {
        error_log("Generate all fines error: " . $e->getMessage());
        return ['success' => false, 'count' => 0, 'message' => $e->getMessage()];
    }
}

function get_pending_fines_count() {
    $conn = getDBConnection();
    if (!$conn) return 0;
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM admin_fines WHERE status = 'unpaid'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Get pending fines error: " . $e->getMessage());
        return 0;
    }
}

function get_all_fines() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $stmt = $conn->prepare("
            SELECT f.*, s.uid, s.student_name, s.course, e.event_name, e.date as event_date,
                   se.attendance_status, se.date_recorded
            FROM admin_fines f
            LEFT JOIN register_student s ON f.student_id = s.id
            LEFT JOIN admin_event e ON f.event_id = e.event_id
            LEFT JOIN students_events se ON f.attendance_id = se.id
            ORDER BY f.date_issued DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get fines error: " . $e->getMessage());
        return [];
    }
}

/**
 * Dashboard Statistics Functions
 */
function get_dashboard_stats() {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    try {
        $stats = [];
        
        // Total registered students
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM register_student");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_students'] = $result['count'] ?? 0;
        
        // Today's check-ins
        $stats['today_checkins'] = get_todays_checkins();
        
        // Pending fines
        $stats['pending_fines'] = get_pending_fines_count();
        
        return $stats;
    } catch(PDOException $e) {
        error_log("Get dashboard stats error: " . $e->getMessage());
        return ['total_students' => 0, 'today_checkins' => 0, 'pending_fines' => 0];
    }
}

/**
 * Utility Functions
 */
function send_json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function format_date($date_string, $format = 'M j, Y g:i A') {
    return date($format, strtotime($date_string));
}

/**
 * Authentication Middleware
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: /biometric/login/login.php');
        exit;
    }
}

function require_student_login() {
    if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
        header('Location: /biometric/login/login.php');
        exit;
    }
}
?>