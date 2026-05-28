<?php
// Vulnerabilites multiples pour test final PFE

// Vuln 1 : SQL injection
function loginUser($name, $password) {
    $query = "SELECT * FROM accounts WHERE name='" . $name . "' AND pwd='" . $password . "'";
    return mysql_query($query);
}

// Vuln 2 : Hardcoded secrets
$AWS_ACCESS_KEY = "AKIAIOSFODNN7EXAMPLE";
$DB_PASSWORD = "BankAdmin_S3cret_2026!";
$GITHUB_TOKEN = "ghp_aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890";

// Vuln 3 : Path traversal
function getCustomerFile($id) {
    return file_get_contents("/var/data/customers/" . $_GET['file']);
}

// Vuln 4 : Command injection
function exportReport($account) {
    return shell_exec("python /tools/export.py " . $_POST['account']);
}

// Vuln 5 : Weak crypto MD5
function hashCustomerPwd($pwd) {
    return md5($pwd);
}
?>
