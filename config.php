<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'classroom_voting');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Helper function to format student name
function formatStudentName($firstName, $middleName, $lastName) {
    $nameParts = array_filter([
        trim($lastName),
        trim($firstName),
        trim($middleName)
    ]);
    
    if (empty($nameParts)) {
        return 'No Name';
    }
    
    // Format: Last Name, First Name Middle Name
    if (!empty($lastName)) {
        $formatted = $lastName;
        if (!empty($firstName) || !empty($middleName)) {
            $formatted .= ', ';
            if (!empty($firstName)) {
                $formatted .= $firstName;
            }
            if (!empty($middleName)) {
                $formatted .= ' ' . $middleName;
            }
        }
        return $formatted;
    }
    
    // If no last name, just concatenate first and middle
    return trim($firstName . ' ' . $middleName);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: student_dashboard.php');
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result(); 
    $user = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $user;
}
?>