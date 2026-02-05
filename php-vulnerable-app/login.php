<?php
/**
 * Authentication bypass and session vulnerabilities
 */

// VULNERABILITY: Sessions not properly secured
session_start();
ini_set('display_errors', 1);

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// VULNERABILITY: Hardcoded credentials
$admin_user = 'admin';
$admin_pass = 'admin123';

if ($username === $admin_user && $password === $admin_pass) {
    // VULNERABILITY: Session fixation - no regeneration
    $_SESSION['authenticated'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    
    echo "Login successful!";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    // VULNERABILITY: Information disclosure
    if ($username === $admin_user) {
        echo "Username correct, password wrong!";
    } else {
        echo "Invalid credentials";
    }
}

// VULNERABILITY: No logout functionality with proper cleanup
if (isset($_GET['logout'])) {
    $_SESSION = [];
    // VULNERABILITY: Session cookie not properly destroyed
    setcookie(session_name(), '');
}

// VULNERABILITY: Weak authentication check
function is_authenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

// Display session data
if (is_authenticated()) {
    echo "<h2>Authenticated User</h2>";
    echo "Username: " . $_SESSION['username'] . "<br>";
    echo "Role: " . $_SESSION['role'] . "<br>";
}

?>
