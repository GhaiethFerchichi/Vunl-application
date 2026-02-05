<?php
/**
 * Configuration file with sensitive information exposure
 */

// VULNERABILITY: Sensitive data hardcoded
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'password123');
define('DB_NAME', 'vulnerable_db');

// VULNERABILITY: API keys in code
define('API_KEY', 'sk_test_51234567890abcdefghijklmnop');
define('SECRET_KEY', 'secret_key_123456');
define('JWT_SECRET', 'super_secret_jwt_key');

// VULNERABILITY: Weak encryption key
define('ENCRYPTION_KEY', 'myencryptionkey');

// VULNERABILITY: Debug mode enabled
define('DEBUG_MODE', true);
define('LOG_ERRORS_TO_FILE', false);

// VULNERABILITY: No security headers
header('X-UA-Compatible: IE=edge');

// VULNERABILITY: Exposed database connection
try {
    // $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
    // Commented to prevent actual connection
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}

?>
