<?php
/**
 * PHP Vulnerable Application
 * This application intentionally contains security vulnerabilities for SAST testing
 * DO NOT USE IN PRODUCTION
 */

// Database connection - vulnerable to credential exposure
$db_host = "localhost";
$db_user = "root";
$db_pass = "password123";
$db_name = "vulnerable_app";

// Simple flag to simulate database connection
$db_connected = true;

// Error display - dangerous in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session without security flags
session_start();

// Get request parameters
$action = $_REQUEST['action'] ?? 'login';
$username = $_REQUEST['username'] ?? '';
$password = $_REQUEST['password'] ?? '';
$user_id = $_REQUEST['user_id'] ?? '';
$search = $_REQUEST['search'] ?? '';
$file = $_REQUEST['file'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Vulnerable PHP Application</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form { background: #f5f5f5; padding: 20px; border-radius: 5px; }
        input, textarea { padding: 8px; margin: 5px 0; width: 100%; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .alert { padding: 10px; margin: 10px 0; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔓 Vulnerable PHP Application</h1>
        <p><em>This application intentionally contains security vulnerabilities for testing purposes</em></p>

        <?php
        // VULNERABILITY 1: SQL INJECTION
        if ($action === 'login') {
            echo "<h2>Login</h2>";
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Vulnerable SQL query - directly concatenates user input
                $sql = "SELECT * FROM users WHERE username = '" . $username . "' AND password = '" . md5($password) . "'";
                echo "<div class='alert alert-danger'>SQL Query: " . htmlspecialchars($sql) . "</div>";
                echo "<div class='alert alert-success'>Login successful (simulated)</div>";
            }
            ?>
            <form method="POST" class="form">
                <input type="hidden" name="action" value="login">
                <h3>SQL Injection Vulnerable Form</h3>
                <input type="text" name="username" placeholder="Username (Try: admin' OR '1'='1)" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <?php
        }

        // VULNERABILITY 2: XSS (Cross-Site Scripting)
        if ($action === 'search') {
            echo "<h2>Search</h2>";
            ?>
            <form method="GET" class="form">
                <input type="hidden" name="action" value="search">
                <h3>XSS Vulnerable Search</h3>
                <input type="text" name="search" placeholder="Search term (Try: <img src=x onerror=alert('XSS')>)" value="<?php echo $_GET['search'] ?? ''; ?>">
                <button type="submit">Search</button>
            </form>
            <?php
            if ($search) {
                // VULNERABILITY: Directly echoing user input without escaping
                echo "<div class='alert alert-danger'>Search Results:</div>";
                echo "<p>You searched for: " . $search . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>