<?php
session_start();
require_once '../connection.php';

// Enable error logging for debugging
error_log("Registration attempt - " . date('Y-m-d H:i:s'));
error_log("POST data: " . print_r($_POST, true));

header('Content-Type: application/json');

// Check if connection was established properly
if (!isset($conn) || !($conn instanceof PDO)) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['fullName'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $role = $_POST['role'] ?? '';
    $agreeTerms = $_POST['agreeTerms'] ?? '0';

    // Validation
    if (empty($fullName) || empty($username) || empty($password) || empty($confirmPassword) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        exit;
    }

    if ($agreeTerms !== '1') {
        echo json_encode(['success' => false, 'message' => 'You must agree to the terms and conditions']);
        exit;
    }

    try {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }

        // Check if email already exists (if provided)
        if (!empty($email)) {
            $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                exit;
            }
        }

        // Insert new admin - NO PASSWORD HASHING (store plain text)
        $stmt = $conn->prepare("INSERT INTO admin (full_name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $username, $email, $password, $role]); // Password stored as plain text

        $adminId = $conn->lastInsertId();
        error_log("Successfully inserted admin with ID: " . $adminId . " - Password stored in plain text: " . $password);
        
        echo json_encode(['success' => true, 'message' => 'Admin account created successfully']);
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating account: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}


?>