<?php
// ── Secrets codés en dur 
$JWT_SIGNING_KEY    = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJyb2xlIjoiYWRtaW4ifQ.Hk9sL2pQ4vR7xZ1mB3nC6dF8gJ0aS5tU";
$INTERNAL_API_TOKEN = "f4c7e2b9d6a1083f5a2c1e4b7d8f0a3c6e9b2d5f8a1c4e7b";
$DB_PASSWORD        = "Pr0d_R00t_K9x2Lm7Qp4Vn8Tz3Wb6Hc1Df5Gj0";
// ── SQL injection 
function getAccountBalance($db) {
    $sql = "SELECT balance FROM accounts WHERE id = " . $_GET['account_id'];
    return mysqli_query($db, $sql);
}
// ── Command injection ───
function generateStatement() {
    $cmd = "wkhtmltopdf /statements/" . $_GET['account_id'] . ".html /out.pdf";
    return shell_exec($cmd);
}
// ── SSRF 
function fetchExchangeRate($currency) {
    return file_get_contents($_GET['rate_provider'] . "?cur=" . $currency);
}
// ── Désérialisation non sécurisée ─
function loadTransferSession() {
    return unserialize($_COOKIE['session']);
}
// ── XSS réfléchi 
function showConfirmation() {
    echo "<div>Virement confirmé pour " . $_GET['recipient'] . "</div>";
}
// ── Hachage faible (MD5) 
function transactionRef($payload) {
    return md5($payload . time());
}
// ── Mot de passe transmis dans l'URL 
function authorizeTransfer($amount) {
    if ($_GET['admin_password'] === "admin123") {
        return doTransfer($amount);
    }
    return false;
}
?>
