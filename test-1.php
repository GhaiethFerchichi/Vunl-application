<?php

// Hardcoded secret (Gitleaks should detect this)
$api_key = "sk_test_123456789_SECRET_KEY";

// Database connection (no error handling)
$conn = mysqli_connect("localhost", "root", "password", "test_db");

// SQL Injection vulnerability
$username = $_GET['username'];
$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);

// Reflected XSS vulnerability
echo "<h1>Welcome " . $_GET['username'] . "</h1>";

// Command Injection vulnerability
if (isset($_GET['ping'])) {
    $ip = $_GET['ping'];
    system("ping -c 4 " . $ip);
}

// File Inclusion vulnerability
if (isset($_GET['page'])) {
    include($_GET['page']);
}

// Insecure file upload (no validation)
if (isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], "uploads/" . $_FILES['file']['name']);
}

?>
