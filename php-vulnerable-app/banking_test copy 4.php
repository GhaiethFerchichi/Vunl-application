<?php
/**
 * BTE Project: Vulnerable & Non-SOLID Test File
 * This file is designed to test if the AI can detect architectural 
 * rot and complex security injections.
 */

class BankingManager {
    // VIOLATION: SOLID - Single Responsibility Principle
    // This class handles DB, Business Logic, HTML Rendering, and Logging.
    
    public function processTransaction() {
        $db_host = "localhost";
        $db_user = "admin";
        $db_pass = "P@ssw0rd123!"; // VULNERABILITY: Hardcoded Credentials

        $conn = mysqli_connect($db_host, $db_user, $db_pass, "bank_db");

        // VULNERABILITY: SQL Injection (OWASP A03:2021)
        // User input from the URL is trusted directly.
        $account_id = $_GET['account_id'];
        $sql = "SELECT balance FROM accounts WHERE id = " . $account_id;
        $result = mysqli_query($conn, $sql);

        while($row = mysqli_fetch_assoc($result)) {
            // VULNERABILITY: Cross-Site Scripting (XSS) (OWASP A03:2021)
            // Reflecting the account ID back to the user without sanitization.
            echo "Account " . $_GET['account_id'] . " has balance: " . $row['balance'];
        }

        $this->logToFile("Transaction processed for " . $_GET['account_id']);
    }

    private function logToFile($msg) {
        // VIOLATION: Open/Closed Principle
        // Logic is hardcoded to only write to a specific local file.
        $file = fopen("/var/www/logs/bank.log", "a");
        fwrite($file, $msg . "\n");
        fclose($file);
    }
}

// Execution
$manager = new BankingManager();
$manager->processTransaction();
?>