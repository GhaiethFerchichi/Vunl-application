<?php
/**
 * API Endpoint with Vulnerabilities
 */

header('Content-Type: application/json');

// VULNERABILITY: No authentication check
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

if ($action === 'get_user') {
    // VULNERABILITY: SQL Injection in API
    $user_id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = " . $user_id;
    
    echo json_encode([
        'success' => true,
        'sql' => $sql,
        'user' => [
            'id' => $user_id,
            'username' => 'user_' . $user_id,
            'email' => 'user' . $user_id . '@example.com'
        ]
    ]);
}

if ($action === 'create_user') {
    // VULNERABILITY: No input validation
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = md5($data['password'] ?? '');
    
    // VULNERABILITY: SQL Injection
    $sql = "INSERT INTO users (username, email, password) VALUES ('" . $username . "', '" . $email . "', '" . $password . "')";
    
    echo json_encode([
        'success' => true,
        'sql' => $sql,
        'message' => 'User created'
    ]);
}

if ($action === 'exec_command') {
    // VULNERABILITY: Remote Code Execution
    $cmd = $_GET['cmd'] ?? 'ls';
    // exec($cmd, $output);
    
    echo json_encode([
        'success' => true,
        'command' => $cmd,
        'output' => 'Command execution blocked for safety'
    ]);
}

if ($action === 'upload_file') {
    // VULNERABILITY: Arbitrary file upload
    if (isset($_FILES['file'])) {
        $filename = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        
        // VULNERABILITY: No file type validation
        // move_uploaded_file($tmp_name, '/var/www/uploads/' . $filename);
        
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded',
            'filename' => $filename
        ]);
    }
}

?>
