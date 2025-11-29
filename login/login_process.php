<?php
session_start();
require_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        $response = ['status' => 'error', 'message' => 'Please enter both username and password'];
        sendResponse($response, 400);
        exit();
    }

    try {
        // Prepare SQL statement to prevent SQL injection using PDO
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // DEBUG: Check what's in the database
            error_log("Admin found: " . print_r($admin, true));
            error_log("Input password: " . $password);
            error_log("Stored password: " . $admin['password']);
            
            // TEMPORARY: Direct password comparison (remove hashing for debugging)
            if ($password === $admin['password']) {
            // if (password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role'] = $admin['role'];
                $_SESSION['full_name'] = $admin['full_name'];
                
                $response = ['status' => 'success', 'message' => 'Login successful'];
                sendResponse($response, 200);
                exit();
            } else {
                // Invalid password
                $response = ['status' => 'error', 'message' => 'Invalid username or password'];
                sendResponse($response, 401);
                exit();
            }
        } else {
            // Admin doesn't exist
            $response = ['status' => 'error', 'message' => 'Admin account not found'];
            sendResponse($response, 404);
            exit();
        }
    } catch (Exception $e) {
        // Database error
        error_log("Database error: " . $e->getMessage());
        $response = ['status' => 'error', 'message' => 'Database error occurred'];
        sendResponse($response, 500);
        exit();
    }
} else {
    // Not a POST request
    $response = ['status' => 'error', 'message' => 'Invalid request method'];
    sendResponse($response, 405);
    exit();
}

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?>