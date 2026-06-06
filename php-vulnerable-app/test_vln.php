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

        // VULNERABILITY 3: Insecure Direct Object Reference (IDOR)
        if ($action === 'user_profile') {
            echo "<h2>User Profile</h2>";
            ?>
            <form method="GET" class="form">
                <input type="hidden" name="action" value="user_profile">
                <h3>Insecure Direct Object Reference</h3>
                <input type="number" name="user_id" placeholder="User ID (Try: 1, 2, 3...)" required>
                <button type="submit">View Profile</button>
            </form>
            <?php
            if ($user_id) {
                // VULNERABILITY: No authorization check - directly accessing user data by ID
                echo "<div class='alert alert-danger'>User ID " . intval($user_id) . " accessed without authorization check</div>";
                echo "<p><strong>Username:</strong> user_" . $user_id . "</p>";
                echo "<p><strong>Email:</strong> user" . $user_id . "@example.com</p>";
                echo "<p><strong>Role:</strong> admin</p>";
            }
        }

        // VULNERABILITY 4: Path Traversal
        if ($action === 'view_file') {
            echo "<h2>File Viewer</h2>";
            ?>
            <form method="GET" class="form">
                <input type="hidden" name="action" value="view_file">
                <h3>Path Traversal Vulnerability</h3>
                <input type="text" name="file" placeholder="File path (Try: ../../../etc/passwd)" required>
                <button type="submit">View File</button>
            </form>
            <?php
            if ($file) {
                // VULNERABILITY: Path traversal - no validation on file path
                $filepath = dirname(__FILE__) . '/files/' . $file;
                if (file_exists($filepath)) {
                    echo "<div class='alert alert-success'>File contents:</div>";
                    echo "<pre>" . htmlspecialchars(file_get_contents($filepath)) . "</pre>";
                } else {
                    echo "<div class='alert alert-danger'>File not found: " . htmlspecialchars($filepath) . "</div>";
                }
            }
        }

        // VULNERABILITY 5: Command Injection
        if ($action === 'command') {
            echo "<h2>System Command</h2>";
            ?>
            <form method="POST" class="form">
                <input type="hidden" name="action" value="command">
                <h3>Command Injection Vulnerability</h3>
                <input type="text" name="filename" placeholder="Filename (Try: test.txt; cat /etc/passwd)" required>
                <button type="submit">Create File</button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
                $filename = $_POST['filename'];
                // VULNERABILITY: Direct command execution with user input
                $cmd = "touch /tmp/" . $filename;
                echo "<div class='alert alert-danger'>Command: " . htmlspecialchars($cmd) . "</div>";
                // Commented to prevent actual execution
                // exec($cmd);
                echo "<div class='alert alert-success'>File created (simulated)</div>";
            }
        }

        // VULNERABILITY 6: Weak Cryptography
        if ($action === 'password') {
            echo "<h2>Password Manager</h2>";
            ?>
            <form method="POST" class="form">
                <input type="hidden" name="action" value="password">
                <h3>Weak Password Hashing</h3>
                <input type="password" name="pwd" placeholder="Enter password" required>
                <button type="submit">Hash Password</button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pwd'])) {
                $pwd = $_POST['pwd'];
                // VULNERABILITY: Using weak md5 hashing with no salt
                $weak_hash = md5($pwd);
                $weak_hash_salt = md5($pwd . "fixed_salt");
                echo "<div class='alert alert-danger'>MD5 Hash (no salt): " . $weak_hash . "</div>";
                echo "<div class='alert alert-danger'>MD5 Hash (fixed salt): " . $weak_hash_salt . "</div>";
            }
        }

        // VULNERABILITY 7: CSRF (No Token Validation)
        if ($action === 'transfer') {
            echo "<h2>Money Transfer</h2>";
            ?>
            <form method="POST" class="form">
                <input type="hidden" name="action" value="transfer">
                <h3>CSRF Vulnerability - No Token</h3>
                <input type="text" name="recipient" placeholder="Recipient Account" required>
                <input type="number" name="amount" placeholder="Amount" required>
                <button type="submit">Transfer Money</button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipient'])) {
                // VULNERABILITY: No CSRF token validation
                $recipient = $_POST['recipient'];
                $amount = $_POST['amount'];
                echo "<div class='alert alert-danger'>Transfer of $" . htmlspecialchars($amount) . " to " . htmlspecialchars($recipient) . " completed</div>";
                echo "<p><em>No CSRF token validation!</em></p>";
            }
        }

        // VULNERABILITY 8: Insecure Deserialization
        if ($action === 'serialize') {
            echo "<h2>Serialization</h2>";
            ?>
            <form method="POST" class="form">
                <input type="hidden" name="action" value="serialize">
                <h3>Insecure Deserialization</h3>
                <textarea name="data" placeholder="Serialized PHP object" required></textarea>
                <button type="submit">Deserialize</button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
                $data = $_POST['data'];
                // VULNERABILITY: Unserializing user input
                echo "<div class='alert alert-danger'>Deserializing user input...</div>";
                try {
                    @unserialize($data);
                    echo "<div class='alert alert-success'>Deserialization completed</div>";
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
        }
        ?>

        <hr>
        <h3>Navigation</h3>
        <ul>
            <li><a href="?action=login">SQL Injection</a></li>
            <li><a href="?action=search">XSS Vulnerability</a></li>
            <li><a href="?action=user_profile">IDOR Vulnerability</a></li>
            <li><a href="?action=view_file">Path Traversal</a></li>
            <li><a href="?action=command">Command Injection</a></li>
            <li><a href="?action=password">Weak Cryptography</a></li>
            <li><a href="?action=transfer">CSRF Vulnerability</a></li>
            <li><a href="?action=serialize">Insecure Deserialization</a></li>
        </ul>

        <hr>
        <h3>Vulnerabilities Included</h3>
        <ul>
            <li>SQL Injection</li>
            <li>Cross-Site Scripting (XSS)</li>
            <li>Insecure Direct Object References (IDOR)</li>
            <li>Path Traversal</li>
            <li>Command Injection</li>
            <li>Weak Cryptography (MD5 without salt)</li>
            <li>CSRF (No token validation)</li>
            <li>Insecure Deserialization</li>
            <li>Security misconfiguration (error display enabled)</li>
            <li>Hardcoded credentials</li>
        </ul>
    </div>
</body>
</html>
