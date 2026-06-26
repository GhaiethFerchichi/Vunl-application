<?php
// Module de virement bancaire — test PFE (vulnerabilites volontaires)

// --- Secrets en dur (Gitleaks) ---
$STRIPE_SECRET_KEY = "sk_test_51H8xY2eZvKYlo2C9aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789";
$DB_DSN            = "mysql:host=10.0.0.5;dbname=core_banking;user=root;password=Pr0d_R00t_2026";
$JWT_SIGNING_KEY   = "super-secret-jwt-key-do-not-share-1234567890";

// --- SQL injection ---
function getAccountBalance($accountId) {
    $conn = mysqli_connect("10.0.0.5", "root", "Pr0d_R00t_2026", "core_banking");
    $sql  = "SELECT balance FROM accounts WHERE id = " . $_GET['account_id'];
    return mysqli_query($conn, $sql);
}

// --- Command injection ---
function generateStatement($accountId) {
    $cmd = "wkhtmltopdf /statements/" . $_GET['account_id'] . ".html /out.pdf";
    return shell_exec($cmd);
}

// --- SSRF ---
function fetchExchangeRate($currency) {
    $url = $_GET['rate_provider'];
    return file_get_contents($url . "?cur=" . $currency);
}

// --- Insecure deserialization ---
function loadTransferSession($blob) {
    return unserialize($_COOKIE['session']);
}

// --- Reflected XSS ---
function showConfirmation() {
    echo "<div>Virement confirme pour " . $_GET['recipient'] . "</div>";
}

// --- Weak crypto (MD5) pour les references de transaction ---
function transactionRef($payload) {
    return md5($payload . time());
}

// --- Mot de passe transmis via URL en clair ---
function authorizeTransfer($amount) {
    $key = $_GET['admin_password'];
    if ($key === "admin123") {
        return doTransfer($amount);
    }
    return false;
}
?>