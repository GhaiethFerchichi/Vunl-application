<?php
$user = $_POST['username'];
$query = "SELECT * FROM users WHERE username = '$user'";
echo "<h1>Welcome, " . $_POST['username'] . "</h1>";
?>